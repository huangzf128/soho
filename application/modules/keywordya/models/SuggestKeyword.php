<?php
class Keywordya_Model_SuggestKeyword extends Db_Abstract{
    
	private $keyword;
    private $registdt;
    private $suggestAry;
    
    
    private $strSuggestKeywords;        /* 検索結果 */
    private $rstCnt;                    /* 結果件数 */
    private $indexTab;                  /* index テーブル */
    private $tdCnt;                     /* index テーブルの列数 */
    
    private $pnum;
    
    private $serverEncode;
    
    private $eappid = "";
    
    /**
     * init
     * @param unknown $keyword
     */
    function __construct($keyword) {
        
    	$this->keyword = trim(mb_convert_encoding($keyword, "utf-8","UTF-8,eucjp-win,sjis-win"));
    	$this->indexTab = "<table class='indextab'><tr>";
    	$this->rstCnt = 0;
    	$this->tdCnt = 21;
    	$this->strSuggestKeywords = "";
    	$this->pnum = 100;
    	$this->serverEncode = "EUC-JP";
    	
    	/** 検索キーワードのブロック方法:  0:ブロックしない;1:完全一致;2:部分一致*/
    	$this->blockFlag = 2;
    	 
    	$this->suggestAry = array("", " ",
    			"あ", "い", "う", "え", "お", "か", "き", "く", "け", "こ",
    			"さ", "し", "す", "せ", "そ", "た", "ち", "つ", "て", "と",
    			"な",
    			"に", "ぬ", "ね", "の", "は", "ひ", "ふ", "へ", "ほ",
    			"ま", "み", "む", "め", "も", "や", "ゆ", "よ", "ら", "り", "る", "れ",
    			"ろ", "わ",
    			"が", "ぎ", "ぐ", "げ", "ご", "ざ", "じ", "ず", "ぜ", "ぞ",
    			"だ", "ぢ", "づ", "で", "ど", "ば", "び", "ぶ", "べ", "ぼ",
    			"ぱ", "ぴ", "ぷ", "ぺ", "ぽ",
    			"a", "b", "c", "d", "e", "f", "g", "h", "i", "j",
    			"k", "l", "m", "n", "o", "p", "q", "r", "s", "t",
    			"u", "v", "w", "x", "y", "z",
    			"0", "1", "2", "3", "4", "5", "6", "7", "8", "9"
    	);
    	
    	set_time_limit(120);
    }
    

    public function checkKeyword()
    {
    	if($this->keyword == "") {
    		return 0;
    	}else if(mb_strlen($this->keyword, 'UTF-8') > 25) {
    		return 1;
    	}
    	return true;
    }
    
    	
    public function isBlockKeyword($keyword)
    {
    	$dir = "filter";
    	$name = "blockKeyword.txt";
    	$blockFile = $dir. DIRECTORY_SEPARATOR. $name;
    	$fileContent = file_get_contents($blockFile);
    	$fileContent = Com_Util::convertEOL($fileContent, PHP_EOL);
    	
        if($this->blockFlag === 1){
    		// 全部一致
    		if(false !== strpos(PHP_EOL.$fileContent, PHP_EOL.$keyword.PHP_EOL)) {
    			$this->errOutput("blockKeyword_log.txt", date("Y-m-d H:i:s")." : keyword=[".$keyword. "]; ip=[". $this->getClientIp()."];");
    			return true;
    		}
    	} elseif ($this->blockFlag === 2) {
    		// 部分一致
    		$blockKeywordAry = explode(PHP_EOL, $fileContent);
    		for($i = 0, $cnt = count($blockKeywordAry); $i < $cnt; $i++){
	    		if(!empty($blockKeywordAry[$i]) && false !== strpos($keyword, $blockKeywordAry[$i])) {
	    			$this->errOutput("blockKeyword_log.txt", date("Y-m-d H:i:s")." : keyword=[".$keyword. "]; ip=[". $this->getClientIp()."];");
	    			return true;
	    		}
    		}    		
    	}
    	return false;
    }
    
