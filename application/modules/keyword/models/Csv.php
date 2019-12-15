<?php
class Keyword_Model_Csv extends Db_Abstract{
    
    private $callApiTimes;
    function __construct() { }
    
    // 予約登録
    public function registCsvOrder($userid, $historyid) {
        $data = array();
        $data["userid"] = $userid;
        $data["historyid"] = $historyid;
        $data["updatedt"] = date('Y-m-d H:i:s');
        $data["site"] = Com_Const::GOOGLE;
        
        $dao = new Db_CsvOrderUser();
        return $dao->regist($data);
    }
    
    // 予約したCSVを取得する
    public function getCsvOrderList($userid) {
        $dao = new Db_CsvOrderUser();
        $orders = $dao->getCsvOrderList($userid, Com_Const::GOOGLE);
        
        foreach($orders as $row) {
            if ($row['registdt'] == null && $row['kword'] == null) {
                try {
                    $result = gzuncompress($row["resultb"]);
                    
                    if ($result != null) {
                        $posS = strpos($result, "<") + 1;
                        $posE = strpos($result, ">");
                        
                        $row['kword'] = substr($result, $posS, $posE - $posS);
                    }
                } catch(Exception $e) { }
                
            }
        }
        return $orders;
    }
    
    public function getNotExecuteCsvId($usreid) {
        $dao = new Db_CsvOrderUser();
        $rows = $dao->getNotExecuteCsv($usreid, Com_Const::GOOGLE);
        if (count($rows)) {
            return $rows[0]["historyid"];
        } else {
            return false;
        }
    }
    
    public function getWaitingCount($usreid) {
        $dao = new Db_CsvOrderUser();
        $rows = $dao->getNotExecuteCsv($usreid, Com_Const::GOOGLE);
        return count($rows);
    }
    
    // 実行中の処理があるか
    public function hasExecutingCsv($userid) {
        $dao = new Db_CsvOrderUser();
        $rows = $dao->getExecutingCsv($userid, Com_Const::GOOGLE);
        if (count($rows) == 0) {
            return false;
        }
        return true;
    }
    
    // 検索結果の<html>タグを外して、Csv取得予約として、expandresultに登録
    public function registExpand($historyid)
    {
        $searchHistoryEntity = new Keyword_Model_Entities_SearchHistory();
        $result = $searchHistoryEntity->getRowById($historyid);
        
        // タグはずす
        if ($result["bflag"] == 0) {
            $skStr = $this->removeSkTag($result["sk"]);
        } else {
            $skStr = $this->removeSkTag(gzuncompress($result["skb"]));
        }
        
        $expandDao = new Db_ExpandResult();
        $data = array();
        $data["historyid"] = $historyid;
        //$data["result"] = $skStr;
        $data["resultb"] = gzcompress($skStr, 9);
        $data["updatedt"] = date('Y-m-d H:i:s');
        $data["status"] = 0;
        $data["site"] = Com_Const::GOOGLE;
        $data["interruptinfo"] = "0--0";
        
        $rst = $expandDao->registb($data);
        
    	return $rst;
	}
	
	public function updateExpandLevel($historyid, $expandKeywords, $level, $interruptinfo)
	{
	    $dao = new Db_ExpandResult();
	    
	    $data = array();
	    //$data["level".$level] = $expandKeywords;
	    $data["level".$level."b"] = gzcompress($expandKeywords, 9);
	    $data["updatedt"] = date('Y-m-d H:i:s');
	    $data["interruptinfo"] = $interruptinfo;
	     
	    $where = array();
	    $where["historyid = ?"] = $historyid;
	    $where["site = ?"] = Com_Const::GOOGLE;
	    
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
	    $where["site = ?"] = Com_Const::GOOGLE;
	    
	    $rst = $dao->updateExpand($data, $where);
	    return $rst;
	}
	
	public function getExpandResult($historyid){
	    $dao = new Db_ExpandResult();
	    $row = $dao->getRowById($historyid, Com_Const::GOOGLE);
	    return $row;
	}
	
	public function deleteOrderCsv($historyid, $userid) {
	    $dao = new Db_CsvOrderUser();
        $dao->deleteOne($historyid, $userid, Com_Const::GOOGLE);
        
        $dao = new Db_ExpandResult();
        $dao->deleteOne($historyid, Com_Const::GOOGLE);
	}
	
