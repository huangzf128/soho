<?php

class Keywordaz_ToolController extends Zend_Controller_Action
{
    public function init()
    {
        $zend_session = new Zend_Session_Namespace("auth");
        if (isset($zend_session->userid)) {
            $this->_helper->layout->assign('usertype', $zend_session->type);
            $this->_helper->layout->assign('username', $zend_session->username);
        }
    }
    
    public function showAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
     
        $tool = new Com_Tool(Com_Const::AMAZON);
        
        $fromDt = mb_convert_encoding($this->getRequest()->fromDt, "utf-8","auto");
        $toDt = mb_convert_encoding($this->getRequest()->toDt, "utf-8","auto");
        
//         $fromDt = "20140901230705";
//         $toDt = "20140901231251";

        $files = $tool->searchKeywordList($fromDt, $toDt);
        
        if ($files === FALSE) {
            echo "対象ファイルがありません。";
        }
        
        $tool->createBkFolder($fromDt."-".$toDt);
        
        $rst = $tool->saveHtml($files);
        
        if ($rst == Com_Const::INTERRUPTION) {
            $this->callInterruptedRequest($fromDt, $toDt);
            return;
        }
        
        mkdir('history'.DIRECTORY_SEPARATOR.$fromDt."-".$toDt."_OK", 0777);
        echo "ok"; 
    }
    
    public function zipAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $dir = mb_convert_encoding($this->getRequest()->dir, "utf-8","auto");
        
        if (empty($dir)) {
            return;
        }
        
        $tool = new Com_Tool(Com_Const::AMAZON);
        $tool->zipFile($dir);

    }
    
    public function removedirAction() {
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $dir = mb_convert_encoding($this->getRequest()->dir, "utf-8","auto");
        
        if (empty($dir)) {
            return;
        }
        
        $tool = new Com_Tool(Com_Const::AMAZON);
        $tool->removeFile('history'.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.'*');
    }
    
    private function callInterruptedRequest($fromDt, $toDt) {
        // 中断
        if (Com_Util::isHttps()) {
            $url_list = array("https://".$_SERVER['HTTP_HOST']."/tool/show?fromDt=".$fromDt."&toDt=".$toDt);
        } else {
            $url_list = array("http://".$_SERVER['HTTP_HOST']."/tool/show?fromDt=".$fromDt."&toDt=".$toDt);
        }
        Com_Util::sendMulitRequest($url_list, 2);
    }    
}

