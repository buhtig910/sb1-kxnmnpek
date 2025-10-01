<?php

class Email {
	
	private $headers;
	private $messageType;
	private $sendTo;
	private $bcc;
	private $from;
	private $subject;
	private $body;
	
	
	/****************
	* setHeaders
	****************/
	public function setHeaders($headers) {
		$this->headers = $headers;
	}
	
	/*************
	* setTo
	**************/
	public function setTo($to) {
		$this->sendTo = $to;
	}
	
	/*************
	* setBcc
	*************/
	public function setBcc($bcc) {
		$this->bcc = $bcc;
	}
	
	/************
	* setFrom
	*************/
	public function setFrom($from) {
		$this->from = $from;	
	}
	
	/***********
	* setSubject
	************/
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	
	/************
	* setBody
	*************/
	public function setBody($body) {
		$this->body = $body;
	}
	
	/*************
	* setMessageType
	**************/
	public function setMessageType($type) {
		if($type == "HTML")
			$this->messageType = "Content-type: text/html; charset=iso-8859-1\r\n";
	}
	
	/**************
	* sendMessage
	***************/
	public function sendMessage() {
		
		$sendTo = $this->sendTo;
		$headers = "";
		
		$header.= $this->headers."\r\n";
		$headers.= "From: ".$this->from."\r\n";
		$headers.= "Reply-To: ".$this->from."\r\n";
		$headers.= "Return-Path: ".$this->from."\r\n";
		
		if(isset($this->bcc))
			$headers.= "BCC: ".$this->bcc."\r\n";
		if(isset($this->messageType))
			$headers.= $this->messageType;
		
		/*echo("Send to:".$sendTo."<br>");
		echo("Subject:".$this->subject."<br>");
		echo("Body:".$this->body."<br>");
		echo("Headers:".$headers."<br>");*/
		
		$sendMailResult = mail($sendTo, $this->subject, $this->body, $headers);
		if(!$sendMailResult)
			return false;
		
		return true;
			
	}
	
}

?>