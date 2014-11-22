<?php
date_default_timezone_set('America/New_York');
//Shhhhh... secret!
define('DB_NAME','pixyt');
define('DB_USER','root');
define('DB_PASSWORD','aouef733tfa9IDwj37');
define('DB_SERVER','localhost');

define('DB_STAT_NAME','stats');
define('DB_STAT_USER','root');
define('DB_STAT_PASSWORD','aouef733tfa9IDwj37');
define('DB_STAT_SERVER','localhost');

define('AWS_USER','pixyt-store');
define('AWS_KEYID','AKIAJ62STRWMFR3AIYNQ');
define('AWS_KEY','XYSSd/WM5ZkOK7/UPC6MQFRGDy16NUZxC1QxCvco');

//Constants
define('ADMIN_EMAIL', 'crew@pixyt.com');
define('ADMIN_PASSWORD', 'iaefiaheknfaejhvsuhbsef');
define('OVH_NIC','ds67843-ovh');
define('OVH_PWD','aojf983yuhkjuUEG_1fa');
define('SIREN','75118871500013');
define('SITE_KEY', 'iaoyGOEYFgKUJHGvfjkIglsehbzjg7igieiusgiusegfisugihsenbfHHJGEYJHFg');
define('TIMESTAMP',date('U'));
define('USER_IP',$_SERVER['REMOTE_ADDR']);
define('DEFAULT_LANG','en_GB');
define('SITENAME', 'Pixyt');
define('VATRATE', 0.2);
define('RATE_ARTIST', 0.75); // percent of added price for artist
define('PICTO_FTP_SERVER', 'ftp.picto.fr');
define('PICTO_FTP_USER', 'pixyt');
define('PICTO_FTP_PASS', 'x3u1fj0q');
define('PICTO_FTP_DIRPREFIX', 'commandes');
define('LOCAL_ORDER_PATH', 'local/orders');
define('MESSAGES_AUTH_TIMEOUT', 864000);

//utility Classes
$classes[] = 'classes/pixyt';
$classes[] = 'classes/db';
$classes[] = 'classes/query';
$classes[] = 'classes/functions';
$classes[] = 'classes/app';
$classes[] = 'classes/template';
$classes[] = 'classes/templates';
$classes[] = 'classes/display';
$classes[] = 'classes/interfaces';
$classes[] = 'classes/translate';
$classes[] = 'classes/email';
$classes[] = 'classes/msg';
$classes[] = 'classes/object';
$classes[] = 'classes/collection';
$classes[] = 'classes/process';
$classes[] = 'paypal/paypalfunctions';
$classes[] = 'plugins/mobile';
$classes[] = 'classes/table';
$classes[] = 'classes/form';
$classes[] = 'connections/facebook/facebook';
$classes[] = 'plugins/Handlebars/Autoloader';

foreach($classes as $class){
	if(ERROR_LEVEL == 0)echo 'Loading '.$class.'...';
	include(ROOT.'inc/php/'.$class.'.php');
	if(ERROR_LEVEL == 0)echo 'OK</br>';
}
//load objects
foreach(App::$objects as $class){
	if(ERROR_LEVEL == 0)echo 'Loading php/objects/'.$class.'...';
	include(ROOT.'inc/php/objects/'.$class.'.php');
	if(ERROR_LEVEL == 0)echo 'OK</br>';
}
Handlebars\Autoloader::register();
?>