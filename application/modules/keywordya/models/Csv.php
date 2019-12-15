<?php
class Keywordya_Model_Csv extends Db_Abstract{
    
    private $callApiTimes;
    function __construct() { }
    
    // 予約登録
    public function registCsvOrder($userid, $historyid) {
        $data = array();
        $data["userid"] = $userid;
        $data["historyid"] = $historyid;
        $data["updatedt"] = date('Y-m-d H:i:s');
        $data["site"] = Com_Const::YAHOO;
        
        $dao = new Db_CsvOrderUser();
        return $dao->regist($data);
    }
    
    // 予約したCSVを取得する
    public function getCsvOrderList($userid) {
        $dao = new Db_CsvOrderUser();
        $orders = $dao->getCsvOrderList($userid, Com_Const::YAHOO);
        return $orders;
    }
    
    public function getNotExecuteCsvId($usreid) {
        $dao = new Db_CsvOrderUser();
        $rows = $dao->getNotExecuteCsv($usreid, Com_Const::YAHOO);
        if (count($rows)) {
            return $rows[0]["historyid"];
        } else {
            return false;
        }
    }
    
    public function getWaitingCount($usreid) {
        $dao = new Db_CsvOrderUser();
        $rows = $dao->getNotExecuteCsv($usreid, Com_Const::YAHOO);
        return count($rows);
    }
    
    // 実行中の処理があるか
    public function hasExecutingCsv($userid) {
        $dao = new Db_CsvOrderUser();
        $rows = $dao->getExecutingCsv($userid, Com_Const::YAHOO);
        if (count($rows) == 0) {
            return false;
        }
        return true;
    }
    
    // 検索結果の<html>タグを外して、Csv取得予約として、expandresultに登録
    public function registExpand($historyid)
    {
        $searchHistoryEntity = new Keywordya_Model_Entities_SearchHistory();
        $result = $searchHistoryEntity->getRowById($historyid);
        
        // タグはずす
        $skStr = $this->removeSkTag($result["sk"]);
        
        $expandDao = new Db_ExpandResult();
        $data = array();
        $data["historyid"] = $historyid;
        $data["result"] = $skStr;
        $data["updatedt"] = date('Y-m-d H:i:s');
        $data["status"] = 0;
        $data["site"] = Com_Const::YAHOO;
	    $data["interruptinfo"] = "0--0";        
        $rst = $expandDao->regist($data);
        
    	return $rst;
	}
	
	public function updateExpandLevel($historyid, $expandKeywords, $level, $interruptinfo)
	{
	    $dao = new Db_ExpandResult();
	    
	    $data = array();
	    $data["level".$level] = $expandKeywords;
	    $data["updatedt"] = date('Y-m-d H:i:s');
	    $data["interruptinfo"] = $interruptinfo;
	    
	    $where = array();
	    $where["historyid = ?"] = $historyid;
	    $where["site = ?"] = Com_Const::YAHOO;
	    
	    $rst = $dao->updateExpand($data, $where);
	    
	    return $rst;
	}
	
	public function updateExpandRand($historyid, $interruptinfo) {
	    $dao = new Db_ExpandResult();
	    
	    $data = array();
	    $data["updatedt"] = date('Y-m-d H:i:s');
	    $data["interruptinfo"] = $interruptinfo;
	    
	    $where = array();
	    $where["historyid = ?"] = $historyid;
	    $where["site = ?"] = Com_Const::YAHOO;
	    
	    $rst = $dao->updateExpand($data, $where);
	    return $rst;
	}
	
	public function getExpandResult($historyid){
	    $dao = new Db_ExpandResult();
	    $row = $dao->getRowById($historyid, Com_Const::YAHOO);
	    return $row;
	}
	
	public function deleteOrderCsv($historyid, $userid) {
	    $dao = new Db_CsvOrderUser();
        $dao->deleteOne($historyid, $userid, Com_Const::YAHOO);
        
        $dao = new Db_ExpandResult();
        $dao->deleteOne($historyid, Com_Const::YAHOO);
	}
	
