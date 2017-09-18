<?php


$dir = "..";

$obj_file = $dir. DIRECTORY_SEPARATOR. "title.php";
$fileContent = file_get_contents($obj_file);

echo '"'.$fileContent.'"';


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