<?php


function getFileList($dir, $fp) {
	
	$cdir = scandir($dir, 1);
	foreach ($cdir as $key => $value)
	{
		if (!in_array($value, array(".", "..")))
		{
			if (!is_dir($dir . DIRECTORY_SEPARATOR . $value)){
				fputcsv($fp, array(mb_convert_encoding ( $value, "shift-jis", "EUC-JP")));
			}
		}
	}
}


header( 'Content-Type: text/csv' );
header("Content-Disposition: attachment; filename=keywordResult_ya.csv");

$dir = "history/archive_ya";
$fp = fopen('php://output', 'w');
$data = getFileList($dir, $fp);

fclose($fp);

?>