
<?php

$url_list = array();

$url_list[] = "http://gskw:8888/batch/slow.php";
$url_list[] = "http://gskw:8888/batch/slow.php";

$res = sendMulitRequest($url_list, 2);
//var_dump($res);


function sendMulitRequest($url_list, $timeout = 10){
    
	$mh = curl_multi_init();
 	foreach ($url_list as $i => $url) {
        $conn[$i] = curl_init($url);
        curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER,1);
        curl_setopt($conn[$i], CURLOPT_FAILONERROR,1);
        curl_setopt($conn[$i], CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($conn[$i], CURLOPT_MAXREDIRS,3);
        curl_setopt($conn[$i], CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; CIBA)");
        //SSL証明書を無視
        curl_setopt($conn[$i], CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($conn[$i], CURLOPT_SSL_VERIFYHOST,false); 	        
        //タイムアウト
        if ($timeout){
            curl_setopt($conn[$i], CURLOPT_TIMEOUT, $timeout);
        }
        
        curl_setopt($conn[$i], CURLOPT_NOSIGNAL, 1);
        
	    curl_multi_add_handle($mh, $conn[$i]);
    }    
    //すべて取得するまでループ
    
    curl_multi_exec($mh, $active);
    
    $active = null;
    do {	    	
        $mrc = curl_multi_exec($mh, $active);
        usleep (250000);
    } while ($active > 0);
    
    
//     $active = null;
//     do {	    	
//         $mrc = curl_multi_exec($mh, $active);
//     } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    
//     $innerSleepInt = $outerSleepInt = 1;
    
    
//     while ($active and $mrc == CURLM_OK) {
	
//         if (curl_multi_select($mh) != -1) {	        	
//             do {
//                 $mrc = curl_multi_exec($mh, $active);
//             } while ($mrc == CURLM_CALL_MULTI_PERFORM);
//         }
//     }	    	    
//     if ($mrc != CURLM_OK) {
//     	echo "ng";
//         return false;
//     }
    
//     //ソースコードを取得
//     $res = array();
//     foreach ($url_list as $i => $url) {
//         if (($err = curl_error($conn[$i])) == '') {
//             $res[$i] = curl_multi_getcontent($conn[$i]);
//         } else {
//             echo '取得に失敗しました:'.$url_list[$i].'<br />';
//             //return false;
//         }
//         curl_multi_remove_handle($mh, $conn[$i]);
//         curl_close($conn[$i]);
//     }
//     curl_multi_close($mh);
		
//     return $res;
}
?>


<html>
<head>
<meta http-equiv=Content-Type content="text/html;charset=utf-8">
<script>

alert("ok");
</script>
</head>
</html>