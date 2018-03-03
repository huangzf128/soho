<?php
class Keyword_Model_Csv extends Db_Abstract{
    
    /**
     * init
     * @param unknown $keyword
     */
    function __construct() { }
    
    public function registCsvOrder($userid, $historyid) {
        $data = array();
        $data["userid"] = $userid;
        $data["historyid"] = $historyid;
        $data["updatedt"] = date('Y-m-d H:i:s');
        
        $dao = new Keyword_Model_Entities_CsvOrderUser();
        $dao->regist($data);
    }
    
    public function getCsvOrderList($userid) {
        $dao = new Keyword_Model_Entities_CsvOrderUser();
        $orders = $dao->getCsvOrderList($userid);
        return $orders;
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
	
	
	public function parseSkStrToAry($skStr) {
	    
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
	
	// 検索結果のhtmlタグを削除する
	public function removeSkTag($sk) {
	    require_once 'simple_html_dom.php';
	    
	    $skDom = str_get_html($sk);
	    $skStr = "";
	    
	    $divs = $skDom->find("div");
	    for ($i = 0; $i < count($divs); $i++) {
	        $groupNm = trim($divs[$i]->find("span", 0)->plaintext);
	        $skStr .= "<".$groupNm.">";
	        
	        $lis = $skDom->find("ul", $i)->find("li");
	        $cnt = count($lis);
	        for($j = 0; $j < $cnt; $j++) {
	            $skStr .= trim($lis[$j]->find("span", 0)->plaintext);
	            if ($j < $cnt - 1) {
	                $skStr .= "&";
	            }	            
	        }
	    }
	    return $skStr;
	}
	
	public function expandLevel($sk) {
	    // parse
	    $skAry = $this->parseSkStrToAry($sk);
	    
	    // expand
	    $client = new Zend_Http_Client();
	    $client->setConfig(array(
	            'adapter'   => 'Zend_Http_Client_Adapter_Curl',
	            'keepalive' => true,
	            'curloptions' => array(CURLOPT_FOLLOWLOCATION => false),
	    ));
	    
	    $groupNmBk = "";
	    $i = 0;
	    foreach ($skAry as $groupNm => $keyword) {
	        
	        $client->setUri("http://www.google.co.jp/complete/search?hl=en&q=".urlencode($keyword)."&output=toolbar");
	        
	        $response = $this->sendAPIRequest($client);
	        if ($response !== null) {
	            $expandKeywords .= $this->parseXMLResponseCsv($response, $groupNm, $keyword);
	        }
	    }
	    
	    return $expandKeywords;
	}
	
	public function makeCsv($expand) {
	    $csvdata = "";
	    
	    $skAry = $this->parseSkStrToAry($expand["result"]);
	    $level1Ary = $this->parseSkStrToAry($expand["level1"]);
	    
	    $file = fopen('php://output', 'w');
	    
	    
	    foreach ($skAry as $gSk => $sk) {
	        
	        fputcsv($file, array(substr($gSk, 0, -1), $sk));
	        
	        for ($i = 0; $i < 10; $i++) {
	            $val = $level1Ary[$gSk.$i];
	            if (!empty($val)) {
	                fputcsv($file, array(substr($gSk, 0, -1), $sk, $val));
	            } else {
	                break;
	            }
	        }
	        //$csvdata .= $gSk.",".$sk;
	        
	        //for (; $j < count($level1Ary); $j++) {
	            
	        //}
	    }
	    
	}
	
	// --------------------------------------------------------------
	// Private Function
	// --------------------------------------------------------------
	
	
	// <groupNm>keyword&keyword&keyword
	private function parseXMLResponseCsv($xmlContents, $groupNm, $keyword) {
	    
	    $objContents = simplexml_load_string(mb_convert_encoding($xmlContents, "utf-8","SJIS-win"));
	    $cnt = count($objContents->CompleteSuggestion);
	    
	    $expandKeywords = "<".$groupNm.">";
	    
	    for($i = 0; $i < $cnt; $i++){
	        $suggestKeyword = $objContents->CompleteSuggestion[$i]->suggestion;
	        foreach($suggestKeyword->attributes() as $key => $val) {
	            
	            if($val != "" && $keyword != $val){
	                $expandKeywords .= (string)$val."&";
	            }
	        }
	    }
	    return rtrim($expandKeywords,"&");
	}
	
	/**
	 * APIを呼んで、サジェストキーワードを取得する
	 * @param unknown $uri
	 * @return NULL
	 */
	private function sendAPIRequest($client)
	{
	    try{
	        $response = $client->request();
	        
	        if ($response->isSuccessful()) {
	            return $response->getBody();
	        } else {
	            //$this->errOutput("err.txt", date("Y-m-d H:i:s").": ".$response->getStatus() . ": " . $response->getMessage().": ".$client->getUri(true)."\n");
	            return null;
	        }
	        
	    } catch (Zend_Http_Client_Exception $e) {
	        //echo '<p>エラーが発生しました (' .$e->getMessage(). ')</p>';
	        return null;
	    }
	}
	
}