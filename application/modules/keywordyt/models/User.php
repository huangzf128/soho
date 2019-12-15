<?php
class Keywordyt_Model_User extends Db_Abstract{
    
    function __construct() { }
    
    public function checkUser($id, $password) {
        
        $client = new Zend_Http_Client();
        $client->setConfig(array(
                'adapter'   => 'Zend_Http_Client_Adapter_Curl',
                'keepalive' => true,
                'curloptions' => array(CURLOPT_FOLLOWLOCATION => false),
        ));
        
        $info = Com_Util::encrypt("id=".$id."&p=".$password."&site=".Com_Const::YOUTUBE);
        
        $client->setUri("http://gskw:8888/keyword/user/get-user-info?info=".urlencode($info)."&f=auth");
        //$client->setUri("https://gskw.net/keyword/user/get-user-info?info=".urlencode($info)."&f=auth");
        $result = Com_Util::sendAPIRequest($client);
        
    	return json_decode($result);
	}
	
}