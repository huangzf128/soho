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
        
//         $fp = Com_Util::getLock("suggest_lock", 10);
        $errorFlg = false;
        $cnt = count($this->suggestAry);
        
        try {
            
            for($i = 0; $i < $cnt; $i++)
            {
                if ($this->suggestAry[$i] == "") {
                    $newKeyword = $this->keyword;
                } else {
                    $newKeyword = $this->keyword." ".$this->suggestAry[$i];
                }
                $client->setUri(Com_Const::API_GOOGLE.urlencode($newKeyword));
            
                //send request
                $response = Com_Util::sendAPIRequest($client, Com_Const::GOOGLE);
                if ($response == Com_Const::FORBIDDEN || $response == null) {
                    // error, 403 forbidden
                    $errorFlg = true;
                    break;
                } else {
                    $this->parseXMLResponse($response, $newKeyword, $i);
                }
            }
            //index
            $this->indexTab .= "</tr></table>";
            
        } catch(Exception $e) {
        }
    	
//     	Com_Util::releaseLock($fp);
    	
    	if($errorFlg){
    		$this->strSuggestKeywords = "<font color='red'>サーバーが混雑しているため、しばらく経ってからご利用ください。</font><br/>（<a href=\"https://www.gskw.net/cao.pdf\" target=\"_blank\">混雑の回避方法はこちら</a>）<br/><br/>※以下の姉妹サイトもお試しください。<br /><a href=\"http://www.yakw.net/\"target=\"_blank\">ヤフーサジェスト キーワード一括ＤＬツール</a><br /><a href=\"http://www.bskw.net/\" target=\"_blank\">ビングサジェストキーワード一括ＤＬツール</a><br /><a href=\"http://www.azkw.net/\" target=\"_blank\">アマゾンサジェストキーワード一括ＤＬツール</a><br /><a href=\"http://www.ytkw.net/\" target=\"_blank\">ユーチューブサジェストキーワード一括ＤＬツール</a><br/><br/>";
    		return false;
    	}
    	return true;
    }
    
    public function getSuggestKeywordOtherServer($server = Com_Const::SUGGEST_SERVER_GOOGLE)
    {
        //initial
        $client = new Zend_Http_Client();
        $client->setConfig(array(
                'adapter'   => 'Zend_Http_Client_Adapter_Curl',
                'keepalive' => true,
                'curloptions' => array(CURLOPT_FOLLOWLOCATION => false),
        ));
    
        $url = str_replace("{keyword}", urlencode($this->keyword), $server);
        $url = str_replace("{p}", Com_Util::encrypt(date("Ymd")), $url);
        $client->setUri($url);

        //send request
        $response = Com_Util::sendAPIRequest($client, Com_Const::GOOGLE);
        if ($response == Com_Const::FORBIDDEN || $response == null) {
            // error, 403 forbidden
            return false;
        } else {
            
            try {
                $resAry = json_decode($response);
                
                if (is_object($resAry)) {
                    $this->strSuggestKeywords = $resAry->sk;
                    $this->indexTab = $resAry->indextab;
                    $this->rstCnt = $resAry->rstCnt;
                    
                    if (strpos($resAry->sk, "サーバーが混雑しているため") !== FALSE) {
                        return false;
                    }
                }
                
            } catch (Exception $e) {
                echo $e->getMessage();
                return false;
            }
        }
         
        return true;
    }
    
    
    public function getRecentKeyword() {
        $dao = new Keyword_Model_Entities_SearchHistory();
        $rows = $dao->getRecentKeyword($this->keyword);
        
        if (count($rows) > 0) {
            return $rows[0];
        }
        return false;
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
        	
        	$srchRst['indextabb'] = gzcompress($this->indexTab, 9);
        	$srchRst['skb'] = gzcompress($this->strSuggestKeywords, 9);
        	
        	$srchRst['clientip'] = $this->getClientIp(false); //略称形式

        	return $searchHistoryEntity->registb($srchRst);
            
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
        $response = Com_Util::sendAPIRequest($client, Com_Const::GOOGLE);
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
        	$rows = $searchHistoryEntity->getList(($currentNo - 1) * $this->pnum, $this->pnum);
        	
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
    public function getSearchHistoryDetail($registdt, $keyword)
    {
        try {
        	$searchHistoryEntity = new Keyword_Model_Entities_SearchHistory();
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
	    			//copy($from, "history/deletearchive/".$fileName.".html");
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
                    && $ipAry[$i] == $ip) {
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
// 		$ip = "[".$this->getServerVariable("HTTP_CLIENT_IP")."][".
// 		      $this->getServerVariable("REMOTE_ADDR")."]";
//     	return $ip;
    	
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
        
        preg_match('/\[(.*)\]\[(.*)\]/u', $ip, $ary);
        if (count($ary) == 3) {
            if ($ary[1] != null) {
                return $ary[1];
            } else if ($ary[2] != null) {
                return $ary[2];
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
        			 
        			if($keyword != $val && false == $this->isBlockKeyword($val)){
        				$this->strSuggestKeywords.=
        				"<li><span class='sugkey'>".Com_Util::filter($val)."</span>";
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
        		 
        		if($this->keyword != $val && false == $this->isBlockKeyword((string)$val)){
        			$this->strSuggestKeywords.=
        			"<li><span class='sugkey'>".(string)$val."</span>";
        				
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

	
    // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // maintenance
    // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	// remove duplicate
	public function delDuplicate($idFrom, $idTo) {
        
    	$count = 0;
	    $searchHistoryEntity = new Keyword_Model_Entities_SearchHistory();
	    
	    for ($j = $idFrom; $j < $idTo; $j+=10000) {
	        
	        $rows = $searchHistoryEntity->getRowByIdRange($j, $j + 10000);
	        $cnt = count($rows);
	        
	        for($i = 0; $i < $cnt; $i++) {
	            $row = $rows[$i];
	            $rst = $searchHistoryEntity->getDuplicateCount($row["kword"], $row["id"]);
	            if ($rst > 0) {
	                $this->deleteKeywordList($row["id"]);
	                //echo $row[id]."<br/>";
	                $count++;
	            }
	        }
	        echo $j;
	    }
        echo $count;
	}

    // remove 0 record
    public function delSearchHistoryZero($registdt)
    {
        try {
        	$searchHistoryEntity = new Keyword_Model_Entities_SearchHistory();
        	$rows = $searchHistoryEntity->getZeroRowByRegDtRange($registdt);
        	$cnt = count($rows);
            
            $idAry = "";
            for($i = 0; $i < $cnt; $i++) {
                $row = $rows[$i];
                $idAry[] = $row["id"];
            }

            $this->deleteKeywordList(implode(",", $idAry));
        	
        } catch (Zend_Db_Adapter_Exception $e) {
        	throw $e;
        } catch (Zend_Exception $e) {
        	throw $e;
        }
    }
    
    // --------------- backup csv --------------------
    
    // save to csv
    public function saveToCsv($idFrom, $idTo) {
         
        $count = 0;
        $searchHistoryEntity = new Keyword_Model_Entities_SearchHistory();
    
        $up = 1000;
         
        for ($j = $idFrom; $j < $idTo; $j+=$up) {
    
            $csvs = array();
    
            $rows = $searchHistoryEntity->getRowByIdRange($j, $j + $up);
            $cnt = count($rows);
            for($i = 0; $i < $cnt; $i++) {
                $row = $rows[$i];
                $csvs[] = array($row["id"], $row["registdt"], $row["kword"], $row["rstcnt"], $row["indextab"], $row["sk"], $row["clientip"]);
            }
    
            // create csv
            $filename = $j. "-". ($j + $up);
            $path = "history/csv/csv_".$filename.".csv";
            $f = fopen($path, "w");
            if ( $f ) {
                foreach($csvs as $line){
                    fputcsv($f, $line);
                }
            }
            fclose($f);
        }
        echo $count;
    }
    
    public function csvToDB($folder, $filenum) {
        $csvPath = "history/".$folder."/csv_{filenum}.csv";
        $searchHistoryEntity = new Keyword_Model_Entities_SearchHistory();
         
        $count = 0;
        $num = explode("-", $filenum);
        
        while($count < 10) {
            
            $from = $num[0] + 1000 * $count;
            $to = $num[1] + 1000 * $count;
            
            $path = str_replace("{filenum}", $from."-".$to, $csvPath);
            
            if (($handle = fopen($path, "r")) !== FALSE) {
                 
                while (($line = fgetcsv($handle)) !== FALSE) {
                    $line[4] = gzcompress($line[4], 9);
                    $line[5] = str_replace("        		     <span class='sugkey'>", "<span class='sugkey'>", $line[5]);
                    $line[5] = gzcompress($line[5], 9);
                    $searchHistoryEntity->registbWithId($line);
                }
                fclose($handle);
            }
            $count++;
            
            echo $path."<br/>";
            echo $line[0]."<br/>";
        }
        
    }
    
    public function compressDb() {

        $searchHistoryEntity = new Keyword_Model_Entities_SearchHistory();
         
        $rows = $searchHistoryEntity->getListForBflag();
        
        for ($i = 0; $i < count($rows); $i++) {
            $data = array();
            $where = array();
            
            $row = $rows[$i];
            
            $data["indextab"] = null;
            $data["indextabb"] = gzcompress($row["indextab"], 9);
            $data["sk"] = null;
            $data["skb"] = gzcompress($row["sk"], 9);
            $data["bflag"] = 1;
            
            $where["id"] = $row["id"];
            
            $searchHistoryEntity->updateb($data, $where);
        }
    }
     
}