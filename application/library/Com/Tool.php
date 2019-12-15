<?php
class Com_Tool extends Db_Abstract{
    
    private $site;
    private $dir;
    private $dirbk;
    private $bkFolderName;
    private $ext = ".html";
    
    private $serverEncode = "EUC-JP";
    
    function __construct($site) {
        $this->site = $site;
        
        if ($site == Com_Const::AMAZON) {
            $this->dir = "history".DIRECTORY_SEPARATOR."archive_az";
            
        } else if ($site == Com_Const::BING) {
            $this->dir = "history".DIRECTORY_SEPARATOR."archive_bs";
            
        } else if ($site == Com_Const::YAHOO) {
            $this->dir = "history".DIRECTORY_SEPARATOR."archive_ya";
            
        } else if ($site == Com_Const::YOUTUBE) {
            $this->dir = "history".DIRECTORY_SEPARATOR."archive_yt";
            
        }
        
        $this->dirbk = "history";
        set_time_limit(0);
    }
    
	public function searchKeywordList($fromDt, $toDt)
	{
		try {
			$searchHistoryEntity = new Db_SearchHistory($this->site);
			$result = $searchHistoryEntity->getRowByRangeDt($fromDt, $toDt);
			return $result;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function saveHtml($files) {
	    
	    $zip = new ZipArchive();
	    $zipname = $this->dirbk.DIRECTORY_SEPARATOR.$this->bkFolderName.".zip";
	    $zip->open($zipname, ZipArchive::CREATE);
	    
	    $startTime = time(true);
	    
	    foreach ($files as $file) {
	    
	        $registdt = date('YmdHis', strtotime($file["registdt"]));
	        $fileName = $registdt."_".mb_convert_encoding($file["kword"], $this->serverEncode, "auto");
	        $filePath = $this->createFilePath($fileName);
	        
	        $fileContent = @file_get_contents($filePath);
	        
	        if ($fileContent !== FALSE) {
	            // ファイル読み込み
	            
	            $this->saveToDb($file["id"], $fileContent);
	            
	            $newFileNm = $this->createBkFilePath($fileName);
	            $this->moveFile($filePath, $newFileNm);
	            
// 	            $zip->addFile($newFileNm,
// 	                    mb_convert_encoding($fileName, "sjis-win", $this->serverEncode).$this->ext);

	            $zip->addFile($newFileNm,
	                    mb_convert_encoding($fileName, "gbk", $this->serverEncode).$this->ext);
	             
	        } else {
	            $this->hideFile($file["id"]);
	            //Com_Util::write($this->bkFolderName, $this->dir.": id = ".$file["id"]."\n");
	        }
	        
	        if (time(true) - $startTime > (Com_Const::EXECUTE_TIME - 10)) {
	            $zip->close();
	            return Com_Const::INTERRUPTION; 
	        }
	    }
	    
	    $zip->close();
	    
	    if (time(true) - $startTime > (Com_Const::EXECUTE_TIME - 10)) {
	        $zip->close();
	        return Com_Const::INTERRUPTION;
	    }	    
	    $this->removeFile('history'.DIRECTORY_SEPARATOR.$this->bkFolderName.DIRECTORY_SEPARATOR.'*');
	    
	    return null;
	}
	
	public function createBkFolder($bkFolderName) {
	    $this->bkFolderName = $bkFolderName;
	    $bkFolderPath = $this->dirbk.DIRECTORY_SEPARATOR.$bkFolderName;
	    
	    if (!file_exists($bkFolderPath)) {
	        mkdir($bkFolderPath, 0777);
	    }
	}
	
	public function zipFile($zipName) {
	    
	    $zip = new ZipArchive();
	    $filename = $this->dirbk.DIRECTORY_SEPARATOR.$zipName.".zip";
	    $zip->open($filename, ZipArchive::CREATE);
	    
	    $cdir = scandir($this->dirbk.DIRECTORY_SEPARATOR.$zipName, 1);
	    foreach ($cdir as $key => $value)
	    {
	       $obj_file = $this->dirbk.DIRECTORY_SEPARATOR.$zipName.DIRECTORY_SEPARATOR.$value;
	       $zip->addFile($obj_file, mb_convert_encoding($value, "gbk", $this->serverEncode));
	    }
	    $zip->close();
	}
	
	public function removeFile($path) {
	    try {
	        $files = glob($path); // get all file names
	        foreach($files as $file){ // iterate files
	            if(is_file($file))
	                unlink($file); // delete file
	        }
	    } catch(Exception $e) {
	        echo $e;
	    }
	}
	/*---------------------------------------------------
	 * private
	 ---------------------------------------------------*/
	
	private function moveFile($from, $to ) {
	    rename($from, $to);
	}
	
	private function saveToDb($id, $fileContent) {
	    $db = new Db_SearchHistory($this->site);
	    
	    $data = array();
	    $data["sk"] = $fileContent;
	    $data["showtype"] = Com_Const::SHOWTYPE_DIRECT;
	    
	    $where = array();
	    $where["id = ? "] = $id;
	    
	    $db->updateHistory($data, $where);
	}
	
	private function hideFile($id) {
	    $db = new Db_SearchHistory($this->site);
	     
	    $data = array();
	    $data["showtype"] = Com_Const::SHOWTYPE_HIDE;
	     
	    $where = array();
	    $where["id = ? "] = $id;
	     
	    $db->updateHistory($data, $where);
	}
	private function createFilePath($fileName) {
	    return $this->dir. DIRECTORY_SEPARATOR. $fileName.$this->ext;
	}
	
	private function createBkFilePath($fileName) {
	    
// 	    return "history".DIRECTORY_SEPARATOR. $fileName.$this->ext;
	    return $this->dirbk. DIRECTORY_SEPARATOR
	               .$this->bkFolderName.DIRECTORY_SEPARATOR. $fileName.$this->ext;
	}
	
}