<?php

Class Com_Util
{
	public function __construct(){}
	
	public static function encrypt($plain_text) {
	    
// 	    $c_t = openssl_encrypt($plain_text, 'AES-128-ECB', Com_Const::KEY);
// 	    return $c_t;
	    return $plain_text;
	}
	
	public static function decrypt($crypt_text) {
// 	    $p_t = openssl_decrypt($crypt_text, 'AES-128-ECB', Com_Const::KEY);
// 	    return $p_t;
	    
	    return $crypt_text;
	}
	
	public static function isHttps() {
	    // httpsで通信しているかどうか
	    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
	}
	
	public static function filter($str) {
	    $search = array("&");
	    $replace = array("＆");
	    
	    return str_replace($search, $replace, $str);
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
	         
	    } catch (Zend_Http_Client_Exception $e) {
	        //echo '<p>エラーが発生しました (' .$e->getMessage(). ')</p>';
	        Com_Log::registErrorLog($e->getMessage(), "sendAPIRequest:catch", null , null, $site);
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
	            Com_Log::registApiErrorLog($response->getMessage(), "sendAPIRequest: try", $response->getStatus(), $client->getUri(true), $site);
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
	
	public static function getFromReplaceServer($client, $site)
	{
	    try {
	        
	        $file_name = "log/server";
	        $servers = array();
	
	        $fp = fopen($file_name, 'a+');
	        while (!flock ($fp, LOCK_EX)) {
	            usleep(1000);
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
	
	            Com_Log::registApiErrorLog("test change 1", $key, $response, $servers[$key], $site);
	             
	            if ($value == 1 ||
	                    $minutes > 30 ||
	                    (4 < $minutes && $minutes < 8) ||
	                    (14 < $minutes && $minutes < 18) ||
	                    (24 < $minutes && $minutes < 28)) {
	
                            // ★★★★★★
	                        $client->setUri($key);
	                        // ★★★★★★
	                        Com_Log::registApiErrorLog("test change 2", $key, $response, $servers[$key], $site);
	
	                        $response = Com_Util::sendAPIRequestServer($client, $site, "POST");
	                        Com_Log::registApiErrorLog("test change", $key, $response, $servers[$key], $site);
	
	                        if ($response == null || $response == Com_Const::FORBIDDEN  ) {
	                            
	                            if ($servers[$key] == 1 && $response == Com_Const::FORBIDDEN) {
	                                $servers[$key] = date("Y/m/d H:i:s");
	                                Com_Log::registApiErrorLog("server down", $key, "", "server", $site);
	                            }
                                else {
                                    Com_Log::registApiErrorLog("test change 3", $key, $response, $servers[$key], $site);
                                }
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
                ftruncate($fp, 0);
                fwrite($fp,  $contents);
                flock($fp, LOCK_UN);
                fclose($fp);
            } catch(Exception $e) {
               Com_Log::registApiErrorLog("exception", $e->getMessage(), "", "", $site);
	           return false;
            }
	        
	        return $retVal;
	
	    } catch (Exception $e) {
	        Com_Log::registApiErrorLog("some Exception", $e->getMessage(), "", "", $site);
	        return false;
	    }
	}
	
	public static function readLock($filename) {
	
	    $fp = fopen("log/".$filename, 'a+');
	    while (!flock ($fp, LOCK_EX)) {
	        usleep(1000);
	    }
	
	    for($line = 1; !feof($fp); $line++) {
	        $lines = fgets($fp);
	        if($lines) {
	            $tmp = explode("---", $lines);
	            $servers[$tmp[0]] = trim(preg_replace('/\s+/', ' ', $tmp[1]));
	        }
	    }
	
	    return array($fp, $servers);
	}
	
	public static function releaseLock($fp, $servers) {
	    
        // 書き戻す
        $contents = null;
	    if ($servers != null && is_array($servers)) {
	        foreach($servers as $key => $value) {
	            $contents .= ($key."---".$value."\n");
	        }
	    }
	    
	    if ($fp) {
	        try {
	            if ($contents !== null) {
	                ftruncate($fp, 0);
	                fwrite($fp,  $contents);
	            }

	            flock($fp, LOCK_UN);
	            fclose($fp);
	        } catch(Exception $e) {
	            fclose($fp);
	            return false;
	        }
	    }
	}
	
	static function write($filename, $content){
	
	    try{
	        $fp = fopen("log/".$filename, 'w');
	
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