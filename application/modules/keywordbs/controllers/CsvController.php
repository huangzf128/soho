<?php

class Keywordbs_CsvController extends Zend_Controller_Action
{
    private $userid = null;
    
    public function init()
    {
        $zend_session = new Zend_Session_Namespace("auth");
        if (isset($zend_session->userid)) {
            $this->userid = $zend_session->userid;
            $this->_helper->layout->assign('usertype', $zend_session->type);
            $this->_helper->layout->assign('username', $zend_session->username);
        } 
    }

    /**
     * 予約したCSVファイルを表示する
     */
    public function indexAction()
    {
        if (isset($this->userid)) {
            $csvModel = new Keywordbs_Model_Csv();
            $orders = $csvModel->getCsvOrderList($this->userid);
            $this->view->orders = $orders;
        } else {
            $this->_redirect('/keywordbs/index/login');
        }
    }

    // CSV予約
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
            $csvModel = new Keywordbs_Model_Csv();
            
            $execuintCount = $csvModel->getWaitingCount($this->userid);
            if($execuintCount > Com_Const::CSV_MAX_ORDER) {
                echo "2";
                return;
            }
            
            $result = $csvModel->registCsvOrder($this->userid, $historyid);
            
            if ($result) {
                // 非同期処理
                if (Com_Util::isHttps()) {
                    $url_list = array("https://".$_SERVER['HTTP_HOST']."/keywordbs/csv/expand-Result-Ajax?historyid=".$historyid."&userid=".$this->userid);
                } else {
                    $url_list = array("http://".$_SERVER['HTTP_HOST']."/keywordbs/csv/expand-Result-Ajax?historyid=".$historyid."&userid=".$this->userid);
                }                
                Com_Util::sendMulitRequest($url_list, 2);
            }
        } catch (Exception $e) {
            Com_Log::registErrorLog($e->getMessage(), "csvFileOrderAction", null, null, Com_Const::BING);
        }
    
        echo $result ? "1" : "0";
    }
    
    // 中断
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
        $csvModel = new Keywordbs_Model_Csv();
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
                
                sleep(Com_Const::CSV_EXPAND_PER_WAITTIME);
                // Com_Log::registExpandLog($callApiTimes, "Sleep", "historyid: ".$historyid, null, Com_Const::BING);
                
                // 中断
                $this->callInterruptedRequest($historyid, $newRand);
                return;
            }
            
            try {
                //Com_Log::registExpandLog($interruptinfo[0]."--".$interruptinfo[1], "Interrupted", "historyid: ".$historyid, $rand, Com_Const::BING);
                
                $expandRst = $this->createExpandCsv($historyid, $startTime, $csvModel);
                if (is_int($expandRst)) {
                    // 中断
                    $this->callInterruptedRequest($historyid, $expandRst);
                    return;
                } elseif ($expandRst == false) {
                    return;
                }
                
                $historyid = $this->getNextHistoryid($this->userid);
                if (!empty($historyid)) {
                    $csvModel->registExpand($historyid);
                    // 中断
                    $this->callInterruptedRequest($historyid, 0);
                }
                
            } catch(Exception $e) {
                Com_Log::registErrorLog($e->getMessage(), "expandResultAjaxAction: catch ", "historyid: ".$historyid, null, Com_Const::BING);
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
        
        $csvModel = new Keywordbs_Model_Csv();
        $hasExecuting = $csvModel->hasExecutingCsv($this->userid);
        
        set_time_limit(0);
        
        if ($hasExecuting === false) {
            try {
                $startTime = time(true);
                $csvModel->setCallApiTimes(1);
                
                $rst = $csvModel->registExpand($historyid);
                if ($rst) {
                    $expandRst = $this->createExpandCsv($historyid, $startTime, $csvModel);
                    if (is_int($expandRst)) {
                        // 中断
                        $this->callInterruptedRequest($historyid, $expandRst);
                        return;
                    } elseif ($expandRst == false) {
                        return;
                    }
                }
                
                // 処理成功だったら、次へ
                $historyid = $this->getNextHistoryid($this->userid);
                if (!empty($historyid)) {
                    $csvModel->registExpand($historyid);
                    // 中断
                    $this->callInterruptedRequest($historyid, 0);
                }
                
            } catch(Exception $e) {
                Com_Log::registErrorLog($e->getMessage(), "expandResultAjaxAction: catch ", "historyid: ".$historyid, null, Com_Const::BING);
            }
        }
    }
    
    // CSVファイルをダウンロードする
    public function downloadAction()
    {
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="keyword.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $historyid = $this->getRequest()->id;
        $csvModel = new Keywordbs_Model_Csv();
        $expand = $csvModel->getExpandResult($historyid);
        
        // CSVを作成する
        $csvModel->makeCsv($expand);
    }
    
    // csv 削除
    public function deleteAction() {
        
        $historyid = $this->getRequest()->id;
        $next = $this->getRequest()->next;
        
        $csvModel = new Keywordbs_Model_Csv();
        $csvModel->deleteOrderCsv($historyid, $this->userid);
        
        if ($next == 1 && false == $csvModel->hasExecutingCsv($this->userid)) {
            
            // 待ち中キーワードを取得する
            $historyid = $this->getNextHistoryid($this->userid);
            if (!empty($historyid)) {
                // 非同期処理
                if (Com_Util::isHttps()) {
                    $url_list = array("https://".$_SERVER['HTTP_HOST']."/keywordbs/csv/expand-Result-Ajax?historyid=".$historyid."&userid=".$this->userid);
                } else {
                    $url_list = array("http://".$_SERVER['HTTP_HOST']."/keywordbs/csv/expand-Result-Ajax?historyid=".$historyid."&userid=".$this->userid);
                }
                Com_Util::sendMulitRequest($url_list, 2);   
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
        
        //$interruptinfo = explode("-", $expandRst["interruptinfo"]);
        $interruptinfo = $this->splitInterruptinfo("-", $expandRst["interruptinfo"]);
        if ($level < $interruptinfo[0]) {
            return false;
        }
        return true;
    }
    
    private function callInterruptedRequest($historyid, $rand) {
        // 中断
        if (Com_Util::isHttps()) {
            $url_list = array("https://".$_SERVER['HTTP_HOST']."/keywordbs/csv/expand-Interrupted-Result-Ajax?historyid=".$historyid."&userid=".$this->userid."&rand=".$rand);
        } else {
            $url_list = array("http://".$_SERVER['HTTP_HOST']."/keywordbs/csv/expand-Interrupted-Result-Ajax?historyid=".$historyid."&userid=".$this->userid."&rand=".$rand);
        }
        Com_Util::sendMulitRequest($url_list, 2);
    }
    
    private function createExpandCsv($historyid, $startTime, $csvModel) {
    
        try {
            // get history
            $expandRst = $csvModel->getExpandResult($historyid);
            $expandKeywords = $expandRst["result"];
            //$interruptinfo = explode("-", $expandRst["interruptinfo"]);
            $interruptinfo = $this->splitInterruptinfo("-", $expandRst["interruptinfo"]);
            
            // expand Search
            for ($i = 0; $i < Com_Const::CSV_EXPAND_LEVEL_MAX; $i++) {
                
                
                if ($this->isNeedExpand($expandRst, $i + 1)) {
                    
                    if (empty($interruptinfo[1])) {
                        Com_Log::registExpandLog("start: level".($i + 1), $historyid, null, null, Com_Const::BING);
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
                //sleep(20);
            }
            
            Com_Log::registExpandLog("end", $historyid, null, null, Com_Const::BING);
            
            $csvModel->updateExpandStatus($historyid, Com_Const::STATUS_FINISH);
        } catch (Exception $e) {
            
            $csvModel->updateExpandStatus($historyid, Com_Const::STATUS_ERROR);
            Com_Log::registErrorLog($e->getMessage(), "createExpandCsv: catch ", "historyid: ".$historyid, null, Com_Const::BING);
            return false;
        }
        
        return true;
    }
    
    private function getNextHistoryid($userid) {
        $csvModel = new Keywordbs_Model_Csv();
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
}

