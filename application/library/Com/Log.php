<?php
class Com_Log extends Db_Abstract{
    
    protected $_name = 'log';
    protected $_use_adapter = 'front_db';
    	
    public static function registErrorLog($message, $biko1, $biko2, $biko3, $site)
    {
    
        $data["registdt"] = date('Y-m-d H:i:s');
        $data["type"] = 0;
        $data["name"] = "exception";
        $data["message"] = $message;
        $data["biko1"] = $biko1;
        $data["biko2"] = $biko2;
        $data["biko3"] = $biko3;
        $data["site"] = $site;
    
        $log = new Com_Log();
        $log->regist($data);
    }
    
    public static function registApiErrorLog($message, $biko1, $biko2, $biko3, $site)
    {
        
        $data["registdt"] = date('Y-m-d H:i:s');
        $data["type"] = 1;
        $data["name"] = "api";
        $data["message"] = $message;
        $data["biko1"] = $biko1;
        $data["biko2"] = $biko2;
        $data["biko3"] = $biko3;
        $data["site"] = $site;
        
        $log = new Com_Log();
        $log->regist($data);
	}
	
	public static function registParseErrorLog($message, $biko1, $biko2, $biko3, $site)
	{
	
	    $data["registdt"] = date('Y-m-d H:i:s');
	    $data["type"] = 2;
	    $data["name"] = "parse";
	    $data["message"] = $message;
	    $data["biko1"] = $biko1;
	    $data["biko2"] = $biko2;
	    $data["biko3"] = $biko3;
	    $data["site"] = $site;
	
        $log = new Com_Log();
        $log->regist($data);
	}
	
	public static function registExpandLog($message, $biko1, $biko2, $biko3, $site)
	{
	
	    $data["registdt"] = date('Y-m-d H:i:s');
	    $data["type"] = 3;
	    $data["name"] = "expand";
	    $data["message"] = $message;
	    $data["biko1"] = $biko1;
	    $data["biko2"] = $biko2;
	    $data["biko3"] = $biko3;
	    $data["site"] = $site;
	
        $log = new Com_Log();
        $log->regist($data);
	}
	
	/**
	 * Logを登録する
	 */
	public function regist($data)
	{
	    $row = $this->createRow();
	
	    $row->registdt = $data['registdt'];
	    $row->type = $data['type'];
	    $row->name = $data['name'];
	    $row->message = $data['message'];
	    $row->biko1 = $data['biko1'];
	    $row->biko2 = $data['biko2'];
	    $row->biko3 = $data['biko3'];
	    $row->site = $data['site'];
	
	    try {
	        $row->save();
	        return true;
	    } catch (Exception $e) {
	        return false;
	    }
	}
}