<?php

class Keyword_CsvController extends Zend_Controller_Action
{
    private $userid = null;
    
    public function init()
    {
        $zend_session = new Zend_Session_Namespace("auth");
        if (isset($zend_session->userid)) {
            $this->userid = $zend_session->userid;
            
            $this->_helper->layout->assign('userid', $zend_session->userid);
            $this->_helper->layout->assign('username', $zend_session->username);
            $this->_helper->layout->assign('usertype', $zend_session->type);
            $this->_helper->layout->assign('service', $zend_session->service);
            
            $zend_session->lastModified = time();
            $zend_session->setExpirationSeconds(Com_Const::SESSION_EXPIRE);
        }
    }

    /**
     * 予約したCSVファイルを表示する
     */
    public function indexAction()
    {
        if (Com_Util::isCsvServiceEnabled(Com_Const::SERVICE_CSV_G)) {
            
            $csvModel = new Keyword_Model_Csv();
            $orders = $csvModel->getCsvOrderList($this->userid);
            $this->view->orders = $orders;
            
        } else {
            $this->_redirect('/keyword/index');
        }
    }
    
    public function csvFileOrderAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    
        $historyid = $this->getRequest()->historyid;
        
        if (empty($historyid) || empty($this->userid)) {
            echo "0";
            return;
        }
        
        try {
            // 予約に入れる
            $csvModel = new Keyword_Model_Csv();
            
            $execuintCount = $csvModel->getWaitingCount($this->userid);
            if($execuintCount > Com_Const::CSV_MAX_ORDER) {
                echo "2";
                return;
            }
            
            $result = $csvModel->registCsvOrder($this->userid, $historyid);
            
            if ($result) {
                // 非同期処理
                if (Com_Util::isHttps()) {
                    $url_list = array("https://".$_SERVER['HTTP_HOST']."/keyword/csv/expand-Result-Ajax?historyid=".$historyid."&userid=".$this->userid);
                } else {
                    $url_list = array("http://".$_SERVER['HTTP_HOST']."/keyword/csv/expand-Result-Ajax?historyid=".$historyid."&userid=".$this->userid);
                }
                Com_Util::sendMulitRequest($url_list);
            }
        } catch (Exception $e) {
            Com_Log::registErrorLog($e->getMessage(), "csvFileOrderAction", null, null, Com_Const::GOOGLE);
        }
    
