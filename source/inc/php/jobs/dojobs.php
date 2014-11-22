#!/usr/bin/php
<?php
define('CLI',true);
require_once('../../../init.php');
App::initialize(DB_NAME,DB_USER,DB_PASSWORD,DB_SERVER);
echo '------- Welcome to Pixyts script interface --------'.PHP_EOL;
$x=0;$y=0;
for($i=0;$i<999;$i++){
	$photo = new Photo($i);
	if(!empty($photo->fileid) && !file_exists(ROOT.'cdn/'.$photo->uid.'/small/'.$photo->id.'.jpg')){
		$file = new File($photo->fileid);
		//echo $photo->id.'... MISSING!'.PHP_EOL;
		$x++;
		if(!file_exists($file->path())){
			//echo 'Additionally, the Original file was missing...('.$file->path().')!'.PHP_EOL;
			$y++;
		}
	}
}
echo $x.' photos missing, '.$y.' with no file..'

?>