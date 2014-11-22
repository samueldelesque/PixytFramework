<?php
class App{
	//The input REQUEST/DATA data
	public static $input;
	
	//User object
	public static $user;
	
	//The current site Object
	public static $site;
	
	//The current site publisher
	public static $publisher;
	
	//User Device (isMobile etc)
	public static $device;
	
	//User browser
	public static $browser;
	
	//Message inbox
	public static $inbox;
	
	//DB object
	public static $db;
	
	//translation object
	public static $translator;

	//the template engine Object
	public static $handlebars;
	
	//Cached objects
	public static $cache;
	
	//the loaded url, split into: url->interface, url->id, url->section, $url->params, $url->path
	public static $url;
	
	//script timing
	public static $start;

	//notifications counters
	public static $notifications = array(
		'feed' => 0,
		'cart' => 0,
		'inbox' => 0,
		'feedback' => 0,
	);
	
	//exiting Object models
	public static $objects = array(
		'User',
		'Photo',
		'Song',
		'Article',
		'Stack',
		'Message',
		'Order',
		'Feedback',
		// 'Contact',
		'Question',
		'Product',
		'Sale',
		'Subscriber',
		'Follower',
		'Site',
		'Transaction',
		'Translation',
		'File',
		'Stat',
		'Feed',
		'Tag',
		'Credit',
	);
	
	
	public static $interfaces = array(
		'admin',
		'account',
		'apps',
		'archive_sync',
		'accounting',
		'checkout',
		'connect',
		'contest',
		'corp',
		'error',
		'help',
		'home',
		'lightroom',
		'organize',
		'photos',
		'rss',
		'instants',
		'search',
		'stacks',
		'sites',
		'terms',
		'transactions',
		'upload',
		'website',
	);
	
	//shortcut keys
	public static $objectTypes = array(
		1=>'Stack',
		2=>'Photo',
		3=>'Song',
		4=>'Article',
		5=>'User',
	);
	
