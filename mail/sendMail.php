<?php

if(!isset($_SESSION)){
	session_start();
}

mb_language('Japanese');
mb_internal_encoding("UTF-8");

$mailto = "qiujian@awa.bbiq.jp";

require_once 'mailManage.php';
$mailObj = new mailManage();

$companynm = $_GET['companynm'];
$telephone = $_GET['telephone'];
$address = $_GET['address'];
$name = $_GET['name'];
$mobile = $_GET['mobile'];
$email = $_GET['email'];
$content = $_GET['content'];

$body = "会社名 : ".$companynm."<br/>".
		"電話 : ".$telephone."<br/>".
		"住所 : ".$address."<br/>".
		"名前 : ".$name."<br/>".
		"携帯 : ".$mobile."<br/>".
		"Email : ".$email."<br/>".
		"問合せ内容:<br/>".$content."<br/>";

try{		
	$headers = $mailObj->getHeader("BeautyVilageシステム", "auto@jcnet-sme.com");
	$subject = $mailObj->getMailSubject($type);	
	
	//send mail
	$send = $mailObj->sendEmail($mailto, $subject, $body, $headers);

	if ($send <> null) {
		$msg = "送信しました。";
	}else{
		$msg = "メールの送信に失敗しました。もう一回やり直してください。";
	}

	$result = $msg;

}catch(Exception $e){
	$result = $e->getMessage();
}

$_SESSION['result'] = $result;
Header("Location: "."http://".$_SERVER['HTTP_HOST']."/beauty/query.html");


