<?php
abstract class Pixyt{
	public static $errors = array();
	public static $calls = array();
	
	public $className = NULL;
	
	public function __construct(){
		$this->className = get_class($this);
		if(ERROR_LEVEL == 0)echo 'New '.$this->className.PHP_EOL;
		if(!isset(self::$calls[$this->className])){self::$calls[$this->className] = 0;}
		if(count(self::$calls[$this->className]) > 5000){die('Way too many calls to '.$this->className);}
		self::$calls[$this->className]++;
	}
	
	private static function trackError($content,$importance=20,$report=true){
		if(!$report){return;}
		if(in_array($content,self::$errors)){
			return;
		}
		self::$errors[] = $content;
		$error = 'url: '.HOME.$_REQUEST['url'].'<br/>';
		$error .= 'request: '.str_replace(PHP_EOL,' ',print_r($_REQUEST,true)).'<br/>';
		if(isset($_SESSION['uid']))$error .= 'user: '.$_SESSION['uid'].'<br/>';
		$error .= 'ip: '.USER_IP.'<br/>';
		$error .= 'time: '.date('Y-m-d H:i:s').'<br/>';
		$error .= 'importance: '.$importance.'%<br/>';
		$error .= '<br/>';
		$error .= dv('error').'"'.$content.'"'.xdv();
		
		if(HOST == 'localhost' || (isset(App::$user->id) && App::$user->id == 1) || ERROR_LEVEL <= 1){
			die($error);
		}
		
		$message = array(array('<h1>'.lnk('<img src="http://pixyt.com/logo.jpg" height="40" width="40" style="position:relative;top:10px;" alt="pixyt" title="pixyt"/>','',array('from'=>'email')).' Error on Pixyt</h1>'),array('<div class="message">'.$error.'</div>'),array('<br/><p style="color:#aaa;border-top:1px dotted #aaa;">email settings '.xtlnk('http://pixyt.com/account/settings','pixyt.com/account/settings').'</p>'));
		sendMail('crew@pixyt.com', 'Pixyt bot', 'no-reply@pixyt.com', 'Error', $message);
	}
	
	public function error($content='',$importance=20,$report=true){//importance from 0 to 100
		if(ERROR_LEVEL == 0){die($content);}
		if(empty($content)){return false;}
		self::trackError($this->className.': '.$content,$importance,$report);
	}
	
	public static function addError($content,$importance=10,$report=true){
		self::trackError($content,$importance,$report);
	}
	
	public static function getErrors(){
		return array_merge(self::$globalErrors,self::$classErrors);
	}
}
?>