	public function updateExpandStatus($historyid, $status)
	{
	    $dao = new Db_ExpandResult();
	     
	    $data = array();
	    $data["status"] = $status;
	    $data["updatedt"] = date('Y-m-d H:i:s');
	     
	    $where = array();
	    $where["historyid = ?"] = $historyid;
	    $where["site = ?"] = Com_Const::YAHOO;
	     
	    $rst = $dao->updateExpand($data, $where);
	    return $rst;
	}
	
	/**
	 * 検索結果を展開する
	 * @param unknown $sk
	 */
	public function expandLevel($skStr, $startTime, $interruptRst, $interruptGrpNm) {
	    // parse
	    $skAry = $this->parseSkStrToAry($skStr, $interruptGrpNm);
	    $eappid = Com_Util::read("eappid.txt");
	    $eappidRetryCount = Com_Const::EAPPID_RETRY_COUNT;
	    
	    // expand
	    $client = new Zend_Http_Client();
	    $client->setConfig(array(
	            'adapter'   => 'Zend_Http_Client_Adapter_Curl',
	            'keepalive' => true,
	            'timeout' => 6,
	            'curloptions' => array(CURLOPT_FOLLOWLOCATION => false),
	    ));
	    
	    // $api = "http://www.kword.lrv.jp/result";
	     
	    $commonapi = array("url" => Com_Const::API_YAHOO,  "per" => 25);
	    $x = array("url" => "http://medical-blog.net/kw/result",  "per" => 25);
	    $leo = array("url" => "http://www.kword.lrv.jp/result",  "per" => 20);
	    $core = array("url" => "http://kword.coresv.com/kw/result",  "per" => 20);
	    $sakura = array("url" => "http://ad8-ssl.sakura.ne.jp/kw/result", "per" => 10);
	    
	    $urls = array($commonapi, $x, $leo, $core, $sakura);
	    $api = $this->randSelect($urls);
	    
	    
	    $expandKeywords = $interruptRst;
	    foreach ($skAry as $groupNm => $keyword) {
	        
	        if ($keyword == null || trim($keyword) == "") {
                continue;
	        }
	        
	        do {
	            $fp = Com_Util::getLock("csvlock");
	            
	            $posts = array(
	                    'site' => Com_Const::YAHOO,
	                    'keyword' => urlencode($keyword)
	            );
	            
	            try {
	                if ($api === Com_Const::API_YAHOO) {
	                    $client->setUri($api."p=".urlencode($keyword)."&eappid=".$eappid);
	                    $response = Com_Util::sendAPIRequest($client, Com_Const::YAHOO);
	                } else {
	                    // other server
	                    $client->setUri($api);
	                    $client->setParameterPost($posts);
	                    $response = Com_Util::sendAPIRequest($client, Com_Const::YAHOO, "POST");
	                }
	                 
	                $this->callApiTimes++;
	                usleep(200 * 1000);
	            } catch (Exception $e) {}
	             
	            Com_Util::releaseLock($fp);
	             
	            usleep(500 * 1000);
	            
	            if($response == Com_Const::EAPPIDERR){
	            
	                // eappid無効
	                if($eappidRetryCount > 0) {
	                    // eappid再取得
	                    $eappid = $this->getEAppid();
	                    Com_Util::write("eappid.txt", $eappid);
	                } else {
	                    return Com_Const::FORBIDDEN;
	                }
	            }
	            
	            if (!$response || in_array($response, array(Com_Const::FORBIDDEN, Com_Const::SERVICEUNAVAILABLE, Com_Const::EAPPIDERR)) ) {
	                 
	                $otherServerRes = Com_Util::getFromReplaceServer($client, $posts, Com_Const::YAHOO);
	                if ($otherServerRes == false) {
	                     
	                    Com_Log::registApiErrorLog("game", "over", "0件", mb_convert_encoding($keyword, "shift-jis"), Com_Const::YAHOO);
	                     
	                    return Com_Const::FORBIDDEN;
	                } else {
	                    // success
	                    $response = $otherServerRes[0];
	                    $api = $otherServerRes[1];
	                }
	            }
	            

	            if ($response == null) {
	                // some error
	                Com_Log::registApiErrorLog("some error", "over", $api, $keyword, Com_Const::YAHOO);
	                 
	                // return Com_Const::ERROR;	                
	            } else {
	                $rstWord = $this->parseXMLResponseCsv($response, $groupNm, $keyword);
	                if (!empty($rstWord)) {
	                    $expandKeywords .= $rstWord;
	                }
	                break;
	            }
	            
	        } while($eappidRetryCount--);
	        
	        
	        if (time(true) - $startTime > (Com_Const::EXECUTE_TIME - 10)
                || $this->callApiTimes > 500) {
                return array(Com_Const::INTERRUPTION, $expandKeywords, $groupNm, $this->callApiTimes);
	        }
	    }
	    return $expandKeywords;
	}
	