	public function updateExpandStatus($historyid, $status)
	{
	    $dao = new Db_ExpandResult();
	     
	    $data = array();
	    $data["status"] = $status;
	    $data["updatedt"] = date('Y-m-d H:i:s');
	     
	    $where = array();
	    $where["historyid = ?"] = $historyid;
	    $where["site = ?"] = Com_Const::GOOGLE;
	    
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
	    // expand
	    $client = new Zend_Http_Client();
	    $client->setConfig(array(
	            'adapter'   => 'Zend_Http_Client_Adapter_Curl',
	            'keepalive' => true,
	            'curloptions' => array(CURLOPT_FOLLOWLOCATION => false),
	    ));
	    
	    // $api = Com_Const::CSV_SERVER_GOOGLE;

	    $commonapi = array("url" => Com_Const::API_GOOGLE,  "per" => 25);
	    $coolblog = array("url" => "http://ad8.coolblog.jp/kw/result", "per" => 20);
	    $core = array("url" => "http://kword.coresv.com/kw/result",  "per" => 20);
	    $leo = array("url" => "http://www.kword.lrv.jp/result",  "per" => 20);
	    $x = array("url" => "http://medical-blog.net/kw/result",  "per" => 15);
	     
	    $urls = array($commonapi, $coolblog, $core, $leo, $x);
	    $api = $this->randSelect($urls);
	    
	    $expandKeywords = $interruptRst;
	    $i = 1;
	    $count = count($skAry);
	    $keyRes = array();
	    foreach ($skAry as $groupNm => $keyword) {
	        
	        if ($keyword == null || trim($keyword) == "") {
                continue;
	        }
	        
	        try {
	            
	            if ($api === Com_Const::API_GOOGLE) {
	                
	                $fp = null;
	                $keyRes = array();
	                
	                $keyRes[$groupNm] = $keyword;
	                $posts = array(
	                        'site' => Com_Const::GOOGLE,
	                        'keyword' => urlencode(json_encode($keyRes))
	                );
	                
	                $client->setUri($api.urlencode($keyword));
	                $response = Com_Util::sendAPIRequest($client, Com_Const::GOOGLE);
	                
	            } else {
	                
	                $keyRes[$groupNm] = $keyword;
	                if (count($keyRes) < 40 && $i++ < $count) {
	                    continue;
	                }
	                 
	                $posts = array(
	                        'site' => Com_Const::GOOGLE,
	                        'keyword' => urlencode(json_encode($keyRes))
	                );
	                
	                $fp = Com_Util::getLock("csvlock", 60);
	                 
	                $client->setUri($api);
	                $client->setParameterPost($posts);
	                $response = Com_Util::sendAPIRequest($client, Com_Const::GOOGLE, "POST");
	            }
	            
	            usleep(400 * 1000);
	            $this->callApiTimes++;
	        } catch (Exception $e) {}
	        
	        if ($fp) {
	            Com_Util::releaseLock($fp);
	        }
	        
            usleep(200 * 1000);
	        
	        if (!$response || in_array($response, array(Com_Const::FORBIDDEN, Com_Const::SERVICEUNAVAILABLE, Com_Const::EAPPIDERR)) ) {
	            
	        	$otherServerRes = Com_Util::getFromReplaceServer($client, $posts, Com_Const::GOOGLE);
	            if ($otherServerRes == false) {
	                
	                Com_Log::registApiErrorLog("game", "over", "0件", mb_convert_encoding($keyword, "shift-jis"), Com_Const::GOOGLE);
	                
	                return Com_Const::FORBIDDEN;
	            } else {
	                // success
	                $response = $otherServerRes[0];
	                $api = $otherServerRes[1];
	            }
            }
            
            if ($api !== Com_Const::API_GOOGLE) { 
                $response = json_decode($response);
            }

            $ri = 0;
            foreach ($keyRes as $gNm => $kword) {
                
                if ($api === Com_Const::API_GOOGLE) {
                    if ( $response == null) {
                    
                        Com_Log::registApiErrorLog("some error", "over", $api, $keyword, Com_Const::GOOGLE);
                        // some error
                        // return Com_Const::ERROR;
                    } else {
                        $rstWord = $this->parseXMLResponseCsv($response, $gNm, $kword);
                         
                        if (!empty($rstWord)) {
                            $expandKeywords .= $rstWord;
                        }
                    }
                } else {
                    if ( is_array($response) == false || $response == null) {
                    
                        Com_Log::registApiErrorLog("some error", "over", $api, $keyword, Com_Const::GOOGLE);
                        // some error
                        // return Com_Const::ERROR;
                    } else {
                        try {
                            $rstWord = $this->parseXMLResponseCsv($response[$ri++], $gNm, $kword);
                             
                            if (!empty($rstWord)) {
                                $expandKeywords .= $rstWord;
                            }
                        } catch (Exception $e) { }
                    }
                }
                
            }
	        
            //Com_Log::registApiErrorLog("time", "time", time(true), $startTime, Com_Const::GOOGLE);
            
	        if (time(true) - $startTime > Com_Const::EXECUTE_TIME_G 
	            || $this->callApiTimes > 500) {
	                
	            return array(Com_Const::INTERRUPTION, $expandKeywords, $groupNm, $this->callApiTimes);
	        }
	        
	        $keyRes = array();
	    }
	    return $expandKeywords;
	}
	
