#!/usr/bin/php
<?php
define('CLI',true);
require_once('../../../local/config.php');

$col = new Collection('Site');
$col->load(0,10000);
function chck($page){
	if(!isset($page['t'])){
		unset($site->data['content'][$id]);
		echo '	1 page removed due to missing title'.PHP_EOL;
	}
	else{
		$page['u'] = normalize($page['t']);
	}
	if(!isset($page['x'])){
		$page['x'] = 1;
		echo '	page ['.$page['u'].'][x] was set to 1';
	}
	if(!isset($page['m'])){
		$page['m'] = 1;
		echo '	page ['.$page['u'].'][m] was set to 1';
	}
	if(!isset($page['d'])){
		$page['d'] = '';
		echo '	page ['.$page['u'].'][d] was set to NULL';
	}
	if(!isset($page['i'])){
		$page['i'] = time();
		echo '	page ['.$page['u'].'][i] was set to time()';
	}
	if(!isset($page['y'])){
		$page['y'] = time();
		echo '	page ['.$page['u'].'][y] was set to time()';
	}
	if($page['x'] != 4 && !is_array($page['c'])){
		$page['c'] = array();
		echo '	page ['.$page['u'].'][c] was set to ARRAY';
	}
}
foreach($col->results as $site){
	echo $site->url.':'.PHP_EOL;
	foreach($site->content as $id=>$page){
		chck($page);
		if($page['x'] == 1){
			foreach($page['c'] as $p){
				chck($c);
			}
		}
	}
	$site->update(true);
}
?>