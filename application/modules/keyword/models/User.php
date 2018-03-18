<?php
class Keyword_Model_User extends Db_Abstract{
    
    
    /**
     * init
     * @param unknown $keyword
     */
    function __construct() {
        
    }
    
    public function getUser($id)
    {
        $userDao = new Keyword_Model_Entities_SearchUser();
        $user = $userDao->getRowById($id);
    	return $user;
	}
	
	public function getUserList() {
	    $userDao = new Keyword_Model_Entities_SearchUser();
	    $rows = $userDao->getAllUserList();
	    return $rows;
	}
	
	public function registUser($data) {
	    
	    $data["type"] = "1";
	    $data["valid"] = "1";
	    $data["updatedt"] = date('Y-m-d H:i:s');
	    
	    $dao = new Keyword_Model_Entities_SearchUser();
	    
	    $rst = $dao->regist($data);
	    return $rst;
	}
	
	public function deleteUser($id) {
	    $dao = new Keyword_Model_Entities_SearchUser();
	    $dao->deleteRowById($id);
	}
	
	public function updateUser($data, $id) {
	    
	    $where = array();
	    
	    $where["id = ?"] = $id;
	    $dao = new Keyword_Model_Entities_SearchUser();
	    
	    $dao->updateUser($data, $where);
	}
}