	/**
	 * CSVファイルを作成する
	 * @param unknown $expand
	 */
	public function makeCsv($expand) {
	    $csvdata = "";
	    
	    $skAry = $this->parseSkStrToAry($expand["result"], "");
	    
	    $level1Ary = !empty($expand["level1"]) ? $this->parseSkStrToAry($expand["level1"], "") : null;
	    $level2Ary = !empty($expand["level2"]) ? $this->parseSkStrToAry($expand["level2"], "") : null;
	    $level3Ary = !empty($expand["level3"]) ? $this->parseSkStrToAry($expand["level3"], "") : null;
	    
	    $file = fopen('php://output', 'w');
	    
	    foreach ($skAry as $gSk => $sk) {
	        
	        $rst = array(substr($gSk, 0, -1), $sk);
	        fputcsv($file, $this->encode_array($rst));
	        
	        // level1
	        for ($i = 0; $i < 10; $i++) {
	            
	            if (!empty($level1Ary) && array_key_exists($gSk.$i, $level1Ary) && !empty($level1Ary[$gSk.$i])) {
	                
	                $level1Rst = $rst;
	                $level1Rst[] = $level1Ary[$gSk.$i];
	                fputcsv($file, $this->encode_array($level1Rst));
	                
	                // leve2
	                for ($j = 0; $j < 10; $j++) {
	                     
	                    if (!empty($level2Ary) && array_key_exists($gSk.$i.$j, $level2Ary) && !empty($level2Ary[$gSk.$i.$j])) {
	                        
	                        $level2Rst = $level1Rst;
	                        $level2Rst[] = $level2Ary[$gSk.$i.$j];
	                        fputcsv($file, $this->encode_array($level2Rst));
	                
	                        // leve3
	                        for ($k = 0; $k < 10; $k++) {
	                        
	                            if (!empty($level3Ary) && array_key_exists($gSk.$i.$j.$k, $level3Ary) && !empty($level3Ary[$gSk.$i.$j.$k])) {
	                                 
	                                $level3Rst = $level2Rst;
	                                $level3Rst[] = $level3Ary[$gSk.$i.$j.$k];
	                                fputcsv($file, $this->encode_array($level3Rst));
	                                 
	                            } else {
	                                break;
	                            }
	                        }
	                        
	                    } else {
	                        break;
	                    }
	                }
	                
	            } else {
	                break;
	            }
	        }
	    }
	}
	
	// --------------------------------------------------------------
	// Private Function
	// --------------------------------------------------------------
	
	private function encode_array($ary) {
	
	    $nary = array();
	    for ($i = 0; $i < count($ary); $i++) {
	        $nary[$i] = mb_convert_encoding($ary[$i], "shift-jis", "auto");
	    }
	
	    return $nary;
	}
	
