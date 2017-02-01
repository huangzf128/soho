<?php

class mailManage{

		
	public function getHeader($usernmfrom = "", $sendfrom = ""){
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
		$headers .= 'From: '.$usernmfrom.'<'.$sendfrom.'>' . "\r\n";
		return $headers;
	}
	
	
	public function getMailSubject($type){
	
		$subject = 'BeautyVillageからの問合せ';
	
		return mb_encode_mimeheader(mb_convert_encoding($subject, "UTF-8", "AUTO"));
	}
	
	//********************プライベートメソッド ***********************
	
	
	public function sendEmail($mailto, $subject, $body, $headers) {
		//$mailto = "adcoming001@gmail.com";
		//$mailto = "customer@armdunk.jp";
		$body = nl2br($body);
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