#!/usr/bin/php
<?php
define('CLI',true);
set_include_path('/var/www/vhosts/ns383426.ovh.net/pixyt/');
require_once("local/config.php");

//Also notify me of site errors
if(file_exists(ROOT.'local/log/error/'.date('Ymd',time()-500).'.txt')){mail(ADMIN_MAIL,'Pixyt bugs',file_get_contents(ROOT.'local/log/error/'.date('Ymd',time()-500).'.txt'));}

echo 'Starting job, on '.date(DATE_RFC822).PHP_EOL;
die('COMPILE stat must be updated :P');
LP::$db->customQuery('SELECT * FROM `tmpstat`',$data);
$url = array();				//url ->array(pageloads,....)			contains the data
$visitors = array();		//array(sessionid=>pageloads,...)		to check if is unique
$ids = array();

foreach($data as $entry){
	$c = (string)$entry['url'];
	$ids[] = $entry['id'];
	$requestedurl = explode('/',$c);
	$domain = $requestedurl[0];
	if(!isset($url[$c])){
		$url[$c] = array(
			'url'=>$c,
			'pageloads'=>0,
			'visitors'=>0,
			'bots'=>0,
			'loadtime'=>0,
			'memory'=> 0,
			'bytes'=>0,				//upload
			'lenght'=>0,			//average visit time
			'browser'=>array(),
			'platform'=>array(),
			'timeofday'=>array(),
			'location'=>array(),
			'language'=>array(),
			'age'=>array(),
			'screen'=>array(),
			'camefrom'=> array(),
			'crawler'=> array(),
			'access'=>array(),
		);
	}
	$url[$c]['loadtime'] += (int)$entry['loadtime'];
	$url[$c]['memory'] += (int)$entry['memory'];
	$url[$c]['bytes'] += (int)$entry['bytes'];
	if($entry['isbot'] == 1){
		$url[$c]['bots']++;
		if(!isset($url[$c]['crawler'][(string)$entry['browser']])){$url[$c]['crawler'][(string)$entry['browser']] = 1;}
		else{$url[$c]['crawler'][(string)$entry['browser']]+=1;}
	}
	else{
		$url[$c]['pageloads']++;
		if(!isset($url[$c]['access'][(string)$entry['access']])){$url[$c]['access'][(string)$entry['access']] = 1;}
		else{$url[$c]['access'][(string)$entry['access']]++;}
		if(!isset($url[$c]['browser'][(string)$entry['browser']])){$url[$c]['browser'][(string)$entry['browser']] = 1;}
		else{$url[$c]['browser'][(string)$entry['browser']]++;}
		if(!isset($url[$c]['platform'][(string)$entry['platform']])){$url[$c]['platform'][(string)$entry['platform']] = 1;}
		else{$url[$c]['platform'][(string)$entry['platform']]++;}
		if(!isset($url[$c]['timeofday'][(string)$entry['timeofday']])){$url[$c]['timeofday'][(string)$entry['timeofday']] = 1;}
		else{$url[$c]['timeofday'][(string)$entry['timeofday']]++;}
		if(!isset($url[$c]['location'][(string)$entry['location']])){$url[$c]['location'][(string)$entry['location']] = 1;}
		else{$url[$c]['location'][(string)$entry['location']]++;}
		if(!isset($url[$c]['language'][(string)$entry['language']])){$url[$c]['language'][(string)$entry['language']] = 1;}
		else{$url[$c]['language'][(string)$entry['language']]++;}
		if(!isset($url[$c]['age'][(string)$entry['age']])){$url[$c]['age'][(string)$entry['age']] = 1;}
		else{$url[$c]['age'][(string)$entry['age']]+=1;}
		if(!isset($url[$c]['camefrom'][(string)$entry['camefrom']])){$url[$c]['camefrom'][(string)$entry['camefrom']] = 1;}
		else{$url[$c]['camefrom'][(string)$entry['camefrom']]+=1;}
			
		//UNIQUE VISITOR COUNT
		if(!isset($visitors[$domain])){
			$visitors[$domain] = array();
		}
		if(!isset($visitors[$domain][(string)$entry['session']])){
			$visitors[$domain][(string)$entry['session']] = array();
		}
		$visitors[$domain][(string)$entry['session']][] = $entry['time'];
	}
}

foreach($visitors as $domain=>$sessions){
	if(!isset($url[$domain])){
		$url[$domain] = array(
			'url'=>$domain,
			'pageloads'=>0,
			'visitors'=>0,
			'bots'=>0,
			'loadtime'=>0,
			'memory'=> 0,
			'bytes'=>0,
			'lenght'=>0,
			'browser'=>array(),
			'platform'=>array(),
			'timeofday'=>array(),
			'location'=>array(),
			'language'=>array(),
			'age'=>array(),
			'camefrom'=> array(),
			'crawler'=> array(),
			'access'=>array(),
		);
	}
	$url[$domain]['visitors'] = count($sessions);
	$t=0;
	foreach($sessions as $time){
		ksort($time);
		$s = (int)$time[0];
		$e = (int)end($time);
		$t = $t+($e-$s);
	}
	$url[$domain]['lenght'] = $t;		//average session duration
}

foreach($url as $entry){
	if(!LP::$db->insertInto('Stat',
		array(
			'url'=>$entry['url'],
			'pageloads'=>$entry['pageloads'],
			'visitors'=>$entry['visitors'],
			'bots'=>$entry['bots'],
			'loadtime'=>$entry['loadtime'],
			'memory'=>$entry['memory'],
			'bytes'=>$entry['bytes'],
			'lenght'=>$entry['lenght'],
			'browser'=>json_encode($entry['browser']),
			'platform'=>json_encode($entry['platform']),
			'timeofday'=>json_encode($entry['timeofday']),
			'location'=>json_encode($entry['location']),
			'language'=>json_encode($entry['language']),
			'age' =>json_encode($entry['age']),
			'camefrom' =>json_encode($entry['camefrom']),
			'crawler'=>json_encode($entry['crawler']),
			'access'=>json_encode($entry['access']),
			'date'=>date('Y-m-d')
		)
	)){
		die('Failed to Insert Stat.');
		break;
	}
}
$q = 'DELETE FROM `tmpstat` WHERE `id` IN ('.implode(',',$ids).')';
if(!LP::$db->customQuery($q,$d)){
	die('Failed to delete tmpstat items.');
}
?>