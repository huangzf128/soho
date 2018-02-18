<?php
class Keyword_Model_User extends Db_Abstract{
    
    
    /**
     * init
     * @param unknown $keyword
     */
    function __construct() {
        
    }
    
    public function getUser($id, $password)
    {
        $userDao = new Keyword_Model_Entities_SearchUser();
        $user = $userDao->getRowById($id);
    	return $user;
	}
}