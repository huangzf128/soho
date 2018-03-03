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

    public function indexAction()
    {
        $csvModel = new Keyword_Model_Csv();
        $orders = $csvModel->getCsvOrderList($this->userid);
        $this->view->orders = $orders;        
    }

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
        
        $csvModel->makeCsv($expand);
        
        exit();
        
    }
    
 
}

