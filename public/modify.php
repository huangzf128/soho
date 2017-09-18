<?php
// 対象ファイルの範囲を定義する
define('FILE_START', '20140715000000');
define('FILE_END', '20140715999999');


$dir = "history/archive";
//$fp = fopen('php://output', 'w');
$fp = "";
$data = insertFile($dir, $fp);


// fclose($fp);



function insertFile($dir, $fp) {
	
	$cdir = scandir($dir, 1);
	foreach ($cdir as $key => $value)
	{
		if (!in_array($value, array(".", "..")))
		{
			if (!is_dir($dir . DIRECTORY_SEPARATOR . $value)){
				
				//fputcsv($fp, array(mb_convert_encoding ( $value, "shift-jis", "EUC-JP")));
				
				if(isModifyFile($value, FILE_START, FILE_END)){
					
					$obj_file = $dir. DIRECTORY_SEPARATOR . $value;
					$fileContent = file_get_contents($obj_file);
					$fileContent = modify($fileContent, $obj_file);
					
					if($fileContent != null){
						file_put_contents($obj_file, $fileContent);
					}
										
				}
			}
		}
	}
}


// 対象ファイルかどうかをチェックする
function isModifyFile($fileName, $start, $end){

	$splitFileName = explode("_", $fileName, 2);
	
	if(count($splitFileName) == 2){
		if($splitFileName[0] > $start && $splitFileName[0] < $end){
			return true;
		}	
	}	
	return false;	
}


function modify($fileCon, $fileName){
	
	$newFileCon = null;
	
	// insert
	$iniFile = "banner.ini";
	$banner = file_get_contents($iniFile);
	$banner = mb_convert_encoding($banner, 'UTF-8', "EUC-JP");
	
	$insertPos = stripos($fileCon, '<div id="list">');
	
	$fileCon = substr_replace($fileCon, $banner, $insertPos, 0);
	
	// delete
	// <footer>で分割
	$splitFile = explode("</footer>", $fileCon);

	if(count($splitFile) == 2){
		$splitFile[1] = preg_replace('/<p align=\"center\">.+?<\/p>/s', '', $splitFile[1]);

		$newFileCon = $splitFile[0]."</footer>".$splitFile[1]; 
	}else{
		errOutput("deleteErr", "reason:<footer>ない;fileName=".$fileName.";count=".count($splitFile))."\n";
	}
	
	return $newFileCon;
}


function errOutput($errSql_file, $msg){

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

?>