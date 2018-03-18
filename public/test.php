<?php
echo openssl_decrypt("abcd", 'AES-128-ECB', "aaabbbcccdddii");

while ($msg = openssl_error_string())
    echo $msg . "<br />\n";
?>