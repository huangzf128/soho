<?php
class Keyword_Model_Csv extends Db_Abstract{
    
    function __construct() { }
    
    // 予約登録
    public function registCsvOrder($userid, $historyid) {
        $data = array();
        $data["userid"] = $userid;
        $data["historyid"] = $historyid;
        $data["updatedt"] = date('Y-m-d H:i:s');
        
        $dao = new Keyword_Model_Entities_CsvOrderUser();
        return $dao->regist($data);
    }
    
    // 予約したCSVを取得する
    public function getCsvOrderList($userid) {
        $dao = new Keyword_Model_Entities_CsvOrderUser();
        $orders = $dao->getCsvOrderList($userid);
        return $orders;
    }
    
    public function getNotExecuteCsvId($usreid) {
        $dao = new Keyword_Model_Entities_CsvOrderUser();
        $rows = $dao->getNotExecuteCsv($usreid);
        if (count($rows)) {
            return $rows[0]["historyid"];
        } else {
            return false;
        }
    }
    
    // 実行中の処理があるか
    public function hasExecutingCsv($userid) {
        $dao = new Keyword_Model_Entities_CsvOrderUser();
        $rows = $dao->getExecutingCsv($userid);
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
        $skStr = $this->removeSkTag($result["sk"]);
        
        $expandDao = new Keyword_Model_Entities_ExpandResult();
        $data = array();
        $data["historyid"] = $historyid;
        $data["result"] = $skStr;
        $data["updatedt"] = date('Y-m-d H:i:s');
        $data["status"] = 0;
        
        $rst = $expandDao->regist($data);
        
    	return $rst;
	}
	
	public function updateExpandLevel($historyid, $expandKeywords, $level)
	{
	    $dao = new Keyword_Model_Entities_ExpandResult();
	    
	    $data = array();
	    $data["level".$level] = $expandKeywords;
	    $data["updatedt"] = date('Y-m-d H:i:s');
	    
	    $where = array();
	    $where["historyid = ?"] = $historyid;
	    
	    $rst = $dao->updateExpand($data, $where);
	    
	    return $rst;
	}
	
	public function getExpandResult($historyid){
	    $dao = new Keyword_Model_Entities_ExpandResult();
	    $row = $dao->getRowById($historyid);
	    return $row;
	}
	
	public function updateExpandStatus($historyid, $status)
	{
	    $dao = new Keyword_Model_Entities_ExpandResult();
	     
	    $data = array();
	    $data["status"] = $status;
	    $data["updatedt"] = date('Y-m-d H:i:s');
	     
	    $where = array();
	    $where["historyid = ?"] = $historyid;
	     
	    $rst = $dao->updateExpand($data, $where);
	    return $rst;
	}
	
	/**
	 * 検索結果を展開する
	 * @param unknown $sk
	 */
	public function expandLevel($skStr) {
	    // parse
	    $skAry = $this->parseSkStrToAry($skStr);
	    
	    // expand
	    $client = new Zend_Http_Client();
	    $client->setConfig(array(
	            'adapter'   => 'Zend_Http_Client_Adapter_Curl',
	            'keepalive' => true,
	            'curloptions' => array(CURLOPT_FOLLOWLOCATION => false),
	    ));
	    
	    $expandKeywords = "";
	    $groupNmBk = "";
	    $i = 1;
	    foreach ($skAry as $groupNm => $keyword) {
	        
	        if ($keyword == null || trim($keyword) == "") {
                continue;
	        }
	        
	        $client->setUri("http://www.google.co.jp/complete/search?hl=en&q=".urlencode($keyword)."&output=toolbar");
	        
	        $response = Com_Util::sendAPIRequest($client);
	        if ($response == Com_Const::FORBIDDEN) {
	            
	            return Com_Const::FORBIDDEN;
	        } elseif ($response !== null) {
	            $rstWord = $this->parseXMLResponseCsv($response, $groupNm, $keyword);
	            
	            if (!empty($rstWord)) {
	                $expandKeywords .= $rstWord;
	            }
	        }
	        if ( ($i++ % 500) == 0) {
	            sleep(Com_Const::CSV_EXPAND_PER_WAITTIME);
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
	    
	    $skAry = $this->parseSkStrToAry($expand["result"]);
	    
	    $level1Ary = !empty($expand["level1"]) ? $this->parseSkStrToAry($expand["level1"]) : null;
	    $level2Ary = !empty($expand["level2"]) ? $this->parseSkStrToAry($expand["level2"]) : null;
	    $level3Ary = !empty($expand["level3"]) ? $this->parseSkStrToAry($expand["level3"]) : null;
	    
	    $file = fopen('php://output', 'w');
	    
	    foreach ($skAry as $gSk => $sk) {
	        
	        $rst = array(substr($gSk, 0, -1), $sk);
	        fputcsv($file, $rst);
	        
	        // level1
	        for ($i = 0; $i < 10; $i++) {
	            
	            if (!empty($level1Ary) && array_key_exists($gSk.$i, $level1Ary) && !empty($level1Ary[$gSk.$i])) {
	                
	                $level1Rst = $rst;
	                $level1Rst[] = $level1Ary[$gSk.$i];
	                fputcsv($file, $level1Rst);
	                
	                // leve2
	                for ($j = 0; $j < 10; $j++) {
	                     
	                    if (!empty($level2Ary) && array_key_exists($gSk.$i.$j, $level2Ary) && !empty($level2Ary[$gSk.$i.$j])) {
	                        
	                        $level2Rst = $level1Rst;
	                        $level2Rst[] = $level2Ary[$gSk.$i.$j];
	                        fputcsv($file, $level2Rst);
	                
	                        // leve3
	                        for ($k = 0; $k < 10; $k++) {
	                        
	                            if (!empty($level3Ary) && array_key_exists($gSk.$i.$j.$k, $level3Ary) && !empty($level3Ary[$gSk.$i.$j.$k])) {
	                                 
	                                $level3Rst = $level2Rst;
	                                $level3Rst[] = $level3Ary[$gSk.$i.$j.$k];
	                                fputcsv($file, $level3Rst);
	                                 
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
	
	private function parseSkStrToAry($skStr) {
	     
	    $skAry = array();
	    $groupAry = explode("<", ltrim($skStr, "<"));
	     
	    foreach($groupAry as $group) {
	        $g = explode(">", $group);
	        $k = explode("&", $g[1]);
	        for ($i = 0; $i < count($k); $i++) {
	            $skAry[$g[0].$i] = $k[$i];
	        }
	    }
	
	    return $skAry;
	}
	
	// <groupNm>keyword&keyword&keyword
	private function parseXMLResponseCsv($xmlContents, $groupNm, $keyword) {
	    
	    $objContents = simplexml_load_string(mb_convert_encoding($xmlContents, "utf-8","SJIS-win"));
	    $cnt = count($objContents->CompleteSuggestion);
	    
	    if ($cnt == 0) {
	        Keyword_Model_Log::registApiErrorLog($xmlContents, "parseXMLResponseCsv", "0件", $keyword);
	        return "";
	    }
	    
	    $expandKeywords = "<".$groupNm.">";
	    
	    for($i = 0; $i < $cnt; $i++){
	        $suggestKeyword = $objContents->CompleteSuggestion[$i]->suggestion;
	        foreach($suggestKeyword->attributes() as $key => $val) {
	            
	            if($val != null && trim($val) != "" && $keyword != $val){
	                $expandKeywords .= (string)$val."&";
	            }
	        }
	    }
	    return rtrim($expandKeywords,"&");
	}
	
	
}