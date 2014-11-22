#!/usr/bin/php
<?php
define('CLI',true);
require_once('../../../inc/config.php');

$users = new Collection('User');

//Always try one on yourself first ;)
//$users->id = 1;

$users->load(0,99999);
echo 'STARTING JOB'.PHP_EOL;
$total = $users->total(true);
foreach($users->results as $i=>$user){
	echo($i.'/'.$total).PHP_EOL;
	if(!$user->notify('','newsletter')){
		echo('--ERROR----------------------------------Sending to '.$user->fullName().' failed!').PHP_EOL;
	}
	echo $user->fullName().' ok'.PHP_EOL;
}
die('JOB DONE');
?>