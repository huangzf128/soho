<?php

class Keyword_DeleteController extends Zend_Controller_Action
{

	private $password;
	
    public function init()
    {
    	$password = "gskw.123";
    }

    public function indexAction()
    {
    	$session = new Zend_Session_Namespace();
   		if(isset($session->status)){
   			$this->render("index");
   		} else {
   			$psd = mb_convert_encoding($this->getRequest()->psd, "utf-8","auto");
   			if(isset($psd) && $psd == "gskw.123"){
   				$session->status = "1";
   				$this->render("index");
   			} else {
   				$this->render("login");
   			}
   		}
    }

    public function searchAction(){
    	$session = new Zend_Session_Namespace();
    	if(isset($session->status)){
    		$this->_helper->layout->setLayout('history');
    		 
    		$keyword = mb_convert_encoding($this->getRequest()->keyword, "utf-8", "auto");
    		$model = new Keyword_Model_SuggestKeyword($keyword);
    		
    		$result = $model->getSearchDeleteList($keyword);
    		$this->view->rlist = $result;
    		$this->view->delKwd = $keyword;
    		
    	} else {
   			$this->render("login");
    	}
    }

    public function deleteAction() {
    	
    	$idList = mb_convert_encoding($this->getRequest()->idlist, "utf-8","auto");
    	$delKwd = mb_convert_encoding($this->getRequest()->delKwd, "utf-8","auto");
    	$model = new Keyword_Model_SuggestKeyword(null);
    	$model->deleteKeywordList($idList);
    	
    	$this->_response->setRedirect("search?keyword=".$delKwd)->sendResponse();
    }
    
}