	// 検索結果のhtmlタグを削除する
	private function removeSkTag($sk) {
	    require_once 'simple_html_dom.php';
	     
	    $skDom = str_get_html($sk);
	    $skStr = "";
	     
	    $divs = $skDom->find("div");
		for ($i = 0; $i < count($divs); $i++) {
	        $groupNm = trim($divs[$i]->find("span", 0)->plaintext);
	        $lis = $divs[$i]->next_sibling()->find("li");
	        $cnt = count($lis);
	        
			if ($cnt > 0) {
	        	$skStr .= "<".$groupNm.">";
	            for($j = 0; $j < $cnt; $j++) {
	                $val = trim($lis[$j]->find("span", 0)->plaintext);
	                $skStr .= Com_Util::filter($val);
	                if ($j < $cnt - 1) {
	                    $skStr .= "&";
	                }
	            }
	        }
	    }
	    return $skStr;
	}
	
	private function parseSkStrToAry($skStr, $grpNm) {
	     
	    $skAry = array();
	    $groupAry = explode("<", ltrim($skStr, "<"));
	    
	    $isNeedProcess = empty($grpNm);
	    foreach($groupAry as $group) {
	        $g = explode(">", $group);
	        $k = explode("&", $g[1]);
	        for ($i = 0; $i < count($k); $i++) {
	            
	            if ($g[0].$i == $grpNm) {
	                $isNeedProcess = true;
	                continue;
	            }
	            if (!$isNeedProcess) {
	                continue;
	            }
	            $skAry[$g[0].$i] = $k[$i];
	        }
	    }
	    return $skAry;
	}
	
	// <groupNm>keyword&keyword&keyword
	private function parseXMLResponseCsv($xmlContents, $groupNm, $keyword) {
	    
    	preg_match("/ytopAssist\\((.*)\\)/", $xmlContents, $matches);
    	if($matches != null && count($matches) > 1) {
    		$json = $matches[1];
    	}
    	
    	$objContents = Zend_Json::decode($json);
    	$cnt = count($objContents[1]);
	    
	    if ($cnt == 0) {
	        //Com_Log::registApiErrorLog($xmlContents, "parseXMLResponseCsv", "0件", $keyword, Com_Const::YAHOO);
	        return "";
	    }
	    
	    $expandKeywords = "<".$groupNm.">";
	    $suggestKeyword = $objContents[1];
	    foreach($suggestKeyword as $key => $val) {
	    
	        if($val != null && trim($val) != "" && $keyword != $val){
                $expandKeywords .= Com_Util::filter($val)."&";
	        }
	    }
	    
	    return rtrim($expandKeywords,"&");
	}
	
	// ------------------------------------
	// Yahoo ホーム頁のEAppid取得専用
	// ------------------------------------
	
	private function sendRequest($url, $timeout = 10){
	
	    $ch = curl_init($url);
// 	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
// 	    curl_setopt($ch, CURLOPT_FAILONERROR,1);
// 	    //curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
// 	    curl_setopt($ch, CURLOPT_MAXREDIRS,3);
// 	    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)');
// 	    //SSL証明書を無視
// 	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
// 	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
// 	    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	
	    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; …) Gecko/20100101 Firefox/61.0');
	     
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	     
	    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	     
	    curl_setopt($ch, CURLOPT_COOKIEFILE, '/log/indexing.cookie');
	    curl_setopt($ch, CURLOPT_COOKIEJAR, '/log/indexing.cookie');
	    
	    $rc = curl_exec($ch);
	    $errMsg = null;
	    if($rc === false){
	        $errMsg = curl_error($ch);
	        Com_Util::errOutput("eappErr.txt", "Date:".date("y-m-d H:i:s").";userid=".$_SESSION['id'].
	                ";FunctionBase->sendRequest : Error Message: ".$errMsg.";url:".$url."\n");
	        //The requested URL returned error: 404
	    }
	    curl_close($ch);
	
	    if(isset($errMsg)) throw new Exception($errMsg);
	
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
	
	private function randSelect($array) {
	    $target = rand(1, 100);
	    foreach ($array as $val) {
	        if ($target <= $val['per']) {
	            return $val["url"];
	        } else {
	            $target -= $val['per'];
	        }
	    }
	}
	
	// -------------------------------------------------
	
	public function setCallApiTimes($time) {
	    $this->callApiTimes = $time;
	}
	public function getCallApiTimes() {
	    return $this->callApiTimes;
	}
}