    private function sendRequest($url, $timeout = 10){
    
    	$ch = curl_init($url);
//     	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
//     	curl_setopt($ch, CURLOPT_FAILONERROR,1);
//     	//curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
//     	curl_setopt($ch, CURLOPT_MAXREDIRS,3);
//     	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)');
//     	//SSL証明書を無視
//     	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
//     	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
//     	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    		
     	//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; …) Gecko/20100101 Firefox/61.0');
     	
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    	curl_setopt($ch, CURLOPT_FAILONERROR,1);
    	curl_setopt($ch, CURLOPT_MAXREDIRS,3);

    	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; Touch; rv:11.0) like Gecko');
    	 
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
    	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    	
    	//curl_setopt($ch, CURLOPT_COOKIEFILE, '/log/indexing.cookie');
    	//curl_setopt($ch, CURLOPT_COOKIEJAR, '/log/indexing.cookie');
    	 
    	$rc = curl_exec($ch);
    	$errMsg = null;
    	if($rc === false){
    		$errMsg = curl_error($ch);
    		$this->errOutput("eappErr.txt", "Date:".date("y-m-d H:i:s").";userid=".$_SESSION['id'].
    									";FunctionBase->sendRequest : Error Message: ".$errMsg.";url:".$url."\n");
    		//The requested URL returned error: 404
    	}
    	curl_close($ch);
    
    	if(isset($errMsg)) throw new Exception($errMsg);

    	//$this->errOutput("eappErr111.txt", $rc);
    	
