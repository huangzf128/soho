<?php

class Keyword_CsvController extends Zend_Controller_Action
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
        $csvModel = new Keyword_Model_Csv();
        $orders = $csvModel->getCsvOrderList($this->userid);
        $this->view->orders = $orders;
    }

    public function csvFileOrderAction()
    {
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    
        $historyid = $this->getRequest()->historyid;
        
        try {
            // 予約に入れる
            $csvModel = new Keyword_Model_Csv();
            $result = $csvModel->registCsvOrder($this->userid, $historyid);
            
            if ($result) {
                // 非同期処理
                if (Com_Util::isHttps()) {
                    $url_list = array("https://".$_SERVER['HTTP_HOST']."/keyword/csv/expand-Result-Ajax?historyid=".$historyid."&userid=".$this->userid);
                } else {
                    $url_list = array("http://".$_SERVER['HTTP_HOST']."/keyword/csv/expand-Result-Ajax?historyid=".$historyid."&userid=".$this->userid);
                }
                Com_Util::sendMulitRequest($url_list, 2);
            }
        } catch (Exception $e) {
            Com_Log::registErrorLog($e->getMessage(), "csvFileOrderAction", null, null, Com_Const::GOOGLE);
        }
    
        echo $result ? "1" : "0";
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
//         register_shutdown_function(function(){
//             Keyword_CsvController::shutdownFunction();
//         });
        
        if ($hasExecuting === false) {
            try {
                do {
                    $rst = $csvModel->registExpand($historyid);
                    if ($rst) {
                        $expandRst = $this->createExpandCsv($historyid);
                        if ($expandRst == false) {
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
    
    /*------------------------------------------------------------------------
     *  private
     *------------------------------------------------------------------------*/
    
    private function createExpandCsv($historyid) {
    
        $csvModel = new Keyword_Model_Csv();
        
        try {
            // get history
            $expandRst = $csvModel->getExpandResult($historyid);
            $expandKeywords = $expandRst["result"];
            
            // expand Search
            for ($i = 0; $i < Com_Const::CSV_EXPAND_LEVEL_MAX; $i++) {
                
                Com_Log::registExpandLog("start: level".($i + 1), $historyid, null, null, Com_Const::GOOGLE);
                
                if (empty($expandRst["level". ($i + 1)])) {
                    $expandKeywords = $csvModel->expandLevel($expandKeywords);
                    
                    if ($expandKeywords == Com_Const::FORBIDDEN) {

                        $csvModel->updateExpandStatus($historyid, Com_Const::STATUS_FORBIDDEN);
                        return false;
                    } elseif ($expandKeywords == Com_Const::ERROR) {
                    
                        $csvModel->updateExpandStatus($historyid, Com_Const::STATUS_ERROR);
                        return false;                        
                    } else {
                        $csvModel->updateExpandLevel($historyid, $expandKeywords, $i + 1);
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
}

