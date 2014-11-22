<?php
class Process extends Pixyt{
	const MAXSCRIPTS = 1000;
	
	private static $jobs = 0;
	private static $sTime = 0;
	private static $now = array();
	private static $onload = array();
	private static $onunload = array();
	private static $specify = array();
	private static $action = array();
	public static $config = array();
	public static $stats = NULL;
	private static $dateFields = array('date','birthday');
	
	public static $warned = false;
	public static $Gstart;
	public static $Gend;
	public static $total = 0;
	public static $Gtotal;
	private static $startTimes = array();
	private static $endTimes = array();
	private static $variable = false;

	public static function startTime($script,$info = ''){
		if(count(self::$startTimes) > self::MAXSCRIPTS){exit('Maximum number of scripts reached ('.self::MAXSCRIPTS.') - last: '.$script.'('.$info.')');}
		self::$startTimes[][$script] = array(microtime(true),htmlspecialchars($info));
	}
	
	public static function endTime($script){
		self::$endTimes[][$script] = microtime(true);
	}
	
	public static function loadTime(){
		return round((microtime(true) - self::$Gstart)*1000);
	}
	
	public static function showLoadTimes(){
		self::$Gend = microtime(true);
		self::$Gtotal = round((self::$Gend- self::$Gstart)*1000,2);
		if(App::$user->access>20){return false;}
		$r = '<h3>'.translate('Speed stats').':</h3>';
		foreach(self::$startTimes as $s){
			foreach($s as $script=>$starTime){
				$e = self::$endTimes[$i][$script];
				$f = self::$startTimes[$i][$script][0];
				$info = self::$startTimes[$i][$script][1];
				if(!empty($e)){
					$totaltime = round(($e - $f)*1000,2);
					self::$total += $totaltime;
					$r .= $script.' <a href="javascript:LPalert(\''.$info.'\');">#'.$i.'</a> took '.$totaltime.'ms<br/>';
				}
			}
				$i++;
		}
		$r .= '<h5>DB total: '.self::$total.'ms (/'.self::$Gtotal.'ms)</h5>';
		return $r;
	}
	
