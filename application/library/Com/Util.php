<?php
require_once 'Mcrypt.php';

Class Com_Util
{
	public function __construct(){}
	
	public static function encrypt($plain_text) {
	    $mcrypt = new Mcrypt();
	    return $mcrypt->encrypt($plain_text); ;
	}
	
	public static function decrypt($crypt_text) {
	    $mcrypt = new Mcrypt();
	    return $mcrypt->decrypt($crypt_text);
	}
	
	public static function isHttps() {
	    // httpsで通信しているかどうか
	    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
	}
	
	public static function isCsvServiceEnabled ($serviceid) {
	    
	    $zend_session = new Zend_Session_Namespace("auth");
	    
	    if (false === isset($zend_session->service) || false === array_key_exists("csv", $zend_session->service)) {
	        return false;
	    }
	    
	    $service = $zend_session->service;
	    if ($serviceid == Com_Const::SERVICE_CSV_G) {
	        return strpos($service["csv"], Com_Const::GOOGLE."") !== false;
	    }
	}
	
	// parameter not use
	public static function isZeroServiceEnabled ($serviceid) {
	    $zend_session = new Zend_Session_Namespace("auth");
	     
	    if (false === isset($zend_session->service) || false === array_key_exists("zero", $zend_session->service)) {
	        return false;
	    }
	    
	    $service = $zend_session->service;
	    return ($service["zero"] == 1);
	}
	
	public static function isLoggedUser($usertype) {
	    return isset($usertype);
	}
	
	public static function isAdmin($usertype) {
	    
	    return Com_Util::isLoggedUser($usertype) && $usertype == Com_Const::USER_ADMIN;
	}
	
	public static function filter($str) {
	    $search = array("&");
	    $replace = array("＆");
	    
	    return str_replace($search, $replace, $str);
	}
	
	public static function getHistoryTableName($site) {
	    
	    if ($site == Com_Const::AMAZON) {
	        $name = "searchhistoryaz";
	    } elseif ($site == Com_Const::BING) {
	        $name = "searchhistorybs";
	    } elseif ($site == Com_Const::YAHOO) {
	        $name = "searchhistoryya";
	    } elseif ($site == Com_Const::YOUTUBE) {
	        $name = "searchhistoryyt";
	    } elseif ($site == Com_Const::GOOGLE) {
	        $name = "searchhistory";
	    }
	    return $name;
	}
	
	public static function errOutput($errSql_file, $sql){
	
	    try{
	        $fp = fopen("log/".$errSql_file, 'a+');
	
	        if ($fp){
	            if (flock($fp, LOCK_EX)){
	                if (fwrite($fp,  $sql."\r\n") === FALSE){
	                    new Exception('ファイル書き込みに失敗しました');
	                }
	                flock($fp, LOCK_UN);
	            }
	        }
	        fclose($fp);
	    }catch(Exception $e){
	        new Exception($e->getMessage());
	    }
	}
	
	public static function convertEOL($string, $to = "\n")
	{
	    return preg_replace("/\r\n|\r|\n/", $to, $string);
	}
	
	/**
	 * APIを呼んで、サジェストキーワードを取得する
	 * @param unknown $uri
	 * @return NULL
	 */
	public static function sendAPIRequest($client, $site = 0, $method = "GET")
	{
	    try{
	        $response = $client->request($method);
	         
	        if ($response->isSuccessful()) {
	            
	            return $response->getBody();
	        } else {
                    	             
	            // some error
	            Com_Log::registApiErrorLog($response->getMessage(), "sendAPIRequest: try", $response->getStatus(), $client->getUri(true), $site);
	            
	            // some error
	            if ($response->getStatus() == 403) {
	                
                    return Com_Const::FORBIDDEN;
                    
	            } elseif ($response->getStatus() == 400) {
                    return Com_Const::EAPPIDERR;
                    
	            } elseif ($response->getStatus() == 503) {
	                
	                return Com_Const::SERVICEUNAVAILABLE;
	            }
	        }
	         
	    } catch (Exception $e) {
	        //echo '<p>エラーが発生しました (' .$e->getMessage(). ')</p>';
	        Com_Log::registErrorLog($e->getMessage(), "sendAPIRequest:catch", $client->getUri(true) , null, $site);
	    }
	    return null;
	}
	
	/**
	 * APIを呼んで、サジェストキーワードを取得する
	 * @param unknown $uri
	 * @return NULL
	 */
	public static function sendAPIRequestServer($client, $site = 0, $method = "GET")
	{
	    try{
	        $response = $client->request($method);
	
	        if ($response->isSuccessful()) {
	
	            return $response->getBody();
	        } else {
	
	            // some error
	            Com_Log::registApiErrorLog($response->getMessage(), "sendAPIRequestServer: try", $response->getStatus(), $client->getUri(true), $site);
	        }
	
	    } catch (Exception $e) {
	        //echo '<p>エラーが発生しました (' .$e->getMessage(). ')</p>';
	        Com_Log::registErrorLog($e->getMessage(), "sendAPIRequest:catch", null , null, $site);
	    }
	    return null;
	}
	
	public static function sendMulitRequest($url_list, $timeout = 10){
	
	    $mh = curl_multi_init();
	    foreach ($url_list as $i => $url) {
	        $conn[$i] = curl_init($url);
	        curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER,1);
	        curl_setopt($conn[$i], CURLOPT_FAILONERROR,1);
	        curl_setopt($conn[$i], CURLOPT_FOLLOWLOCATION,1);
	        curl_setopt($conn[$i], CURLOPT_MAXREDIRS,3);
	        curl_setopt($conn[$i], CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; CIBA)");
	        //SSL証明書を無視
	        curl_setopt($conn[$i], CURLOPT_SSL_VERIFYPEER,false);
	        curl_setopt($conn[$i], CURLOPT_SSL_VERIFYHOST,false);
	        //タイムアウト
	        if ($timeout){
	            curl_setopt($conn[$i], CURLOPT_TIMEOUT, $timeout);
	        }
	
	        curl_setopt($conn[$i], CURLOPT_NOSIGNAL, 1);
	
	        curl_multi_add_handle($mh, $conn[$i]);
	    }
	    //すべて取得するまでループ
	
	    curl_multi_exec($mh, $active);
	
	    $active = null;
	    do {
	        $mrc = curl_multi_exec($mh, $active);
	        usleep (250000);
	    } while ($active > 0);
	}
	
	public static function getFromReplaceServer($client, $posts, $site)
	{
	    try {
	        
	        $servers = array();
	        if ($site == Com_Const::GOOGLE) {
	            $fp = self::getLock("server", 60);
	        } else {
	            $fp = self::getLock("server");
	        }
	        
	    	for($line = 1; !feof($fp); $line++) {
	            $lines = fgets($fp);
	            if($lines) {
	                $tmp = explode("---", $lines);
	                $servers[$tmp[0]] = trim(preg_replace('/\s+/', ' ', $tmp[1]));
	            }
	        }
	        
	        $retVal = false;
	
	        foreach ($servers as $key => $value) {
	
	            $minutes = 0;
	            if ($value != 1) {
	                $diff = time(true) - strtotime($value);
	                $minutes = (int)($diff / 60);
	            }
	
	            if ($value == 1 ||
	                    $minutes > 30 ||
	                    (4 < $minutes && $minutes < 8) ||
	                    (14 < $minutes && $minutes < 18) ||
	                    (24 < $minutes && $minutes < 28)) {
	
	                        // ★★★★★★
	                        $client = new Zend_Http_Client();
	                        $client->setConfig(array(
	                                'adapter'   => 'Zend_Http_Client_Adapter_Curl',
	                                'keepalive' => true,
	                                'curloptions' => array(CURLOPT_FOLLOWLOCATION => false),
	                        ));
	                         
	                        $client->setUri($key);
	                        $client->setParameterPost($posts);
	                        // ★★★★★★
	                        
	                        $response = Com_Util::sendAPIRequestServer($client, $site, "POST");
	
	                        if ($response == null || $response == Com_Const::FORBIDDEN  ) {
	                            
	                            if ($servers[$key] == 1 && $response == Com_Const::FORBIDDEN) {
	                                $servers[$key] = date("Y/m/d H:i:s");
	                                Com_Log::registApiErrorLog("server down", $key, "", "server", $site);
	                            }

	                            Com_Log::registApiErrorLog("server wrong", $key, $response, "server", $site);
	                             
	                            sleep(2);
	                            continue;
	                        } else {
	                            $servers[$key] = 1;
	                            $retVal = array($response, $key);

	                            Com_Log::registApiErrorLog("server change", $key, $response, "", $site);
	                            break;
	                        }
	                    }
	        }
	
	        // 書き戻す
	        $contents = "";
	        foreach($servers as $key => $value) {
	            $contents .= ($key."---".$value."\n");
	        }
	
	        try {
	            if (!empty($contents)) {
	                ftruncate($fp, 0);
	                fwrite($fp,  $contents);
	            }
                flock($fp, LOCK_UN);
                fclose($fp);
            } catch(Exception $e) {
	           return false;
            }
	        
	        return $retVal;
	
	    } catch (Exception $e) {
	        Com_Log::registApiErrorLog("some Exception", $e->getMessage(), "", "", $site);

	        try {
	            if ($fp) {
	                flock($fp, LOCK_UN);
	                fclose($fp);
	            }
	        } catch (Exception $e) {
	           Com_Log::registApiErrorLog("Util line-243:", $e->getMessage(), "", "", $site);
	        }
	        return false;
	    }
	}
	
	public static function getLock($filename, $timeout = Com_Const::FILE_LOCK_TIMEOUT) {
	
	    $filepath = "log/".$filename;
	    
	    $fp = fopen($filepath, 'a+');
	    $start = time(true);
	    
        while (!flock ($fp, LOCK_EX | LOCK_NB)) {
            if ((time(true) - $start) > $timeout) {
        
                unlink($filepath);
                $fp = fopen($filepath, 'a+');
                
                $errMsg = array(date('Y-m-d H:i:s'), $filepath);
                Com_Util::write("lock_error", implode(": ", $errMsg)."\n", "a+");
                break;
            }
            sleep(1);
        }
        
	    return $fp;
	}
	
	public static function releaseLock($fp) {
	    
        if ($fp) {
            try {
                flock($fp, LOCK_UN);
                fclose($fp);
            } catch(Exception $e) {
                fclose($fp);
            }
        }
	}
	
	static function write($filename, $content, $mode = 'w'){
	
	    try{
	        $fp = fopen("log/".$filename, $mode);
	
	        if ($fp){
	            if (flock($fp, LOCK_EX)){
	                if (fwrite($fp,  $content) === FALSE){
	                    new Exception('ファイル書き込みに失敗しました');
	                }
	                flock($fp, LOCK_UN);
	            }
	        }
	        fclose($fp);
	    }catch(Exception $e){
	        new Exception($e->getMessage());
	    }
	}
	
	
	static function read($file_name){
	
	    $file_name = "log/".$file_name;
	    $text = null;
	    if(is_file($file_name)){
	        $fp = fopen($file_name,'r');
	        for($line = 1; !feof($fp); $line++){
	            $lines = fgets($fp);
	            if($lines){
	                $text .= $lines;
	            }
	        }
	        fclose($fp);
	    }else{
	        print 'ファイルがありません';
	        exit;
	    }
	    return $text;
	}
}