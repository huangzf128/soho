<?php
class Keyword_Model_SuggestKeyword extends Db_Abstract{
    
    private $keyword;
    private $registdt;
    private $suggestAry;
    
    private $strSuggestKeywords;    /* 検索結果 */
    private $rstCnt;                /* 結果件数 */
    private $indexTab;              /* index テーブル */
    private $tdCnt;                 /* index テーブルの列数 */
    private $pnum;
    private $serverEncode;
    
    /**
     * init
     * @param unknown $keyword
     */
    function __construct($keyword) {
        
    	$this->keyword = trim(mb_convert_encoding($keyword, "utf-8","auto"));
    	$this->indexTab = "<table id='indextab'><tr>";
    	$this->rstCnt = 0;
    	$this->tdCnt = 21;
    	$this->strSuggestKeywords = "";
    	$this->serverEncode = "EUC-JP";

    	/** 件楽履歴頁に一頁あたりのキーワード件数 */
    	$this->pnum = 100;
    	
    	/** 検索キーワードのブロック方法:  0:ブロックしない;1:完全一致;2:部分一致*/
    	$this->blockFlag = 2;
    	 
    	$this->suggestAry = array("", " ",
    			"あ", "い", "う", "え", "お", "か", "き", "く", "け", "こ",
    			"さ", "し", "す", "せ", "そ", "た", "ち", "つ", "て", "と",
    			"な", "に", "ぬ", "ね", "の", "は", "ひ", "ふ", "へ", "ほ",
    			"ま", "み", "む", "め", "も", "や", "ゆ", "よ", 
    	        "ら", "り", "る", "れ",	"ろ", "わ",
    			"が", "ぎ", "ぐ", "げ", "ご", "ざ", "じ", "ず", "ぜ", "ぞ",
    			"だ", "ぢ", "づ", "で", "ど", "ば", "び", "ぶ", "べ", "ぼ",
    			"ぱ", "ぴ", "ぷ", "ぺ", "ぽ",
    			"a", "b", "c", "d", "e", "f", "g", "h", "i", "j",
    			"k", "l", "m", "n", "o", "p", "q", "r", "s", "t",
    			"u", "v", "w", "x", "y", "z",
    			"0", "1", "2", "3", "4", "5", "6", "7", "8", "9"
    	);    	 
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
        	
    public function isBlockKeyword()
    {
    	$dir = "filter";
    	$name = "blockKeyword.txt";
    	$blockFile = $dir. DIRECTORY_SEPARATOR. $name;
    	$fileContent = file_get_contents($blockFile);
    	
    	if($this->blockFlag === 1){
    		// 全部一致
    		if(false !== strpos("\r\n".$fileContent, "\r\n".$this->keyword."\r\n")) {
    			$this->errOutput("blockKeyword_log.txt", date("Y-m-d H:i:s")." : keyword=[".$this->keyword. "]; ip=[". $this->getClientIp()."];");
    			return true;
    		}
    	} elseif ($this->blockFlag === 2) {
    		// 部分一致
    		$blockKeywordAry = explode("\r\n", $fileContent);
    		for($i = 0, $cnt = count($blockKeywordAry); $i < $cnt; $i++){
	    		if(!empty($blockKeywordAry[$i]) && false !== strpos($this->keyword, $blockKeywordAry[$i])) {
	    			$this->errOutput("blockKeyword_log.txt", date("Y-m-d H:i:s")." : keyword=[".$this->keyword. "]; ip=[". $this->getClientIp()."];");
	    			return true;
	    		}
    		}    		
    	}    	
    	return false;
    }
    
    /**
     * 検索
     */
    public function getSuggestKeyword()
    {
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
       		$client->setUri("http://www.google.co.jp/complete/search?hl=en&q=".urlencode($newKeyword)."&output=toolbar");
            
            //send request
            $response = Com_Util::sendAPIRequest($client);
            if ($response !== null) {
            	$this->parseXMLResponse($response, $newKeyword, $i);
            	
            } else {
                // error, 403 forbidden
//             	$this->makeIndex($i, false);
            	$errorFlg = true;
            	break;
            }
        }        
    	//index
    	$this->indexTab .= "</tr></table>";
    	
    	if($errorFlg){
    		$this->strSuggestKeywords = "<font color='red'>サーバーが混雑しているため、しばらく経ってからご利用ください。</font><br/><br/>※以下の姉妹サイトもお試しください。<br /><a href=\"http://www.yakw.net/\"target=\"_blank\">ヤフーサジェスト キーワード一括ＤＬツール</a><br /><a href=\"http://www.bskw.net/\" target=\"_blank\">ビングサジェストキーワード一括ＤＬツール</a><br /><a href=\"http://www.azkw.net/\" target=\"_blank\">アマゾンサジェストキーワード一括ＤＬツール</a><br /><a href=\"http://www.ytkw.net/\" target=\"_blank\">ユーチューブサジェストキーワード一括ＤＬツール</a><br/><br/>";
    	}
    }
    
    
    /**
     * 検索結果をDBに登録する
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Exception
     */
    public function registSearchHistoryResult(){
        
        try {
        	$searchHistoryEntity = new Keyword_Model_Entities_SearchHistory();
        
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
        
        $fp = fopen("history/archive/".str_replace(array("/", " ", ":", "-"), "", $this->registdt)."_".$keyword.".html", 'w');
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
        $resultFile = @file_get_contents("history/archive/".$fileName.".html");
        return $resultFile;        
    }
    
    /**
     * AJAX検索
     */
    public function getSuggestKeywordAjax()
    {
        //initial
        $client = new Zend_Http_Client();
        $config = array(
        		'adapter'   => 'Zend_Http_Client_Adapter_Curl',
        		'curloptions' => array(CURLOPT_FOLLOWLOCATION => false),
        );
        $client->setConfig($config);        
        $client->setUri("http://www.google.com/complete/search?hl=en&output=toolbar&q=".urlencode($this->keyword));
        
        //send request
        $response = Com_Util::sendAPIRequest($client);
        if ($response !== null) {
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
        	$searchHistoryEntity = new Keyword_Model_Entities_SearchHistory();
        	$result = $searchHistoryEntity->getList(($currentNo - 1) * $this->pnum, $this->pnum);
        	return $result;
        		
        } catch (Zend_Db_Adapter_Exception $e) {
            self::errOutput("error", $e->getMessage());

        	throw $e;
        } catch (Zend_Exception $e) {
            self::errOutput("error", $e->getMessage());

        	throw $e;
        }        
    }

    /**
    * 検索履歴を取得する
    * @throws Zend_Db_Adapter_Exception
    * @throws Zend_Exception
    * @return unknown
    */
    public function getSearchDeleteList($keyword)
    {
    	try {
    		$searchHistoryEntity = new Keyword_Model_Entities_SearchHistory();
    		$result = $searchHistoryEntity->getListByKeyword($keyword);
    		return $result;
    
    	} catch (Zend_Db_Adapter_Exception $e) {
    		throw $e;
    	} catch (Zend_Exception $e) {
    		throw $e;
    	}
    }
    
    
    public function getSearchHistoryListPageNo($currentNo)
    {
        try {
        	$searchHistoryEntity = new Keyword_Model_Entities_SearchHistory();        	 
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
    public function getSearchHistoryDetail($id)
    {
        try {
        	$searchHistoryEntity = new Keyword_Model_Entities_SearchHistory();
        	 
        	$result = $searchHistoryEntity->getRowById($id);
        	return $result;
        
        } catch (Zend_Db_Adapter_Exception $e) {
        	throw $e;
        } catch (Zend_Exception $e) {
        	throw $e;
        }        
    }
    
    public function deleteKeywordList($idList)
    {
        try {
        	$deleteEntity = new Keyword_Model_Entities_SearchHistory();
        	$idAry = explode(",", $idList);
	    	for($i = 0; $i < count($idAry); $i++){
	    		$id = $idAry[$i];
	    		$row = $deleteEntity->getKeywordById($id);
	    		
	    		$fileName = "";
	    		try {
	    			$time = str_replace(array("/", " ", ":", "-"), "", $row[0]["registdt"]);
	    			$keyword = htmlentities($row[0]['kword'], ENT_QUOTES, "UTF-8");
	    			
	    			$fileName = mb_convert_encoding ( $time."_".$keyword, $this->serverEncode, "auto");
	    			$from = "history/archive/".$fileName.".html";
	    			copy($from, "history/deletearchive/".$fileName.".html");
	    			unlink($from);
	    			
	    			$result = $deleteEntity->deleteRowById($id);
	    			
	    		} catch(Exception $e){
	    			$this->errOutput("deleteKeywordError_log.txt", date("Y-m-d H:i:s")." : id=[".$id. "];");
	    		}        		
	    	}
        	return $result;
        } catch (Zend_Db_Adapter_Exception $e) {
        	throw $e;
        } catch (Zend_Exception $e) {
        	throw $e;
        }        
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
    	$objContents = simplexml_load_string(mb_convert_encoding($xmlContents, "utf-8","SJIS-win"));
    	
    	$cnt = count($objContents->CompleteSuggestion);
    	$cnt = $cnt > Com_Const::MAX_RST_COUNT ? Com_Const::MAX_RST_COUNT : $cnt;
    	
    	if ($cnt > 0) {
			if ($id <> 1) {
				$this->strSuggestKeywords .=
				"<div id='".$id."' class='til'><a class='t_icon' href='#header'>|</a><span> ".$keyword."</span></div>";
			} else {
				$this->strSuggestKeywords .=
				"<div id='".$id."' class='til'><a class='t_icon' href='#header'>|</a><span> ".$keyword."_</span></div>";
			}
    
        	$this->strSuggestKeywords.= "<ul>";
        	for($i = 0; $i < $cnt; $i++){
        		$suggestKeyword = $objContents->CompleteSuggestion[$i]->suggestion;
        		foreach($suggestKeyword->attributes() as $key => $val) {
        			 
        			if($keyword != $val){
        				$this->strSuggestKeywords.=
        				"<li>
            		     <span class='sugkey'>".(string)$val."</span>";
        				$this->strSuggestKeywords.="</li>";
        				$this->rstCnt++;
        			}
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
        $objContents = simplexml_load_string(mb_convert_encoding($xmlContents, "utf-8","SJIS-win"));
        $cnt = count($objContents->CompleteSuggestion);
        
        $this->strSuggestKeywords.= "<ul>";
        for($i = 0; $i < $cnt; $i++){
        	$suggestKeyword = $objContents->CompleteSuggestion[$i]->suggestion;
        	foreach($suggestKeyword->attributes() as $key => $val) {
        		 
        		if($this->keyword != $val){
        			$this->strSuggestKeywords.=
        			"<li>
        		     <span class='sugkey'>".(string)$val."</span>";
        				
        			$this->strSuggestKeywords.="</li>";
        			$this->rstCnt++;
        		}
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
    
    private function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
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