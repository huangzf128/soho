<?php 

set_time_limit(0);

function errOutput($errSql_file, $sql){

    try{
        $fp = fopen("log/".$errSql_file, 'a+');

        if ($fp){
            if (flock($fp, LOCK_EX)){
                if (fwrite($fp,  $sql."\r\n") === FALSE){
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


$start_time = time(true);
errOutput("maxtime.txt", date("Y-m-d H:i:s", $start_time));

do {
    $end_time = time(true);
    sleep(10);
    errOutput("maxtime.txt", date("Y-m-d H:i:s", $end_time));
    
} while ($end_time - $start_time < 600);

?>


<html>
<head>
<meta charset="utf-8" />
</head>
<body>
	
    		
</body>
</html>
	
