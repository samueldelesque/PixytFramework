<?php
class Msg extends Pixyt{
	public static $notifications = array();
	public static $actions = array();
	public static $redundance = array();
	public static $time = array();
	public static $timeSum=0;
	
	const NOTIFICATION = 1;
	const WARNING = 2;
	const CONFIRM = 3;
	const CRITICAL = 4;
	const DEBUG = 0;
	
	public static function notify($msg,$duration=5){
		self::addMsg($msg,$duration,self::NOTIFICATION);
	}
	
	public static function warn($msg){
		self::addMsg($msg,-1,self::WARNING);
	}

	public static function addMsg($content, $duration = 5,$type = self::NOTIFICATION){
		$content = addslashes($content);
		if($duration != 0){
			self::$timeSum += $duration;
			$duration = self::$timeSum;
		}
		if(preg_match('/^[0-9]$/',$content)){$type = self::CRITICAL;}
		switch ($type){
			case self::CRITICAL:
				if(ERROR_LEVEL < 2){
					die($content);
				}
				else{
					die('Sorry an error occured, please try again later.');
				}
			break;
			
			case self::WARNING:
				if(Mysql::$status === true && LP::$db != false) {
					Process::jobLog($type,$content);
				}
				$id = md5($content);
				if(!isset(self::$redundance[$id])){
					T::$staticError[] = $content;
					self::$time[$id] = $duration;
				}
				else{
					self::$redundance[$id]++;
				}
			break;
			
			case self::DEBUG:
				if(DEBUG === true){
					$id = md5($content);
					if(!isset(self::$notifications[$id])){
						self::$notifications[$id] = 'DEBUG_ERROR:'.$content;
						self::$redundance[$id] = 1;
						self::$time[$id] = $duration;
					}
					else{
						self::$redundance[$id]++;
					}
				}
			break;
			
			case self::NOTIFICATION:
			default:
				$id = md5($content);
				if(!isset(self::$notifications[$id])){
					self::$notifications[$id] = $content;
					self::$redundance[$id] = 1;
					self::$time[$id] = $duration;
				}
				else{
					self::$redundance[$id]++;
				}
			break;
		}
	}
	
	public static function getMessages(){
		$m = array();
		if(count(self::$notifications) > 0){
			foreach (self::$notifications as $val){
				$m[] = $val;
			}
		}
		return $m;
	}
	
	public static function showMessages(){
		if(IS_AJAX){return true;}
		if(count(self::$notifications) > 0){
			foreach (self::$notifications as $id=>$msg){
				if(self::$redundance[$id] > 1){$msg .= '('.self::$redundance[$id].' occurences)';}
				T::js('notify("'.str_replace('"','\'',$msg).'");');
			}
		}
	}
}

?>