	/**
	 * CSVファイルを作成する
	 * @param unknown $expand
	 */
	public function makeCsv($expand) {
	    $csvdata = "";
	    
	    if($expand["bflag"] == 1) {
	        $expand["result"] = gzuncompress($expand["resultb"]);
	        $expand["level1"] = gzuncompress($expand["level1b"]);
	        $expand["level2"] = gzuncompress($expand["level2b"]);
	        $expand["level3"] = gzuncompress($expand["level3b"]);
	    }
	    
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
	    
	    if (empty($xmlContents)) {
	        return "";
	    }
	    
	    $objContents = simplexml_load_string(mb_convert_encoding($xmlContents, "utf-8","SJIS-win"));
	    $cnt = count($objContents->CompleteSuggestion);
	    
	    if ($cnt == 0) {
	        //Com_Log::registApiErrorLog($xmlContents, "parseXMLResponseCsv", "0件", $keyword, Com_Const::GOOGLE);
	        return "";
	    }
	    
	    $expandGroup = "<".$groupNm.">";
	    $expandKeywords = "";
	    
	    for($i = 0; $i < $cnt; $i++){
	        $suggestKeyword = $objContents->CompleteSuggestion[$i]->suggestion;
	        foreach($suggestKeyword->attributes() as $key => $val) {
	            
	            if($val != null && trim($val) != "" && $keyword != $val){
	                $expandKeywords .= Com_Util::filter($val)."&";
	            }
	        }
	    }
	    
	    return empty($expandKeywords) ? "" : rtrim($expandGroup.$expandKeywords,"&");
	}
	
	// -------------------------------------------------
	
	public function setCallApiTimes($time) {
	    $this->callApiTimes = $time;
	}
	public function getCallApiTimes() {
	    return $this->callApiTimes;
	}
	
	// ============================= maintenance ++++++++++++++++++
	public function compressCsv($idfrom, $idto) {
	    
	    $site = 1;
	    
	    $dao = new Db_ExpandResult();
	    $rows = $dao->getRowsByIdRange($idfrom, $idto, $site);
	    if ($rows == null) return;
	    
	    foreach ($rows as $row) {
	        
	        $data = array();
	        $where = array();

	        $data["result"] = null;
	        $data["level1"] = null;
	        $data["level2"] = null;
	        $data["level3"] = null;
	         
	        $data["resultb"] = gzcompress($row["result"], 9);
	        $data["level1b"] = gzcompress($row["level1"], 9);
	        $data["level2b"] = gzcompress($row["level2"], 9);
	        $data["level3b"] = gzcompress($row["level3"], 9);
	        $data["bflag"] = 1;
	         
	        $where["historyid = ?"] = $row["historyid"];
	        $where["site = ?"] = $site;
	        $where["bflag = ?"] = 0;
	        
	        $dao->updateExpand($data, $where);
 	    }
	    
	}
	
	
}
