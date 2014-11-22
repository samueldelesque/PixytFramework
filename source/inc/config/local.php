<?php
date_default_timezone_set('America/New_York');
define('DB_NAME','pixyt_1');
define('DB_USER','root');
define('DB_PASSWORD','root');
define('DB_SERVER','localhost');

define('DB_STAT_NAME','stats');
define('DB_STAT_USER','root');
define('DB_STAT_PASSWORD','root');
define('DB_STAT_SERVER','localhost');

//Constants
define('LIVE',false);
define('URL_SEPARATOR', '&amp;');
define('URL_IDENTIFIER','=');
define('ADMIN_FIRSTNAME', 'Pixyt');
define('ADMIN_LASTNAME', 'Crew');
define('ADMIN_EMAIL', 'crew@pixyt.com');
define('ADMIN_PASSWORD', 'iaefiaheknfaejhvsuhbsef');
define('OVH_NIC','ds67843-ovh');
define('OVH_PWD','aojf983yuhkjuUEG_1fa');
define('SIREN','75118871500013');
define('SITE_KEY', 'iaoyGOEYFgKUJHGvfjkIglsehbzjg7igieiusgiusegfisugihsenbfHHJGEYJHFg');
define('BUILD_DATE', '2011-06-14');
define('TIMESTAMP',date('U'));
define('USER_IP',$_SERVER['REMOTE_ADDR']);
define('DEFAULT_LANG','en_GB');
define('SITENAME', 'Pixyt');
define('SITEVERSION', '0.9');
define('VATRATE', 0.196);
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
$classes[] = 'plugins/vcard/vcard_convert';
$classes[] = 'plugins/vcard/utils';
$classes[] = 'plugins/plesk';
$classes[] = 'plugins/mobile';
$classes[] = 'classes/table';
$classes[] = 'classes/form';
$classes[] = 'connections/facebook/facebook';
$classes[] = 'plugins/Handlebars/Handlebars';
$classes[] = 'plugins/Handlebars/Helpers';
$classes[] = 'plugins/Handlebars/Loader';
$classes[] = 'plugins/Handlebars/Loader/StringLoader';
$classes[] = 'plugins/Handlebars/Loader/FilesystemLoader';
$classes[] = 'plugins/Handlebars/Parser';
$classes[] = 'plugins/Handlebars/String';
$classes[] = 'plugins/Handlebars/Template';
$classes[] = 'plugins/Handlebars/Tokenizer';
$classes[] = 'plugins/Handlebars/Context';
$classes[] = 'plugins/Handlebars/Cache';
$classes[] = 'plugins/Handlebars/Cache/APC';
$classes[] = 'plugins/Handlebars/Cache/Disk';
$classes[] = 'plugins/Handlebars/Cache/Dummy';
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
//Handlebars\Autoloader::register();
$engine = new Handlebars\Handlebars;
?>