	static function initialize($db,$username,$password,$host){
		self::$start = microtime(true);
		self::loadEnvironment();
		
		//initialize cache
		foreach(self::$objects as $class){self::$cache[$class] = array();}
		
		//initialize DB
		App::$db = new Mysql($db,$username,$password,$host);

		//set url details
		$u = explode('/',$_REQUEST['url']);

		if(!isset($u[1])){$u[1]=NULL;}
		if(!isset($u[2])){$u[2]=NULL;}
		if(!isset($u[3])){$u[3]=NULL;}
		if(!isset($u[4])){$u[4]=NULL;}
		self::$url = new stdClass();
		self::$url->format = $_REQUEST['format'];
		self::$url->path = $_REQUEST['url'];
		//if(!in_array($u[0],Display::interfaces())){$u[0]='Display';}
		self::$url->interface = strtolower($u[0]);
		self::$url->method = (isset($_SERVER['REQUEST_METHOD']))?strtolower($_SERVER['REQUEST_METHOD']):'GET';
		$obj = ucfirst(self::$url->interface);
		if(in_array($obj,self::$objects)){
			self::$url->id = $u[1];
			self::$url->function = $u[2];
			self::$url->object = new $obj(self::$url->id);
			self::$url->params = array_slice($u,3);
		}
		else{
			if(self::$url->interface == ''){self::$url->interface = 'index';}
			if(empty($u[1])){$u[1] = 'directory';}
			self::$url->function = $u[1];
			self::$url->params = array_slice($u,2);
		}
		

		//Check if user has same IP as last time, else log him out to avoid man in the middle attacks to API.
		if(isset($_SESSION['USER_IP']) && $_SESSION['USER_IP'] !== USER_IP){App::$user=new User();$_SESSION['uid'] = 0;}
		$_SESSION['USER_IP'] = USER_IP;

		//initialize template engine
		self::$handlebars = new Handlebars\Handlebars;
		// self::$handlebars->addHelper('eq',function($v1) {
		// 	if($v1=='x'){return true;}
		// 	return false;
		// });

		//load my data, set my device
		if(!isset($_SESSION['uid'])){$_SESSION['uid']=0;}
		App::$user = new User($_SESSION['uid']);
		
		self::$device = new MobileDetect();
		
		if(HOST=='localhost' || (defined('FROMIP') && FROMIP) || defined('CLI')){
			self::$browser = array(
				'parent' => 'Chrome Generic',
				'platform' => 'MacOSX',
				'browser' => 'Chrome',
				'crawler' => false,
			);
		}
		else{
			self::$browser = get_browser($_SERVER['HTTP_USER_AGENT'], true);
		}
			
		//Location info (lang, time...)
		if(!defined('CLI')){
			if(isset($_REQUEST['lang']) && isset(Translate::$languages[$_REQUEST['lang']])){
				App::$user->settings->language = $_SESSION['lang'] = $_REQUEST['lang'];
			}
			if(!isset($_SESSION['lang'])){
				if(isset(App::$user->settings->language) && in_array(App::$user->settings->language,Translate::$languages)){
					$_SESSION['lang'] = App::$user->settings->language;
				}
				else{
					if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){$_SERVER['HTTP_ACCEPT_LANGUAGE']='en';}
					$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2);
					$lang = strtolower($lang);
					switch($lang){//AVAILABLE LANGUAGES
						case 'fr':
							App::$user->settings->language = $_SESSION['lang'] = 'fr_FR';
						break;
						
						case 'en':
						default:
							App::$user->settings->language = $_SESSION['lang'] = 'en_GB';
						break;
					}
				}
			}
		}
		else{
			$_SESSION['lang'] = DEFAULT_LANG;
		}
		putenv('LC_ALL='.$_SESSION['lang']);
		setlocale(LC_ALL, $_SESSION['lang'].'.UTF-8');
		bindtextdomain('lang', 'local/locale/');
		textdomain('lang');
		
		
		//Load user inbox		
		self::$inbox = new Collection('Message');
		self::$inbox->where(array('uid'=>$_SESSION['uid'],'unread'=>true));

		//all parameters are set, start processing data			
		Process::processActions($_REQUEST);
	}

	public function loadNotifications(){
		App::$notifications['cart'] = App::$db->count('Sale',array('uid'=>$_SESSION['uid'],'status'=>'0'));
	
		$unseen = array(
			'proprietor'=>$_SESSION['uid'],
			'uid !='=>$_SESSION['uid'],
			'seen'=>0,
			'created >'=>time()-1209600,
		);
		App::$notifications['feedback'] = App::$db->count('Feedback',$unseen);
		
		$unseen = array(
			'via !='=>'log',
			'uid'=>$_SESSION['uid'],
			'unread'=>1,
		);
		App::$notifications['feedback'] += App::$db->count('Message',$unseen);
	}
	
	public static function cached($obj){
		return isset(self::$cache[$obj->className][$obj->id]);
	}
	
	public static function loadTime(){
		return round((microtime(true) - self::$start)*1000);
	}
	
	public static function get($obj){
		if(!isset(self::$cache[$obj->className][$obj->id])){
			self::$cache[$obj->className][$obj->id] = self::$db->get($obj);
		}
		return self::$cache[$obj->className][$obj->id];
	}

	private static function loadEnvironment(){

		// Fix permissions if necessary
		if(get_current_user() === 'pixyt' || get_current_user() === 'www-data'){exec('sudo /usr/local/bin/fix_pixyt_permissions.php');}

		//define http user, protocol and port
		if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)){define('PROTOCOL','https://');}
		else{define('PROTOCOL','http://');}
		if(isset($_SERVER['HTTPS']) && isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443 || $_SERVER['SERVER_PORT'] == 80)){define('PORT',':'.$_SERVER['SERVER_PORT']);}
		else{define('PORT','');}
		if(isset($_SERVER['REMOTE_USER'])){define('REMOTE_USER',$_SERVER['REMOTE_USER'].'@');}
		else{define('REMOTE_USER','');}

		//define http host
		if(!defined('CLI') && !defined('WEBDAV')){
			if(preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/",$_SERVER['HTTP_HOST'])){
				define('HOST',$_SERVER['HTTP_HOST']);
				define('FROMIP',true);
			}
			else{
				preg_match('@^(?:http://)?([^/]+)@i',$_SERVER['HTTP_HOST'], $matches);
				$host = $matches[1];
				$domain = explode('.',$host);
				$backward = array_reverse($domain);
				if(isset($backward[1]) && (isset($backward[2]) &&$backward[2] != 'www')){
					preg_match('/[^.]+?(.)[^.]+$/', $host, $matches);
					define('HOST',$host);
				}
				else{
					if(!empty($backward[1])){$h = $backward[1].'.'.$backward[0];}
					else{$h = $backward[0];}
					define('HOST',$h);
				}
				define('FROMIP',false);
			}
			
		}
		else{
			if(defined('WEBDAV')){
				define('HOST', 'pixyt.com');
			}else{
				define('HOST', 'pixyt.com');
				$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
			}
		}

		//home variable - for URLs
		define('HOME',PROTOCOL.REMOTE_USER.HOST.'/');

		//define if website is called from ajax
		if((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=="XMLHttpRequest") ||  isset($_REQUEST['ajax'])){define('IS_AJAX',true);}
		else{define('IS_AJAX',false);}
		if(isset($_REQUEST['datatype']) && $_REQUEST['datatype'] == 'lightroom'){define('LIGHTROOM',true);}

		//These vars must be defined at all time
		if(!isset($_REQUEST['url'])){$_REQUEST['url'] = '';}
		if(!isset($_SESSION['uid'])){$_SESSION['uid']=0;}
		if(!isset($_REQUEST['s'])){$_REQUEST['s']=0;}

		//the output format requested. 
		if(isset($_REQUEST['format']) && in_array($_REQUEST['format'],array('html','json'))){}
		elseif(isset($_REQUEST['datatype'])){$_REQUEST['format']=$_REQUEST['datatype'];}
		elseif(isset($_SERVER['HTTP_ACCEPT']) && substr($_SERVER['HTTP_ACCEPT'],0,16) == 'application/json'){$_REQUEST['format'] = 'json';}
		elseif(isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json'){$_REQUEST['format'] = 'json';}
		else{$_REQUEST['format'] = 'html';}

		//set screen resolution if cookie is set
		if(isset($_COOKIE['ww'])){define('WINDOWW',$_COOKIE['ww']-15);}
		else{define('WINDOWW',1024);}
		if(isset($_COOKIE['wh'])){define('WINDOWH',$_COOKIE['wh']);}
		else{define('WINDOWH',600);}
		if(isset($_COOKIE['screen'])){define('SCREEN',$_COOKIE['screen']);}
		else{define('SCREEN','');}

		//Timezone
		if(isset($_COOKIE['gmtOffset'])){define('GMTOFFSET',$_COOKIE['gmtOffset']);}
		else{define('GMTOFFSET',0);}
		if(isset($_COOKIE['timezone'])){define('TIMEZONE',$_COOKIE['timezone']);}
		else{define('TIMEZONE','Europe/Paris');}

		//error reporting
		if(ENV=="dev"||ENV=="local"||ENV=="stg"){
			ini_set('display_errors','On');
			error_reporting(E_ALL);
			//set_error_handler('error',E_ALL);
		}
		else{
			ini_set('display_errors','off');
			error_reporting(0);
		}

		//timezone and encoding
		ini_set('date.timezone', TIMEZONE);
		ini_set('default_charset', 'utf-8');
		bind_textdomain_codeset('lang', 'UTF-8');
		mb_internal_encoding('utf-8');


		//start output
		session_start();
		header('charset:utf-8');
		header('Access-Control-Allow-Origin: *');

		//FROM - use remote input first, fall back to local
		if(isset($_REQUEST['from'])){$_SESSION['from'] = $_REQUEST['from'];}
		elseif(isset($_REQUEST['utm_source'])){$_SESSION['from'] = $_REQUEST['utm_source'];}
		elseif(isset($_SESSION['from'])){}
		elseif(isset($_SERVER['HTTP_REFERER'])){
			preg_match('@^(?:http://)?([^/]+)@i',$_SERVER['HTTP_REFERER'], $matches);
			if(isset($matches[1]))$from = $matches[1];else $from = '';
			$_SESSION['from'] = $from;
		}
		else{$_SESSION['from'] = NULL;}

		//CAMPAIGN - use remote input first, fall back to local
		if(isset($_REQUEST['campaign'])){$_SESSION['campaign'] = $_REQUEST['campaign'];}
		elseif(isset($_REQUEST['utm_campaign'])){$_SESSION['campaign'] = $_REQUEST['utm_campaign'];}
		elseif(isset($_SESSION['campaign'])){}
		else{$_SESSION['campaign'] = NULL;}

		//CAMPAIGN - use remote input first, fall back to local
		if(isset($_REQUEST['medium'])){$_SESSION['medium'] = $_REQUEST['medium'];}
		elseif(isset($_REQUEST['utm_medium'])){$_SESSION['medium'] = $_REQUEST['utm_medium'];}
		elseif(isset($_SESSION['medium'])){}
		else{$_SESSION['medium'] = NULL;}

		//CAMPAIGN - use remote input first, fall back to local
		if(isset($_REQUEST['keyword'])){$_SESSION['keyword'] = $_REQUEST['keyword'];}
		if(isset($_SESSION['keyword'])){}
		else{$_SESSION['keyword'] = NULL;}

		//if enabled, fill DB with translation keys (english)
		if(isset($_REQUEST['addtranslations'])){
			$_SESSION['addtranslations'] = (bool)$_REQUEST['addtranslations'];
		}
	}
}


