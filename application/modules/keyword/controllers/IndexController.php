<?php

class Keyword_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $zend_session = new Zend_Session_Namespace("auth");
        if (isset($zend_session->userid)) {
            
            $this->_helper->layout->assign('userid', $zend_session->userid);
            $this->_helper->layout->assign('username', $zend_session->username);
            $this->_helper->layout->assign('usertype', $zend_session->type);
            $this->_helper->layout->assign('service', $zend_session->service);
            
            $zend_session->lastModified = time();
            $zend_session->setExpirationSeconds(Com_Const::SESSION_EXPIRE);
        }
    }

    public function indexAction() {    }

    public function loginAction() {    }
    
    public function authAction() 
    {
        $id = mb_convert_encoding($this->getRequest()->id, "utf-8","auto");
        $password = mb_convert_encoding($this->getRequest()->password, "utf-8","auto");

        $model = new Keyword_Model_User();
        $user = $model->getUser($id);
        
        if ($user && $user['password'] == $password && 
                (strpos($user["site"], Com_Const::GOOGLE."") !== false || 
                 $user["zero"] == 1)) {

            Zend_Session::regenerateId();
            $zend_session = new Zend_Session_Namespace("auth");
            
            $zend_session->userid = $user["id"];
            $zend_session->type = $user["type"];
            $zend_session->username = $user["name"];
            $zend_session->service = array("zero" => $user["zero"], "csv" => $user["site"]);
            $zend_session->lastModified = time();
            
            $zend_session->setExpirationSeconds(Com_Const::SESSION_EXPIRE);
            
            if ($user["type"] == Com_Const::USER_ADMIN) {
                // 管理者
                $this->_redirect('/keyword/user');
            }
            
            $this->_helper->layout->assign('userid', $zend_session->userid);
            $this->_helper->layout->assign('usertype', $zend_session->type);
            $this->_helper->layout->assign('username', $zend_session->username);
            $this->_helper->layout->assign('service', $zend_session->service);
            
            $this->_helper->viewRenderer->setRender('index');
            
            return;
        }

        $this->view->errormsg = 'お客様ＩＤもしくはパスワードが違います。';
        $this->_helper->viewRenderer->setRender('login');
    }
    
    public function logoutAction() 
    {
        Zend_Session::namespaceUnset('auth');
        Zend_Session::forgetMe();
        Zend_Session::regenerateId();
        
        //Zend_Session::destroy();
        
        $this->_helper->layout->assign('userid', null);
        $this->_helper->layout->assign('username', null);
        $this->_helper->layout->assign('usertype', null);
        $this->_helper->layout->assign('service', null);
        
        $this->_helper->viewRenderer->setRender('index');
    }
}