    	return $rc;
    }
    
    private function getEAppid()
    {
    	//initial
    	$response = $this->sendRequest("https://www.yahoo.co.jp");
    	if($response !== null){
    		//$start = stripos($response, 'eappid\u0022:') + strlen("eappid\u0022:\u0022");
    		//$end = stripos($response, '\u0022', $start + strlen("\u0022:"));

    	    $keywordPos = stripos($response, 'eappid":');
    	    
    	    if ($keywordPos == false) {
    	        return "";
    	    }
    	    
    		$start = $keywordPos + strlen('eappid":"');
    		$end = stripos($response, '"', $start + strlen('":'));
    		
    		$response = substr($response, $start, $end - $start);
    		
    		$this->errOutput("eappErr.txt", date("Y-m-d H:i:s"));
    	}
    	return $response;    	 
    }
    
    /**
     * 検索
     */
    public function getSuggestKeyword()
    {
    	$this->eappid = $this->read("eappid.txt");
    	$eappidRetryCount = Com_Const::EAPPID_RETRY_COUNT;
    	
        //initial
        $client = new Zend_Http_Client();
        $client->setConfig(array(
        		'adapter'   => 'Zend_Http_Client_Adapter_Curl',
        		'keepalive' => true,
        		'curloptions' => array(CURLOPT_FOLLOWLOCATION => false),
        ));
        
        $errorFlg = false;
        $cnt = count($this->suggestAry);
        for($i = 0; $i < $cnt; $i++)
        {
            if ($this->suggestAry[$i] == ""){
                $newKeyword = $this->keyword;
            } else {
            	$newKeyword = $this->keyword." ".$this->suggestAry[$i];
           	}
           	
           	do {
       	        $client->setUri(Com_Const::API_YAHOO."p=".urlencode($newKeyword)."&eappid=".$this->eappid);
       	        //send request
       	        $response = Com_Util::sendAPIRequest($client, Com_Const::YAHOO);
       	        
       	        if($response == Com_Const::EAPPIDERR){
       	        
       	            // eappid無効
       	            if($eappidRetryCount > 0) {
       	                // eappid再取得
       	                $this->eappid = $this->getEAppid();
       	                $this->write("eappid.txt", $this->eappid);
       	            } else {
       	                //$this->makeIndex($i, false);
       	                $errorFlg = true;
       	                break;
       	            }
       	             
       	        } elseif ($response == Com_Const::FORBIDDEN || $response == null) {
       	            
       	            // error, 403 forbidden
       	            $errorFlg = true;
       	            break;
       	        } else {
       	            $this->parseXMLResponse($response, $newKeyword, $i);
       	            break;
       	        }
           	
           	} while($eappidRetryCount--);           	
            
           	if ($errorFlg) {
           	    break;
           	}
        }        
    	//index
    	$this->indexTab .= "</tr></table>";
    	
    	if($errorFlg){
    		$this->strSuggestKeywords = "<font color='red'>サーバーが混雑しているため、しばらく経ってからご利用ください。</font><br/><br/><br/>";
    		return false;
    	}
    	return true;
    }
    
    
    /**
     * 検索結果をDBに登録する
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Exception
     */
    public function registSearchHistoryResult(){
        
        try {
        	$searchHistoryEntity = new Keywordya_Model_Entities_SearchHistory();
        
        	$srchRst  = array();
        	$srchRst['registdt'] = $this->registdt;
        	$srchRst['kword'] = $this->keyword;
        	$srchRst['rstcnt'] = $this->rstCnt;
        	
        	$srchRst['indextab'] = $this->indexTab;
        	$srchRst['sk'] = $this->strSuggestKeywords;
        	
        	$srchRst['clientip'] = $this->getClientIp(false); //略称形式
        	 
        	return $searchHistoryEntity->regist($srchRst);
        		
        } catch (Zend_Db_Adapter_Exception $e) {
        	throw $e;
        } catch (Zend_Exception $e) {
        	throw $e;
        }        
    }
    
    /**
     * ファイルを保存する
     */
    public function saveResult($result){
        $keyword = mb_convert_encoding ( $this->keyword, $this->serverEncode, "auto");        
        
        $fp = fopen("history/archive_ya/".str_replace(array("/", " ", ":", "-"), "", $this->registdt)."_".$keyword.".html", 'w');
        fwrite($fp, $result);
        fclose($fp);        
    }
    
    /**
     * 検索結果を取得する
     * @param unknown $fileName
     * @return unknown
     */
    public function getResultFile($fileName) {
        $fileName = mb_convert_encoding ( $fileName, $this->serverEncode, "auto");
        $resultFile = @file_get_contents("history/archive_ya/".$fileName.".html");
        return $resultFile;        
    }
    
    /**
     * AJAX検索
     */
    public function getSuggestKeywordAjax()
    {
    	$this->eappid = $this->read("eappid.txt");
    	
        //initial
        $client = new Zend_Http_Client();
        $config = array(
        		'adapter'   => 'Zend_Http_Client_Adapter_Curl',
        		'curloptions' => array(CURLOPT_FOLLOWLOCATION => false),
        );
        $client->setConfig($config);        
        $client->setUri(Com_Const::API_YAHOO."p=".urlencode($this->keyword)."&eappid=".$this->eappid);
        
        //send request
        $response = Com_Util::sendAPIRequest($client, Com_Const::YAHOO);
        
        if($response == Com_Const::EAPPIDERR){
        
            // eappid再取得
            $this->eappid = $this->getEAppid();
            $this->write("eappid.txt", $this->eappid);
            
            $client->setUri(Com_Const::API_YAHOO."p=".urlencode($this->keyword)."&eappid=".$this->eappid);
            $response = Com_Util::sendAPIRequest($client, Com_Const::YAHOO);
            $this->parseXMLResponseAjax($response);
            
        } elseif ($response !== null) {
        	$this->parseXMLResponseAjax($response);
        }        
    }
    
    
    /**
     * 検索履歴を取得する
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Exception
     * @return unknown
     */
    public function getSearchHistoryList($currentNo)
    {
        try {
        	$searchHistoryEntity = new Keywordya_Model_Entities_SearchHistory();
        	$result = $searchHistoryEntity->getList(($currentNo - 1) * $this->pnum, $this->pnum);
        	$rows = $result->fetchAll();
        	
        	foreach ($rows as $index => $row) {
        	    $rows[$index]['clientip'] = $this->parseCorrectIp($row["clientip"]);
        	}
        	
        	return $rows;
        		
        } catch (Zend_Db_Adapter_Exception $e) {
            self::errOutput("error", $e->getMessage());

        	throw $e;
        } catch (Zend_Exception $e) {
            self::errOutput("error", $e->getMessage());

        	throw $e;
        }        
    }

    public function getSearchHistoryListPageNo($currentNo)
    {
        try {
        	$searchHistoryEntity = new Keywordya_Model_Entities_SearchHistory();        	 
        	$maxPageNo = ceil($searchHistoryEntity->getCount() / $this->pnum);
        	 
        	$pageNo = "<div id='pageno'><span class='snm'>ページ ：&nbsp;</span>".
        			$this->getPageNo($currentNo, $maxPageNo).
        			"<span id='up' style='display:none;'>keyword/keyword/get-Search-History-List?currentNo=</span>".
        			"</div>";
        	 
        	return $pageNo;
        
        } catch (Zend_Db_Adapter_Exception $e) {
        	throw $e;
        } catch (Zend_Exception $e) {
        	throw $e;
        }
    }

    /**
     * 
     * @param unknown $id
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Exception
     * @return unknown
     */
    public function getSearchHistoryDetail($registdt, $keyword)
    {
        try {
        	$searchHistoryEntity = new Keywordya_Model_Entities_SearchHistory();
        	$rows = $searchHistoryEntity->getRowByRegDt($registdt);
        	$cnt = count($rows);
        	 
        	if($cnt == 1) {
        	    return $rows[0];
        	} else if ($cnt > 1) {
        	    for($i = 0; $i < $cnt; $i++) {
        	        $row = $rows[$i];
        	        if ($row["kword"] == $keyword) {
        	            return $row;
        	        }
        	    }
        	}
        	
        	return null;
        
        } catch (Zend_Db_Adapter_Exception $e) {
        	throw $e;
        } catch (Zend_Exception $e) {
        	throw $e;
        }        
    }
    
    public function isBlockIp()
    {
        $dir = "filter";
        $name = "blockip.txt";
        $ipFile = $dir. DIRECTORY_SEPARATOR. $name;
        $fileContent = file_get_contents($ipFile);
        $ip = $this->get_client_ip();
         
        $ipAry = explode("\r\n", $fileContent);
    
        for($i = 0, $cnt = count($ipAry); $i < $cnt; $i++) {
    
            if(!empty($ipAry[$i])
                    && false !== strpos($ipAry[$i], $ip)) {
                        return true;
                    }
        }
        return false;
    }
    
    public function isValidIp()
    {
        $dir = "filter";
        $name = "ip.txt";
        $ipFile = $dir. DIRECTORY_SEPARATOR. $name;
        $fileContent = file_get_contents($ipFile);
        $ip = $this->get_client_ip();
         
        $ipAry = explode("\r\n", $fileContent);
    
        for($i = 0, $cnt = count($ipAry); $i < $cnt; $i++) {
    
            if(!empty($ipAry[$i])
                    && false !== strpos($ipAry[$i], $ip."=")) {
                        return true;
                    }
        }
        return false;
    }
    
    /*------------------------------------------------------------------------
    *
    *  private
    *
    *------------------------------------------------------------------------*/
    
    function getClientIp($type = true)
    {
    	if($type){
    	    $ip = "HTTP_CLIENT_IP=[".$this->getServerVariable("HTTP_CLIENT_IP")."];HTTP_X_FORWARDED_FOR=[".
    	    		      $this->getServerVariable("HTTP_X_FORWARDED_FOR")."]; REMOTE_ADDR=[".
    	    		      $this->getServerVariable("REMOTE_ADDR")."];";
    	}else{
    	    $ip = "[".$this->getServerVariable("HTTP_CLIENT_IP")."][".
    	    		      $this->getServerVariable("HTTP_X_FORWARDED_FOR")."][".
    	    		      $this->getServerVariable("REMOTE_ADDR")."]";
    	}
    	return $ip;
    }

    private function parseCorrectIp($ip) {
        $ary = array();
        preg_match('/\[(.*)\]\[(.*)\]\[(.*)\]/u', $ip, $ary);
    
        if (count($ary) >= 4) {
            if ($ary[2] != null) {
                return $ary[2];
            } else if ($ary[3] != null) {
                return $ary[3];
            }
        }
        return $ip;
    }
    
    private function get_client_ip() {
        $ipaddress = '';
    
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    
            else if(isset($_SERVER['REMOTE_ADDR']))
                $ipaddress = $_SERVER['REMOTE_ADDR'];
    
                else if(isset($_SERVER['HTTP_X_FORWARDED']))
                    $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    
                    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
                        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    
                        else
                            $ipaddress = 'UNKNOWN';
                            return $ipaddress;
    }
    
    private function getServerVariable($key) {
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        return "";
    }
    
    /**
     * APIレスポンスを解析する
     * @param unknown $xmlContents
     */
    private function parseXMLResponse($xmlContents, $keyword, $id)
    {   
    	preg_match("/ytopAssist\\((.*)\\)/", $xmlContents, $matches);
    	if($matches != null && count($matches) > 1) {
    		$json = $matches[1];
    	}
    	
    	$objContents = Zend_Json::decode($json);
    	$cnt = count($objContents[1]);
    
    	if ($cnt > 0) {
			if ($id <> 1) {
				$this->strSuggestKeywords .=
				"<div id='".$id."' class='til'><a class='t_icon' href='#header'>|</a><span> ".$keyword."</span></div>";
			} else {
				$this->strSuggestKeywords .=
				"<div id='".$id."' class='til'><a class='t_icon' href='#header'>|</a><span> ".$keyword."_</span></div>";
			}
    
        	$this->strSuggestKeywords.= "<ul>";
       		$suggestKeyword = $objContents[1];
       		foreach($suggestKeyword as $key => $val) {
        			 
       			if($keyword != $val && false == $this->isBlockKeyword((string)$val)){
       				$this->strSuggestKeywords.=
       				"<li>
           		     <span class='sugkey'>".Com_Util::filter($val)."</span>";
       				$this->strSuggestKeywords.="</li>";
       				$this->rstCnt++;
       			}
       		}
    	   	$this->strSuggestKeywords.= "</ul>";
    
			$this->strSuggestKeywords .= "<br/><br/>";
			//index
			$this->makeIndex($id, true);
    	} else {
			//index
			$this->makeIndex($id, false);
    	}
    }
    

    private function parseXMLResponseAjax($xmlContents)
    {
    	preg_match("/ytopAssist\\((.*)\\)/", $xmlContents, $matches);
    	if($matches != null && count($matches) > 1) {
    		$json = $matches[1];
    	}

    	$objContents = Zend_Json::decode($json);
    	$cnt = count($objContents[1]);
                
        $this->strSuggestKeywords.= "<ul>";
        
       	$suggestKeyword = $objContents[1];
       	foreach($suggestKeyword as $key => $val) {
        		 
       		if($this->keyword != $val && false == $this->isBlockKeyword((string)$val)){
       			$this->strSuggestKeywords.=
       			"<li>
       		     <span class='sugkey'>".(string)$val."</span>";
        				
       			$this->strSuggestKeywords.="</li>";
       			$this->rstCnt++;
       		}
       	}
        $this->strSuggestKeywords.= "</ul>";        
    }
    
    
    /**
     * キーワードテーブルを作成する
     * @param unknown $keyword
     * @param unknown $id
     * @param unknown $linkFlg
     */
    private function makeIndex($id, $linkFlg)
    {
    	if ($id == 2 || ($id <> 0 && $id % $this->tdCnt == 2)) {
    		$this->indexTab .= "</tr><tr>";
    	}
    
    	if ($linkFlg == false) {
    		if ($id == 0) {
    			$this->indexTab .= "<td colspan='".($this->tdCnt - 1)."' >&nbsp;".$this->keyword."</td>";
    		} elseif ($id == 1) {
    			$this->indexTab .= "<td>_</td>";
    		} else {
    			$this->indexTab .= "<td><a>".$this->suggestAry[$id]."</a></td>";
    		}
    	} else {
    
    		if ($id == 0) {
    			$this->indexTab .= "<td colspan='".($this->tdCnt - 1)."' style='text-align:left;'>&nbsp;<a href='#".$id."'>".$this->keyword."</a></td>";
    		} elseif ($id == 1) {
    			$this->indexTab .= "<td><a href='#".$id."'>&nbsp;</a></td>";
    		} else {
    			$this->indexTab .= "<td><a href='#".$id."'>".$this->suggestAry[$id]."</a></td>";
    		}
    	}
    }
    
    private function errOutput($errSql_file, $msg){
    
    	try{
    		$fp = fopen("log/".$errSql_file, 'a+');
    
    		if ($fp){
    			if (flock($fp, LOCK_EX)){
    				if (fwrite($fp,  $msg."\r\n") === FALSE){
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
    
    
    /***
     * 頁番号を生成する
    */
    private function getPageNo($page, $tallPage){
    
    	$pCon="";
    	$page = $page > $tallPage ? $tallPage : $page;
    	
    	// 
    	if ($tallPage <= 2 * $this->pnum + 3 ){
    		for($i = 1; $i <= $tallPage ; $i++){
    			$pCon .= ($page != $i) ? "<a href='".$this->getPageNoUrl($i)."'>".$i.
    			"<span style='display:none;'>".$i."</span></a> &nbsp;" : $i."&nbsp;&nbsp;";
    		}
    	}elseif($page <= $this->pnum + 2){
    			
    		for($i = 1; $i <= 2 * $this->pnum + 3; $i++){
    			$pCon .= ($page != $i) ? "<a href='".$this->getPageNoUrl($i)."'>".$i.
    			"<span style='display:none;'>".$i."</span></a> &nbsp;" : $i."&nbsp;&nbsp;";
    		}
    		$pCon .= ("..."."<a href='".$this->getPageNoUrl($tallPage)."'>".$tallPage.
    				"<span style='display:none;'>".$tallPage."</span></a> &nbsp;");
    	}elseif($tallPage - $page <= $this->pnum ){
    			
    		for($i = $tallPage - (2 * $this->pnum + 3); $i <= $tallPage ; $i++){
    			$pCon .= ($page != $i) ? "<a href='".$this->getPageNoUrl($i)."'>".$i.
    			"<span style='display:none;'>".$i."</span></a> &nbsp;" : $i."&nbsp;&nbsp;";
    		}
    		$pCon = "<a href='".$this->getPageNoUrl(1)."'>1<span style='display:none;'>1</span></a> &nbsp;"."...".$pCon;
    	}else{
    			
    		for($i = $page - $this->pnum; $i <= $page + $this->pnum ; $i++){
    			$pCon .= ($page != $i) ? "<a href='".$this->getPageNoUrl($i)."'>".$i.
    			"<span style='display:none;'>".$i."</span></a> &nbsp;" : $i."&nbsp;&nbsp;";
    		}
    		$pCon = "<a href='".$this->getPageNoUrl(1)."'>1<span style='display:none;'>1</span></a> &nbsp;".
    				"...&nbsp;".$pCon."...&nbsp;".
    				"<a href='".$this->getPageNoUrl($tallPage)."'>".$tallPage.
    				"<span style='display:none;'>".$tallPage."</span></a> &nbsp;";
    	}
    
    	if($page <> 1){
    		$pCon = "&nbsp;&nbsp;<a href='".$this->getPageNoUrl($page - 1)."'>前へ<span style='display:none;'>".($page - 1)."</span></a> &nbsp;".$pCon;
    	}
    
    	if($page <> $tallPage){
    		$pCon .= "<a href='".$this->getPageNoUrl($page + 1)."'>次へ<span style='display:none;'>".($page + 1)."</span></a> &nbsp;" ;
    	}
    	return $pCon;
    }

    private function getPageNoUrl($pageNo){
    	return '/history/index'.$pageNo.'.html';
    }
    
    
    /*------------------------------------------------------------------------
     *
     *  getter, setter
     *
    ------------------------------------------------------------------------ */
    
    /**
     * @return the $rstCnt
     */
    public function getRstCnt() {
    	return $this->rstCnt;
    }    
    
    /**
     * @return the $indexTab
     */
    public function getIndexTab() {
    	return $this->indexTab;
    }
    
    /**
     * @return the $strSuggestKeywords
     */
    public function getStrSuggestKeywords() {
    	return $this->strSuggestKeywords;
    }
	
	/**
	 * @return the $keyword
	 */
	public function getKeyword() {
		return $this->keyword;
	}
	
	/**
	 * @param field_type $registdt
	 */
	public function setRegistdt($registdt) {
		$this->registdt = $registdt;
	}
	/**
	 * @return the $registdt
	 */
	public function getRegistdt() {
		return $this->registdt;
	}
}