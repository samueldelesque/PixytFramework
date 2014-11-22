<?php
date_default_timezone_set('America/New_York');
//Shhhhh... secret!
define('DB_NAME','stg');
define('DB_USER','root');
define('DB_PASSWORD','97aet3g3iu');
define('DB_SERVER','localhost');

define('REPLICATION_SLAVE_USER','stg');
define('REPLICATION_SLAVE_PASSWORD','h987H_da!Dud09');

define('DB_STAT_NAME','dev_stats');
define('DB_STAT_USER','dev_stats');
define('DB_STAT_PASSWORD','aoey937uf3a');
define('DB_STAT_SERVER','localhost');

define('AWS_USER','pixyt-store');
define('AWS_KEYID','AKIAJ62STRWMFR3AIYNQ');
define('AWS_KEY','XYSSd/WM5ZkOK7/UPC6MQFRGDy16NUZxC1QxCvco');

//Constants
define('LIVE',true);
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
?>