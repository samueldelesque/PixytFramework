#!/usr/bin/php
<?php
define('CLI',true);
require_once('../../../local/config.php');
/*
$col = new Collection('Project');
$col->load(0,100);

foreach($col->results as $p){
	if($p->id!=27){
		$uuid = new Uniqueid();
		$uuid->objecttype = 1;
		$uuid->objectid = $p->id;
		$uuid->uid = $p->uid;
		$uuid->created = $uuid->modified = time();
		$uuid->insert(true);
		$stack = new Stack();
		$stack->uid = $p->uid;
		$stack->uuid = $uuid->id;
		$stack->title = $p->title;
		$stack->modified = $p->modified;
		$stack->created = $p->created;
		$stack->insert(true);
		echo 'Project #'.$stack->id.' was updated'.PHP_EOL;
		$col = new Collection('Stack');
		$col->prid('=',$stack->id,true);
		$col->load(0,10000);
		foreach($col->results as $st){
			if($st->uuid == 0){
				$uuid = new Uniqueid();
				$uuid->objecttype = 1;
				$uuid->objectid = $st->id;
				$uuid->uid = $st->uid;
				$uuid->parent = $stack->uuid;
				$uuid->created = $uuid->modified = time();
				$uuid->insert(true);
				$st->uuid = $uuid->id;
				$st->update(true);
				echo 'Stack #'.$st->id.' was updated'.PHP_EOL;
			}
		}
	}
}
*/
$col = new Collection('Stack');
$col->load(0,10000);
foreach($col->results as $stack){
	if($stack->uuid == 0){
		$uuid = new Uniqueid('NEW');
		$uuid->objecttype = 1;
		$uuid->objectid = $stack->id;
		$uuid->uid = $stack->uid;
		$uuid->created = $uuid->modified = time();
		$uuid->insert(true);
		$stack->uuid = $uuid->id;
		if(!$stack->update(true)){die('COULD NOT UPDATE STACK');}
		echo 'Stack #'.$stack->id.' was updated [node.'.$uuid->id.' Stack.uuid='.$stack->uuid.']'.PHP_EOL;
	}
}

$col = new Collection('Photo');
$col->load(0,20000);
foreach($col->results as $photo){
	$parent = new Stack($photo->kid);
	if($photo->uuid == 0){
		$uuid = new Uniqueid();
		$uuid->objecttype = 2;
		$uuid->objectid = $photo->id;
		$uuid->uid = $photo->uid;
		$uuid->parent = $parent->uuid;
		$uuid->created = $uuid->modified = time();
		$uuid->insert(true);
		$photo->uuid = $uuid->id;
		$photo->update(true);
		echo 'Photo #'.$photo->id.' was updated'.PHP_EOL;
	}
}
?>