        echo $result ? "1" : "0";
    }
    
    public function expandInterruptedResultAjaxAction() {
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        // check
        $historyid = $this->getRequest()->historyid;
        $this->userid = $this->getRequest()->userid;
        $rand = $this->getRequest()->rand;
        
        if (empty($this->userid) || empty($historyid)) {
            return;
        }
        
        set_time_limit(0);
        $csvModel = new Keyword_Model_Csv();
        $row = $csvModel->getExpandResult($historyid);
        
        $interruptinfo = array(0,0,0);
        if ($row) {
            //$interruptinfo = explode("-", $row["interruptinfo"]);
            $interruptinfo = $this->splitInterruptinfo("-", $row["interruptinfo"]);
        }
        
        if ($interruptinfo[2] === $rand) {
            $startTime = time(true);
            
            $callApiTimes = $rand % 1000;
            $csvModel->setCallApiTimes($callApiTimes);
            
            if ($callApiTimes > 490) {
                
                $newRand = $this->getRandNum(1);
                $interruptinfo = $interruptinfo[0]."-".$interruptinfo[1]."-".$newRand;
                $csvModel->updateExpandRand($historyid, $interruptinfo);
                
                sleep(Com_Const::CSV_EXPAND_PER_WAITTIME_G);
                
                // Com_Log::registExpandLog($callApiTimes, "Sleep", "historyid: ".$historyid, null, Com_Const::GOOGLE);
                
                $this->callInterruptedRequest($historyid, $newRand);
                
                return;
            }
            
            try {
                do {
                    Com_Log::registExpandLog($interruptinfo[0]."--".$interruptinfo[1], "Interrupted", "historyid: ".$historyid, $rand, Com_Const::GOOGLE);
                    
                    $expandRst = $this->createExpandCsv($historyid, $startTime, $csvModel);
                    if (is_int($expandRst)) {
                        
                        $this->callInterruptedRequest($historyid, $expandRst);
                        break;
                    } elseif ($expandRst == false) {
                        break;
                    }
                    
                    $historyid = $this->getNextHistoryid($this->userid);
                    if (!empty($historyid)) {
                        $rst = $csvModel->registExpand($historyid);
                        $interruptinfo = array(0, "", 0);
                    }
                } while(!empty($historyid));
                
            } catch(Exception $e) {
                Com_Log::registErrorLog($e->getMessage(), "expandResultAjaxAction: catch ", "historyid: ".$historyid, null, Com_Const::GOOGLE);
            }
        }
    }
    
    
    // CSV 展開する
    public function expandResultAjaxAction() {
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        // check
        $historyid = $this->getRequest()->historyid;
        $this->userid = $this->getRequest()->userid;
        
        if (empty($this->userid) || empty($historyid)) {
            return;
        }
        
        $csvModel = new Keyword_Model_Csv();
        $hasExecuting = $csvModel->hasExecutingCsv($this->userid);
        
        set_time_limit(0);
        
        if ($hasExecuting === false) {
            try {
                $startTime = time(true);
                $csvModel->setCallApiTimes(1);
                do {
                    $rst = $csvModel->registExpand($historyid);
                    if ($rst) {
                        $expandRst = $this->createExpandCsv($historyid, $startTime, $csvModel);
                        if (is_int($expandRst)) {
                            
                            $this->callInterruptedRequest($historyid, $expandRst);
                            
                            break;
                        } elseif ($expandRst == false) {
                            break;
                        } 
                    }
                    $historyid = $this->getNextHistoryid($this->userid);
                } while(!empty($historyid));
                
            } catch(Exception $e) {
                Com_Log::registErrorLog($e->getMessage(), "expandResultAjaxAction: catch ", "historyid: ".$historyid, null, Com_Const::GOOGLE);
            }
        }
    }
    
    /**
     * CSVファイルをダウンロードする
     */
    public function downloadAction()
    {
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="keyword.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $historyid = $this->getRequest()->id;
        $csvModel = new Keyword_Model_Csv();
        $expand = $csvModel->getExpandResult($historyid);
        
        // CSVを作成する
        $csvModel->makeCsv($expand);
    }
    
    public function deleteAction() {
        
        $historyid = $this->getRequest()->id;
        $next = $this->getRequest()->next;
        
        $csvModel = new Keyword_Model_Csv();
        $csvModel->deleteOrderCsv($historyid, $this->userid);
        
        if ($next == 1 && false == $csvModel->hasExecutingCsv($this->userid)) {
            
            // 待ち中キーワードを取得する
            $historyid = $this->getNextHistoryid($this->userid);
            if (!empty($historyid)) {
                // 非同期処理
                if (Com_Util::isHttps()) {
                    $url_list = array("https://".$_SERVER['HTTP_HOST']."/keyword/csv/expand-Result-Ajax?historyid=".$historyid."&userid=".$this->userid);
                } else {
                    $url_list = array("http://".$_SERVER['HTTP_HOST']."/keyword/csv/expand-Result-Ajax?historyid=".$historyid."&userid=".$this->userid);
                }
                Com_Util::sendMulitRequest($url_list);   
            }
        }
        
        $orders = $csvModel->getCsvOrderList($this->userid);
        $this->view->orders = $orders;
        
        $this->render("index");
    }
    
    /*------------------------------------------------------------------------
     *  private
     *------------------------------------------------------------------------*/
    
    private function splitInterruptinfo($delimiter, $interruptinfo) {
        
        $parts = explode($delimiter, $interruptinfo);
        
        $first = array_shift($parts);
        $last = array_pop($parts);
        $middle = trim(implode($delimiter, $parts));
        
        return array($first, $middle, $last);
    }
    
    private function getRandNum($calApiTimes) {
        $rand = rand(1000, 9999);
        $rand = $rand * 1000 + $calApiTimes;
        return $rand;
    }
    
    private function isNeedExpand($expandRst, $level) {
        if (empty($expandRst["level".$level])) {
            return true;
        }
        
        // $interruptinfo = explode("-", $expandRst["interruptinfo"]);
        $interruptinfo = $this->splitInterruptinfo("-", $expandRst["interruptinfo"]);
        
        if ($level < $interruptinfo[0]) {
            return false;
        }
        return true;
    }
    
    private function callInterruptedRequest($historyid, $rand) {
        // 中断
        if (Com_Util::isHttps()) {
            $url_list = array("https://".$_SERVER['HTTP_HOST']."/keyword/csv/expand-Interrupted-Result-Ajax?historyid=".$historyid."&userid=".$this->userid."&rand=".$rand);
        } else {
            $url_list = array("http://".$_SERVER['HTTP_HOST']."/keyword/csv/expand-Interrupted-Result-Ajax?historyid=".$historyid."&userid=".$this->userid."&rand=".$rand);
        }
        Com_Util::sendMulitRequest($url_list, 2);
    }
    
    private function createExpandCsv($historyid, $startTime, $csvModel) {
    
        try {
            // get history
            $expandRst = $csvModel->getExpandResult($historyid);
            if ($expandRst["bflag"] == 1) {
                $expandRst["result"] = gzuncompress($expandRst["resultb"]);
                $expandRst["level1"] = gzuncompress($expandRst["level1b"]);
                $expandRst["level2"] = gzuncompress($expandRst["level2b"]);
                $expandRst["level3"] = gzuncompress($expandRst["level3b"]);
            }
            $expandKeywords = $expandRst["result"];
            //$interruptinfo = explode("-", $expandRst["interruptinfo"]);
            $interruptinfo = $this->splitInterruptinfo("-", $expandRst["interruptinfo"]);
            
            
            // expand Search
            for ($i = 0; $i < Com_Const::CSV_EXPAND_LEVEL_MAX; $i++) {
                
                
                if ($this->isNeedExpand($expandRst, $i + 1)) {
                    
                    if (empty($interruptinfo[1])) {
                        Com_Log::registExpandLog("start: level".($i + 1), $historyid, null, null, Com_Const::GOOGLE);
                    }
                    
                    $expandKeywords = $csvModel->expandLevel($expandKeywords, $startTime, 
                                                                $expandRst["level".($i + 1)], $interruptinfo[1]);
                    
                    if ($expandKeywords == Com_Const::FORBIDDEN) {

                        $csvModel->updateExpandStatus($historyid, Com_Const::STATUS_FORBIDDEN);
                        return false;
                    } elseif ($expandKeywords == Com_Const::ERROR) {
                        
                        $csvModel->updateExpandStatus($historyid, Com_Const::STATUS_ERROR);
                        return false;
                    } elseif (is_array($expandKeywords) && $expandKeywords[0] == Com_Const::INTERRUPTION) {
                        $rand = $this->getRandNum($expandKeywords[3]);
                        
                        $csvModel->updateExpandLevel($historyid, $expandKeywords[1], $i + 1, 
                                                        ($i + 1)."-".$expandKeywords[2]."-".$rand);
                        return $rand;
                    }
                    else {
                        $csvModel->updateExpandLevel($historyid, $expandKeywords, $i + 1, ($i + 2)."--0");
                        $interruptinfo[1] = "";
                    }
                } else {
                    $expandKeywords = $expandRst["level".($i + 1)];
                }
                sleep(20);
            }
            
            Com_Log::registExpandLog("end", $historyid, null, null, Com_Const::GOOGLE);
            
            $csvModel->updateExpandStatus($historyid, Com_Const::STATUS_FINISH);
        } catch (Exception $e) {
            
            $csvModel->updateExpandStatus($historyid, Com_Const::STATUS_ERROR);
            Com_Log::registErrorLog($e->getMessage(), "createExpandCsv: catch ", "historyid: ".$historyid, null, Com_Const::GOOGLE);
            return false;
        }
        
        return true;
    }
    
    private function getNextHistoryid($userid) {
        $csvModel = new Keyword_Model_Csv();
        $historyid = $csvModel->getNotExecuteCsvId($userid);
        return $historyid;
    }
    
    private function errOutput($errSql_file, $sql){
    
        try{
            $fp = fopen("log/".$errSql_file, 'a+');
    
            if ($fp){
                if (flock($fp, LOCK_EX)){
                    if (fwrite($fp,  $sql."\r\n") === FALSE){
                        new Exception('ファイル書き込みに失敗しました');
                    }
                    flock($fp, LOCK_UN);
                }
            }
            fclose($fp);
        }catch(Exception $e){
            new Exception($e->getMessage());
        }
    }
    
    public function testAction() {
        
        $api = "http://kw:8888/result";
        $keyword = "football";
        
        $keyRes = array("1" => "football", "2" => "akb48");
        
        $posts = array(
                'site' => Com_Const::GOOGLE,
                'keyword' => urlencode(json_encode($keyRes))
        );
        
        $client = new Zend_Http_Client();
        $client->setConfig(array(
                'adapter'   => 'Zend_Http_Client_Adapter_Curl',
                'keepalive' => true,
                'curloptions' => array(CURLOPT_FOLLOWLOCATION => false),
        ));
        
        try {
            $client->setUri($api);
            $client->setParameterPost($posts);
            $response = Com_Util::sendAPIRequest($client, Com_Const::GOOGLE, "POST");
            
        } catch (Exception $e) {}
        
        if (!$response || in_array($response, array(Com_Const::FORBIDDEN, Com_Const::SERVICEUNAVAILABLE, Com_Const::EAPPIDERR)) ) {
             
            $otherServerRes = Com_Util::getFromReplaceServer($client, $posts, Com_Const::GOOGLE);
            if ($otherServerRes == false) {
                 
                Com_Log::registApiErrorLog("game", "over", "0件", mb_convert_encoding($keyword, "shift-jis"), Com_Const::GOOGLE);
                 
                return Com_Const::FORBIDDEN;
            } else {
                // success
                $response = $otherServerRes[0];
                $api = $otherServerRes[1];
            }
        }
        
        $response = json_decode($response);
        
        echo var_dump($response);
        
    }
}

