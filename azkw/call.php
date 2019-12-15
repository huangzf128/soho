<?php 

set_time_limit(0);

$url_list = array("https://".$_SERVER['HTTP_HOST']."/test.php");
sendMulitRequest($url_list);

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
}

?>


<html>
<head>
<meta charset="utf-8" />
</head>
<body>
	
    		OK
</body>
</html>
	