	public static function processActions($actions = ''){
		App::$input = array();
		if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], App::$input);
        }
		$body = file_get_contents("php://input");
        $content_type = false;
        if(isset($_SERVER['CONTENT_TYPE'])) {
            $content_type = $_SERVER['CONTENT_TYPE'];
        }
		switch($content_type) {
			case "application/json":
				$body_params = json_decode($body);
				if($body_params) {
					foreach($body_params as $param_name => $param_value) {
					App::$input[$param_name] = $param_value;
					}
				}
			break;
			
			case "application/x-www-form-urlencoded":
				parse_str($body, $postvars);
				foreach($postvars as $field => $value) {
					App::$input[$field] = $value;
				}
			break;
			
			default:
				App::$input = $_REQUEST;
			break;
		}
		// print_r(App::$input);die('..');
		//This functions can receive an array like this: action[Object][id][varName]=value
		//It will save any data sent by user, update it, delete it, or set a global varriable of any other sent values.
		// if(empty($actions)){$actions=$_REQUEST;}// by default we process any user submitted data
		// $request = array();
		// foreach($actions as $actionName=>$actionParam){
		// 	//$actionName = urldecode($actionName);
		// 	//$actionParam = urldecode($actionParam);
		// 	if(!empty($actionName)){
		// 		switch ($actionName){
		// 			case 'update':
		// 				foreach($actionParam as $name=>$object){
		// 					if(!class_exists($name)){Msg::addMsg('Wrong object type.',0,Msg::WARNING);return false;}
		// 					foreach($object as $id=>$fields){
		// 						$validate = true;
		// 						if($name == 'User' && $id == App::$user->id){$o = App::$user;}
		// 						else{$o = new $name($id);}
		// 						foreach($fields as $n=>$v){
		// 							if(is_array($v) && in_array($n,self::$dateFields)){
		// 								//Transform date arrays to Date
		// 								$v = $v['year'].'-'.$v['month'].'-'.$v['day'];
		// 							}
		// 							if(!$o->validateData($n,$v)){
		// 								$validate=false;
		// 							}
		// 						}
		// 						if($validate){
		// 							if($o->isOwner()){
		// 								if(!$o->update()){
		// 									self::addError('Failed to update '.$name.'::'.$id,80);
		// 								}
		// 							}
		// 							else{
		// 								Msg::addMsg('You may not edit this item.');
		// 							}
		// 						}
		// 						else{
		// 							if(empty(Msg::$notifications)){Msg::addMsg('Failed to validate data.');}
		// 						}
		// 					}
		// 				}
		// 			break;
					
		// 			case 'show':
		// 				foreach($actionParam as $name=>$object){
		// 					if(!class_exists($name)){Msg::addMsg('Wrong object type.',0,Msg::WARNING);return false;}
		// 					foreach($object as $id=>$count){
		// 						switch($name){
		// 							case 'Song':
		// 								$s = new Song($id);
		// 								$s->increment('plays');
		// 							break;
									
		// 							default:
		// 								Msg::addMsg('Object not recognized');
		// 							break;
		// 						}
		// 					}
		// 				}
		// 			break;
					
		// 			case 'insert':
		// 				foreach($actionParam as $object=>$fields){
		// 					if(!class_exists($object)){self::addError('Wrong object type.',60);return false;}
		// 					if($object!='Message'&&$object!='Subscriber'&&$object!='User'&&App::$user->id==0){Msg::addMsg(translate('You must login to create objects.'));return false;}
		// 					$o = new $object();
		// 					$validate=true;
		// 					foreach($fields as $n=>$v){
		// 						if(is_array($v) && in_array($n,self::$dateFields)){
		// 							//Transform date arrays to Date
		// 							$v = $v['year'].'-'.$v['month'].'-'.$v['day'];
		// 						}
		// 						if(!$o->validateData($n,$v)){$validate=false;}
		// 					}
		// 					if($validate){
		// 						if(!$o->id = $o->insert()){
		// 							self::addError('Process: Failed to insert '.$object,80);
		// 						}
		// 					}
		// 					else{
		// 						foreach($fields as $n=>$v){$request[$object][$n]=$v;}
		// 						if(empty(Msg::$notifications)){Msg::notify('Failed to validate data.');}
		// 					}
		// 					if($object=='User'){$_SESSION['uid'] = $o->id;}
		// 					$request[$o->className] = $o->id;
		// 					$request[$o->className.'_insert'] = $validate;
		// 				}
		// 			break;
					
		// 			case 'buy':
		// 				foreach($actionParam as $object=>$fields){
		// 					if($object == 'domain'){
								
		// 					}
		// 					else{
		// 						$o = new Shopitem($object);
		// 						$o->clicks++;
		// 						$o->update();
		// 						$colorStr = '';
		// 						$quantity = 0;
		// 						$comments = '';
		// 						$dataError = false;
		// 						$options = array();
		// 						$r = '';
		// 						if(isset($fields['printFormat']) || $o->type == 'album'){
		// 							if($o->type == 'album'){
		// 								$fid = ShopitemFormat::$albumFormatId;
		// 								if(isset($fields['cd'])){
		// 									$options['cd'] = true;
		// 								}
		// 							}
		// 							else{
		// 								$fid = $fields['printFormat'];
		// 							}
		// 							if(isset($o->priceGenre->formats[(string)$fid])){
		// 								$printFormatId = $fid;
		// 							}
		// 							else{
		// 								$r .= 'invalid format: '.$fid;
		// 								$dataError = true;
		// 							}
		// 						}
		// 						else{
		// 							$r .= 'no printFormat error.';
		// 							$dataError = true;
		// 						}
								
		// 						if(isset($fields['comments']) && $fields['comments'] != translate('add comments')){
		// 							$comments = $fields['comments'];
		// 						}
		// 						else{
		// 							$comments = '0';
		// 						}
		// 						//if(App::$user->access==1){die(print_r($fields));}
		// 						if(isset($fields['papertype'])){
		// 							$options['papertype'] = $fields['papertype'];
		// 						}
		// 						if(isset($fields['frame']) && ($fields['frame'] == 'on' || $fields['frame'] == 1)){
		// 							$options['frame'] = $fields['frametype'];
		// 						}
								
		// 						$colorStr = Shopitem::getColor($options);
								
		// 						if(isset($fields['quantity'])){
		// 							$quantity = intval($fields['quantity']);
		// 						}
		// 						else{
		// 							$quantity = 1;
		// 						}
								
		// 						if($dataError == true){
		// 							Msg::addMsg('Data type error ['.$r.']');
		// 							return false;
		// 						}
		// 						$cartArr = array();
		// 						$cartArr['shopItemId'] = $o->id;
		// 						$cartArr['printFormatId'] = $printFormatId;
		// 						$cartArr['quantity'] = $quantity;
		// 						$cartArr['comments'] = $comments;
		// 						$cartArr['options'] = json_encode($options);
		// 						$cartArr['color'] = $colorStr;
								
		// 						if(!isset($_SESSION['cart'])){
		// 							$_SESSION['cart'] = array();
		// 						}
		// 						$_SESSION['cart'][] = $cartArr;
		// 					}
		// 				}
		// 			break;
					
		// 			case 'delete':
		// 				foreach($actionParam as $name=>$param){
		// 					if(!class_exists($name)){LP::addError('Wrong object type ['.$name.'].',90);}
		// 					else{
		// 						foreach($param as $id=>$value){
		// 							$object = new $name($id);
		// 							if(!$object->delete()){
		// 							    Msg::addMsg('Failed to delete '.$name);
		// 							}
		// 						}
		// 					}
		// 				}
		// 			break;
					
		// 			case 'button':
		// 			break;
					
		// 			case 'glob':
		// 				foreach($actionParam as $name=>$value){
		// 					$request[$name]=$value;
		// 				}
		// 			break;
					
		// 			default:
		// 				//No action was specified, so we assume it is meant to be a global variable used for validation like logging in.
		// 				$request[$actionName]=$actionParam;
		// 			break;
		// 		}
		// 	}
		// 	else{
		// 		Msg::addMsg('No action specified.');
		// 	}
		// }
		// unset($_REQUEST);
		// unset($_POST);
		// unset($_GET);
		// $_GET = $_POST = $_REQUEST = $request;
	}
	
	public static function log_stat(){
		//SAVE STATS
		$stat = new Stat();
		$stat->ip = USER_IP;
		$stat->uid = $_SESSION['uid'];
		$stat->session = session_id();
		$stat->isbot = App::$browser['crawler'];
		$stat->isunique = !isset($_COOKIE['pixyt']);
		$stat->host = HOST;
		$stat->url = App::$url->path;
		$stat->browser = App::$browser['browser'];
		$stat->platform = App::$browser['platform'];
		$stat->screen = SCREEN;
		$stat->window = WINDOWW.'x'.WINDOWH;
		$stat->from = $_SESSION['from'];
		$stat->language = $_SESSION['lang'];
		if(App::$device->isTablet()){$stat->device = 'tablet';}
		elseif(App::$device->isMobile()){$stat->device = 'mobile';}
		else{$stat->device = 'desktop';}
		$stat->age = App::$user->age();
		$stat->hour = date('H');
		$stat->day = date('Y-m-d');
		$stat->insert();
	}
}
?>