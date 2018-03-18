<?php
ignore_user_abort(true);
set_time_limit(0);

output("start:".date('Y-m-d H:i:s'));
sleep(20);
output("end:".date('Y-m-d H:i:s'));


function output($msg) {
    $errSql_file = "thread.txt";
    
    $fp = fopen("".$errSql_file, 'a+');
    if ($fp){
        if (flock($fp, LOCK_EX)){
            if (fwrite($fp,  $msg."\r\n") === FALSE){
                new Exception('ファイル書き込みに失敗しました');
            }
            flock($fp, LOCK_UN);
        }
    }
    fclose($fp);
}
