<?php

class Keywordbs_IndexController extends Zend_Controller_Action
{
    public function init()
    {
    	$zend_session = new Zend_Session_Namespace("auth");
    	if (isset($zend_session->userid)) {
    	    $this->_helper->layout->assign('usertype', $zend_session->type);
    	    $this->_helper->layout->assign('username', $zend_session->username);
    	}	
    }

    public function indexAction() {}
    
    public function loginAction() {}
    
    public function authAction()
    {
        $id = mb_convert_encoding($this->getRequest()->id, "utf-8","auto");
        $password = mb_convert_encoding($this->getRequest()->password, "utf-8","auto");
    
        $model = new Keywordbs_Model_User();
        $userinfo = $model->checkUser($id, $password);
    
        if (!empty($userinfo) && is_object($userinfo) &&
                $userinfo->id == $id && $userinfo->name != "failure") {
    
            $zend_session = new Zend_Session_Namespace("auth");
    
            $zend_session->userid = $id;
            //$zend_session->type = Com_Const::USER_COMMON;
            $zend_session->type = $userinfo->type;
            $zend_session->username = $userinfo->name;
            $zend_session->setExpirationSeconds(Com_Const::SESSION_EXPIRE);
    
            $this->_helper->layout->assign('usertype', $zend_session->type);
            $this->_helper->layout->assign('username', $zend_session->username);
    
            $this->_helper->viewRenderer->setRender('index');
    
            return;
        }
    
        $this->view->errormsg = 'お客様ＩＤもしくはパスワードが違います。';
        $this->_helper->viewRenderer->setRender('login');
    }
    
    public function logoutAction()
    {
        Zend_Session::destroy();
        $this->_helper->layout->assign('usertype', null);
        $this->_helper->layout->assign('username', null);
        $this->_helper->layout->assign('usertype', null);
    
        $this->_helper->viewRenderer->setRender('index');
    }

    /*---------------------------------------------------
     * Delete sensitive keyword
     ---------------------------------------------------*/
    public function displayDeleteAction(){
        $this->_helper->layout->disableLayout();
        $keyword = trim(mb_convert_encoding($this->getRequest()->keyword, "utf-8","UTF-8,eucjp-win,sjis-win"));
        if(!empty($keyword)){
            $model = new Keywordbs_Model_RecoveryModel($keyword);
            $result = $model->searchDeleteList();
            $this->view->keyword = $keyword;
            $this->view->rlist = $result;
            if(count($result) == 0){
                $this->view->msg = "該当データがありません。";
            }
        }
        $this->render("delete");
    }
    
    public function deleteAction(){
        $this->_helper->layout->disableLayout();
        $keyword = trim(mb_convert_encoding($this->getRequest()->keyword, "utf-8", "UTF-8,eucjp-win,sjis-win"));
        $password = trim(mb_convert_encoding($this->getRequest()->password, "utf-8","UTF-8,eucjp-win,sjis-win"));
        if($password !== "bskw.123"){
            $this->view->msg = "パスワードが違います。";
        }else if(!empty($keyword)){
            $model = new Keywordbs_Model_RecoveryModel($keyword);
            $result = $model->deleteKeyword();
            if($result){
                $this->view->msg = "削除しました。";
            }else{
                $this->view->msg = "削除が失敗しました。";
            }
        }else{
            $this->view->msg = "キーワードを入力してください。";
        }
    
        $this->render("delete");
    }
    

}

