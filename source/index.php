<?php
$start = microtime(true);
define('ERROR_LEVEL',2);		//0: output all activity, 1: output all errors, 2: output critical errors, 3: no errors
define('ENV','dev');			//local||dev||stg||prod
switch(ENV){
	case 'dev':define('ROOT','/var/www/pixyt/dev/');break;
	case 'stg':define('ROOT','/var/www/pixyt/stg/');break;
	case 'prod':define('ROOT','/var/www/pixyt/prod/');break;
	default:die('ENV not defined or unknown!');break;
}

include(ROOT.'inc/config/'.ENV.'.php');
App::initialize(DB_NAME,DB_USER,DB_PASSWORD,DB_SERVER);

// echo '<!-- YOU HAVE REACHED THE SERVER. -->';

if(Object::exists('Site',HOST,'url',$id)){
	App::$site = new Site($id);
	if(!empty(App::$site->alias) && Object::objectExists('Site',App::$site->alias,'url',$id)){
		App::$site = new Site($id);
	}

	// if(isset($_GET['cooper'])){
	// $jerem = new User();
	// $jerem->validateData('password','test');
	// $jerem->validateData('email','cooper@test.com');
	// $jerem->insert();
	// die('Cooper created');
	// }
	
	if(in_array(HOST,Site::$pixytSites)){
		App::$site->content = read_json(ROOT.'assets/json/pixyt-routes.json');
		T::$options['GA'] = 'UA-31248995-2';
		if(isset($_GET['showcontent'])){
			var_dump(App::$site->content);
			die('///?'.read_json(ROOT.'assets/json/pixyt-routes.json'));
		}
	}
	elseif(in_array(HOST,Site::$LASites)){
		// die('123');
		T::$options['FB'] = "1412737455657587";
		T::$options['GA'] = 'UA-40552887-1';
		App::$site->settings->icon = '/assets/themes/light-architect/img/logo/favicon.ico';
		App::$site->content = read_json(ROOT.'assets/json/light-architect.json');
		App::$site->content->artists->photographers = read_csv('https://docs.google.com/spreadsheet/pub?key=0AmUjFqoVyGqvdERCbW9PcWhOM25tMWxLMEE1UzVjYnc&single=true&gid=0&output=csv');
		// App::$site->content->shop->products = read_csv('https://docs.google.com/spreadsheet/pub?key=0AmUjFqoVyGqvdERCbW9PcWhOM25tMWxLMEE1UzVjYnc&single=true&gid=0&output=csv');
		sort(App::$site->content->artists->photographers);
	}
	App::$publisher = new User(App::$site->uid);

	$display = new Display();


	if(HOST == 'rodrigoriz.com'){
		// App::$site->settings->icon = 'http://cdn.pixyt.com/sites/rodrigoriz.com/favico.ico';
		// App::$site->content = Site::contentFromDirectory('../cdn/sites/rodrigoriz.com/content/','series');
		// App::$site->update(true);
		// die("Hello");
		// die(json_encode(App::$site->content));
		App::$site->content->index = (object)array(
			'title'=>'Rodrigo Riz photographer',
			'template'=>'index',
			'hidefrommenu'=>true,
		);
		// App::$site->content->index = App::$site->content->women;
		// App::$site->content->index->hidefrommenu = true;
		// App::$site->content->women->hidefrommenu = false;
	}

	if(isset(App::$site->settings->icon)){
		T::$page['icon'] = App::$site->settings->icon;
	}
	T::$page['js'][] = '/assets/js/app.min.js';
	T::$page['js'][] = '/assets/js/router.min.js';
	if(isset(App::$site->settings->theme)){
		T::$page['template'] = App::$site->settings->theme;
		T::$page['css'][] = '/assets/themes/'.App::$site->settings->theme.'/style.css';
		T::$page['js'][] = '/assets/themes/'.App::$site->settings->theme.'/tpl.min.js';
		T::$page['js'][] = '/assets/themes/'.App::$site->settings->theme.'/theme.min.js';
	}
	else{


		// var_dump(App::$site->content);
		// die('----end-----');
		// die(HOST);
		if(ERROR_LEVEL<3)echo '<!-- Site has no Theme! -->';
	}
	
	echo $display->show();
}
else{
	$display = new Display();
	T::$page['css'][] = '/assets/themes/pixyt/style.css';
	if(ERROR_LEVEL<3)echo '<!-- Site not found! -->';
	echo $display->show('/assets/tpl-source/pixyt/elements/404.html',404);
}

// switch(HOST){
// 	case 'pix.yt':
// 		// Shortlinks
// 		header('location: http://pixyt.com');
// 	break;
	
// 	case 'pixyt.fr':
// 	case 'pixyt.dk':
// 	case 'pixyt.com':
// 	case 'pixyt.us':
// 	case 'pixytcontest.com':
// 	case 'dev.pixyt.com':
// 	case 'admin.pixyt.com':
// 		// A Pixyt site
// 		if(ERROR_LEVEL==0){echo 'Display Pixyt<br/>';}
// 		$template = new T('pixyt');

// 		if(ENVIRONMENT == 'dev'){
// 			$json = file_get_contents(ROOT.'assets/json/pixyt-routes.json');
// 			$decoded = json_decode($json);
// 			App::$site->content = $decoded;
// 			if(isset($_GET['show_content'])){
// 				print_r($decoded);
// 				die("\n\nDecoded from: ".$json);
// 			}
// 		}

// 		T::$page['pages'] = App::$site->content;
			
// 		define('BASEPATH',HOME);
		
// 		T::$page['css'][] = '/assets/css/pixyt.css';

// 		T::$page['js'][] = '/assets/js/tpl.min.js';
// 		T::$page['js'][] = '/assets/js/app.min.js';
// 		T::$page['js'][] = '/assets/js/router.min.js';
		
		
// 		$interface = new Display();
// 		echo $interface->show();
// 	break;
	
// 	default:
// 		//Client sites

// 	break;
// }
//Process::log_stat();
Msg::showMessages();
if(App::$user->id != 0){App::$user->update();}
$_SESSION['user'] = (object)App::$user;
?>