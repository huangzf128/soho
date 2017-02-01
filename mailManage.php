<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$paramAry = $_GET;
}elseif ($_SERVER['REQUEST_METHOD'] == 'POST'){
	$paramAry = $_POST;
}

$mailObj = new mailManage();

$email = $paramAry["mail"];
$name = $paramAry["name"];
$message = $paramAry["message"];

$header = $mailObj->getHeader();
$subject = $mailObj->getMailSubject();
$body = $mailObj->getBody($name, $email, $message);

$mailObj->sendEmail($mailto, $subject, $body, $header);

$msg = "送信しました。";
Header("Location: message.html?message=".$msg);

class mailManage{

	public function __construct(){
		mb_language('Japanese');
		mb_internal_encoding("UTF-8");		
	}
		
	public function getHeader($usernmfrom = "system", $sendfrom = "system"){
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
		$headers .= 'From: '.$usernmfrom.'<'.$sendfrom.'>' . "\r\n";
		return $headers;
	}
	
	public function getBody($name, $email, $message){
		$space = ";\n";
		$body = '名前 : '.$name.$space.
					 'E-mail : '.$email.$space.
					 'メッセージ : '.$message;						
		return $body;
	}
	
	/*
	 * title
	 */
	public function getMailSubject(){
		$subject = '問合せがありました';
		return mb_encode_mimeheader(mb_convert_encoding($subject, "UTF-8", "AUTO"));
	}
	
	//********************プライベートメソッド ***********************
	
	
	public function sendEmail($mailto, $subject, $body, $headers) {
 		$body = nl2br($body);
//  		return mail("huangzf128@gmail.com", $subject, $body, $headers);
		$mailto = "okina@sepia.plala.or.jp";
 		return mail($mailto, $subject, $body, $headers);				
	}
	
	private function _formatType($value){
	
		if(is_array($value)) {
			$value = trim(implode(",", $value));
		}else{
			$value = trim(mysql_real_escape_string($value));
		}
		$value = ($value == null || $value == "") ? "''" : "'".htmlspecialchars($value)."'";
		return $value;
	}
	
}