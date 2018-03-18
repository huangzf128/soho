<?php

class Keyword_UserController extends Zend_Controller_Action
{
    private $userid = null;
    
    public function init()
    {
        $zend_session = new Zend_Session_Namespace("auth");
        if (isset($zend_session->userid)) {
            $this->userid = $zend_session->userid;
            $this->_helper->layout->assign('usertype', $zend_session->type);
            $this->_helper->layout->assign('username', $zend_session->username);
        } else {
            
            $fun = $this->getRequest()->f;
            if ($fun != "auth") {
                $this->_redirect('/keyword/index/login');
            }
        }
    }

    /**
     * ユーザーリストを表示する
     */
    public function indexAction()
    {
        $userModel = new Keyword_Model_User();
        $users = $userModel->getUserList();
        $this->view->users = $users;
    }

    public function getUserInfoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $info = $this->getRequest()->info;
        $info = Com_Util::decrypt(urldecode($info));
        
        parse_str($info, $user);
        
        $id = $user["id"];
        $password = $user["p"];
        
        $userModel = new Keyword_Model_User();
        $user = $userModel->getUser($id);
        
        if ($user != null && $user['password'] == $password) {
            echo Com_Util::encrypt($user["name"]);
            return;
        }
        
        ob_clean();
        echo Com_Util::encrypt("failure");
    }
    
    public function registAction() {
        
        $data = array();
        
        $data["id"] = $this->getRequest()->id;
        $data["name"] = $this->getRequest()->name;
        $data["email"] = $this->getRequest()->email;
        $data["password"] = $this->getRequest()->password;
        
        
        $model = new Keyword_Model_User();
        $rst = $model->registUser($data);
        
        if ($rst == false) {
            $this->view->msg = "IDは重複しています。";
            $this->view->id = $data["id"];
            $this->view->name = $data["name"];
            $this->view->email = $data["email"];
            $this->view->password = $data["password"];
        }
        
        $this->_forward('index');
    }
    
    public function deleteAction() {
        
        $id = $this->getRequest()->updid;
        $model = new Keyword_Model_User();
        $model->deleteUser($id);
        
        $this->_forward('index');
    }
    
    public function updateAction() {
        
        $data = array();
        
        $id = $this->getRequest()->updid;
        $data["name"] = $this->getRequest()->updname;
        $data["email"] = $this->getRequest()->updemail;
        $data["password"] = $this->getRequest()->updpassword;
        
        
        $model = new Keyword_Model_User();
        $model->updateUser($data, $id);
        
        $this->_forward('index');
    }
}

