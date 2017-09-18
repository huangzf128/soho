<?php
// 対象ファイルの範囲を定義する
define('FILE_START', '20140716000000');
define('FILE_END', '20140716999999');


$dir = "history/archive";
$data = deleteFile($dir);


echo "delete complete!";

function deleteFile($dir) {
	
	$cdir = scandir($dir, 1);
	foreach ($cdir as $key => $value)
	{
		if (!in_array($value, array(".", "..")))
		{
			if (!is_dir($dir . DIRECTORY_SEPARATOR . $value)){
				
				if(isDeleteFile($value, FILE_START, FILE_END)){
					
					$obj_file = $dir. DIRECTORY_SEPARATOR. $value;
					$fileContent = file_get_contents($obj_file);
					$fileContent = delete($fileContent, $obj_file);
					
					if($fileContent != null){
						file_put_contents($obj_file, $fileContent);
					} else {
						errOutput("deleteOK", "reason:NG ;fileName=".$value."\n");
					}
										
				}
			}
		}
	}
}



function delete($fileCon, $fileName){
	
	// delete
	$fileCon = preg_replace('/<p align=\"center\">.+?<\/p>/s', '', $fileCon);
	return $fileCon;
}


// 対象ファイルかどうかをチェックする
function isDeleteFile($fileName, $start, $end){

	$splitFileName = explode("_", $fileName, 2);

	if(count($splitFileName) == 2){
		if($splitFileName[0] > $start && $splitFileName[0] < $end){
			return true;
		}
	}
	return false;
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