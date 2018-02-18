<?php

class Keyword_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $zend_session = new Zend_Session_Namespace("auth");
        if (isset($zend_session->userid)) {
            $this->_helper->layout->assign('usertype', $zend_session->type);
            $this->_helper->layout->assign('username', $zend_session->username);
        }
    }

    public function indexAction()
    {
    }

    public function loginAction()
    {
    }
    
    public function authAction() 
    {
        $id = mb_convert_encoding($this->getRequest()->id, "utf-8","auto");
        $password = mb_convert_encoding($this->getRequest()->password, "utf-8","auto");

        $model = new Keyword_Model_User();
        $user = $model->getUser($id, $password);
        
        if ($user && $user['password'] == $password) {
            
            $zend_session = new Zend_Session_Namespace("auth");
            
            $zend_session->userid = $user["id"];
            $zend_session->type = $user["type"];
            $zend_session->username = $user["name"];
            
            $this->_helper->layout->assign('usertype', $zend_session->type);
            $this->_helper->layout->assign('username', $zend_session->username);
            
            $this->_helper->layout->setLayout('layout');
            $this->_helper->viewRenderer->setRender('index');
            
            return;
        }

        $this->view->errormsg = 'メールもしくはパスワードが違います。';
        $this->_helper->layout->setLayout('layout');
        $this->_helper->viewRenderer->setRender('login');
    }
    
    public function logoutAction() 
    {
        Zend_Session::destroy();
        $this->_helper->layout->assign('usertype', null);
        $this->_helper->layout->assign('username', null);
        
        $this->_helper->layout->setLayout('layout');
        $this->_helper->viewRenderer->setRender('index');
    }
}

