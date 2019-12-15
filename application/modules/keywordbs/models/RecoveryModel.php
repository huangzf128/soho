<?php
class Keywordbs_Model_RecoveryModel extends Db_Abstract{
    
    private $keyword;
    private $serverEncode;
    
    /**
     * init
     * @param unknown $keyword
     */
    function __construct($keyword) {
        
    	$this->keyword = trim(mb_convert_encoding($keyword, "utf-8", "UTF-8, eucjp-win, sjis-win"));
    	$this->serverEncode = "EUC-JP";
    	 
    	set_time_limit(300);
    }
    
	/*---------------------------------------------------
	* Delete
	---------------------------------------------------*/
	
	public function searchDeleteList()
	{
		try {
			$searchHistoryEntity = new Keywordbs_Model_Entities_SearchHistory();
			$result = $searchHistoryEntity->getDeleteList($this->keyword);
			return $result;
	
		} catch (Zend_Db_Adapter_Exception $e) {
			throw $e;
		} catch (Zend_Exception $e) {
			throw $e;
		}
	}
	
	public function deleteKeyword(){
        $searchHistoryEntity = new Keywordbs_Model_Entities_SearchHistory();
        $delLists = $searchHistoryEntity->getDeleteList($this->keyword);
        if (count($delLists) == 0) return true;
        
        // delete file
        foreach ($delLists as $row) {
            $keyword = htmlentities($row['kword'], ENT_QUOTES, "UTF-8");
            $time = str_replace(array("/", " ", ":", "-"), "", $row['registdt']);
            $fileName = mb_convert_encoding ( $time.'_'.$keyword, $this->serverEncode, "auto");
            @unlink("history/archive_bs/".$fileName.".html");
        };
        
        // delete history list
        $result = $searchHistoryEntity->getDelete($this->keyword);
        
        return $result;
	}

	public function recovery($start, $end){

		$result = true;
		$dir = "history/archive_bs";
		$searchHistoryEntity = new Keywordbs_Model_Entities_SearchHistory();
		
		try{
			$cdir = scandir($dir, 1);
			foreach ($cdir as $key => $value)
			{
				if (!in_array($value, array(".", "..")))
				{
					if (!is_dir($dir . DIRECTORY_SEPARATOR . $value)){
			
						if($this->isRecoveryFile($value, $start, $end)){
							
							$obj_file = $dir. DIRECTORY_SEPARATOR. $value;
							// ファイル読み込み
							$fileContent = file_get_contents($obj_file);
							// ヒット件数取得
							$count = $this->getCount($fileContent, $obj_file);
								
							$splitFileName = explode("_", preg_replace("/\.html$/", "", $value), 2);
								
							if($count !== null && $splitFileName !== false && count($splitFileName) == 2
								&& strlen($splitFileName[0]) === 14){
								try{
									$srchRst  = array();
									$srchRst['registdt'] = $splitFileName[0];
									$srchRst['kword'] = mb_convert_encoding($splitFileName[1], "utf-8", "UTF-8,eucjp-win,sjis-win");
									$srchRst['rstcnt'] = $count;
									$srchRst['clientip'] = "";
									
									$searchHistoryEntity->regist($srchRst);
									
								}catch(Exception $e){
									$this->errOutput("deleteOK", "fileName=".$value.";Count=".$count.";Error=".$e->getMessage()."\n");
								}
							} else {
								$this->errOutput("deleteOK", "fileName=".$value.";Count=".$count."\n");
							}
						}
					}
				}
			}
		}catch(Exception $e){
			$this->errOutput("deleteOK", "ErrMessage=".$e->getMessage()."\n");
			$result = false;
		}
		
		return $result;
	}
	
	private function isRecoveryFile($fileName, $start, $end){
	
		$splitFileName = explode("_", $fileName, 2);
	
		if(count($splitFileName) == 2){
			if($splitFileName[0] >= $start && $splitFileName[0] <= $end){
				return true;
			}
		}
		return false;
	}
	
	function getCount($fileCon, $fileName){
	
		$fileCon = substr_count($fileCon, "class='sugkey'");
		return $fileCon;
	}
	
}