<?php
class Keyword_Model_Log extends Db_Abstract{
    
    function __construct() { }
    
    public static function registErrorLog($message, $biko1, $biko2, $biko3)
    {
        $dao = new Keyword_Model_Entities_Log();
        $data = array();
    
        $data["registdt"] = date('Y-m-d H:i:s');
        $data["type"] = 0;
        $data["name"] = "exception";
        $data["message"] = $message;
        $data["biko1"] = $biko1;
        $data["biko2"] = $biko2;
        $data["biko3"] = $biko3;
    
        $dao->regist($data);
    }
    
    public static function registApiErrorLog($message, $biko1, $biko2, $biko3)
    {
        $dao = new Keyword_Model_Entities_Log();
        $data = array();
        
        $data["registdt"] = date('Y-m-d H:i:s');
        $data["type"] = 1;
        $data["name"] = "api";
        $data["message"] = $message;
        $data["biko1"] = $biko1;
        $data["biko2"] = $biko2;
        $data["biko3"] = $biko3;
        
        $dao->regist($data);
	}
	
	public static function registParseErrorLog($message, $biko1, $biko2, $biko3)
	{
	    $dao = new Keyword_Model_Entities_Log();
	    $data = array();
	
	    $data["registdt"] = date('Y-m-d H:i:s');
	    $data["type"] = 2;
	    $data["name"] = "parse";
	    $data["message"] = $message;
	    $data["biko1"] = $biko1;
	    $data["biko2"] = $biko2;
	    $data["biko3"] = $biko3;
	
	    $dao->regist($data);
	}
	
	public static function registExpandLog($message, $biko1, $biko2, $biko3)
	{
	    $dao = new Keyword_Model_Entities_Log();
	    $data = array();
	
	    $data["registdt"] = date('Y-m-d H:i:s');
	    $data["type"] = 3;
	    $data["name"] = "expand";
	    $data["message"] = $message;
	    $data["biko1"] = $biko1;
	    $data["biko2"] = $biko2;
	    $data["biko3"] = $biko3;
	
	    $dao->regist($data);
	}
	
	
}