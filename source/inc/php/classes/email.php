<?php
class email{
	public $recipient;
	public $sender;
	public $senderName;
	public $replyTo;
	public $boundary;
	public $headers;
	public $message;
	public $subject;
	
	function __construct($to,$from,$fromName='',$replyTo='noreply@pixyt.com'){
		$this->recipient = $to;
		$this->sender = $from;
		$this->replyTo = $replyTo;
		if(empty($fromName)){$c=explode('@',$from);$fromName = $c[0];}
		$this->senderName = $fromName;
		$this->boundary = '--mimepart_' . md5(randStr().time()).PHP_EOL;
		
		if($fromName == 'guest'){$fromName = 'A pixyt guest';}
		$this->headers = 'From: '.$fromName.' <'.$from.'>'.PHP_EOL;
		$this->headers .= 'Disposition-Notification-To: receipt@pixyt.com'.PHP_EOL;
		$this->headers .= 'Return-Path: <'.$replyTo.'>'.PHP_EOL;
		$this->headers .= 'MIME-Version: 1.0'.PHP_EOL;
		$this->headers .= 'Content-Type: multipart/alternative; boundary="'.$this->boundary.'"';
	}
	
	public function subject($subject){
		$this->subject = $subject;
	}
	
	public function message($msg){
		$msg = wordwrap($msg,70);
		$this->message = $msg;
	    /*
		$this->message .= '--'.$boundary."\n";
		$this->message .= 'Content-Type: text/plain; charset="iso-8859-1"'."\n";
		$this->message .= 'Content-Transfer-Encoding: 8bit'."\n\n";
		$this->message .= $msg."\n\n";
	    	$this->HTMLencode($msg);
		*/
	}
	
	private function HTMLencode($content){
		$this->message .= PHP_EOL.'--'.$this->boundary;
		$this->message .= PHP_EOL.'Content-Type: text/html; charset="iso-8859-1"';
		$this->message .= PHP_EOL.'Content-Transfer-Encoding: 8bit';
		$this->message .= PHP_EOL.T::HTMLMail($content).PHP_EOL.PHP_EOL;
	}
	
	public function attachment($file){
		$this->message .= '--'.$this->boundary.'--'."\n";
		switch (strtoupper(end(explode('.',$file)))){
			case 'JPG':
				$this->message .= 'Content-Type: image/jpeg; name="'.$file.'"'.PHP_EOL;
			break;
			
			case 'GIF':
				$this->message .= 'Content-Type: image/gif; name="'.$file.'"'.PHP_EOL;
			break;
			
			case 'PNG':
				$this->message .= 'Content-Type: image/jpeg; name="'.$file.'"'.PHP_EOL;
			break;
		}

		$this->message .= 'Content-Transfer-Encoding: base64'."\n";
		$this->message .= 'Content-Disposition:attachement; filename="'.$file.'"'.PHP_EOL.PHP_EOL;
		$this->message .= chunk_split(base64_encode(file_get_contents($file))).PHP_EOL;
	}
	
	function send(){
		if(mail($this->recipient,$this->subject,$this->message,$this->headers)){
			return true;
		}
		return false;
	}
}
?>