/*

//MULTI DOMAIN COOKIES

if(isset($_REQUEST['init'])){
	die(session_id());
}
if(empty($_SESSION)){
	session_destroy();
	if(!isset($_REQUEST['PHPSESSID'])){
		if($_SERVER['HTTP_HOST'] != 'pixyt.com'){
			header('Location: http://pixyt.com/?redirect='.urlencode(LOADED_URL));
			die();
		}
		else{
			$sessionCookieExpireTime=864000;//10*24*60*60;//10 days
			session_set_cookie_params($sessionCookieExpireTime);
			session_start();
			$_SESSION['started'] = time();
			if(isset($_REQUEST['redirect'])){
				$url = urldecode($_REQUEST['redirect']);
				$parts = explode('?',$url);
				if(count($parts)==2){
					$req = urlToVars($parts[1]);
					$req['PHPSESSID'] = session_id();
					$data = varsToUrl($req);
					$redirect = $parts[0].'?'.$parts[1];
				}
				else{
					$redirect = $parts[0].'?PHPSESSID='.session_id();
				}
				header('Location: '.$redirect);
				die();
			}
			else{
				//die('This is a standard Pixyt load');
			}
		}
	}
	else{
		session_id($_REQUEST['PHPSESSID']);
		$sessionCookieExpireTime=864000;//10*24*60*60;//10 days
		session_set_cookie_params($sessionCookieExpireTime);
		session_start();
		//REDIRECT TO REMOVE PHPSESSID FROM URL
	}
}
elseif(isset($_REQUEST['redirect'])){
	$url = urldecode($_REQUEST['redirect']);
	$parts = explode('?',$url);
	if(count($parts)==2){
		$req = urlToVars($parts[1]);
		$req['PHPSESSID'] = session_id();
		$data = varsToUrl($req);
		$redirect = $parts[0].'?'.$parts[1];
	}
	else{
		$redirect = $parts[0].'?PHPSESSID='.session_id();
	}
	header('Location: '.$redirect);
	die();
}
*/	


?>