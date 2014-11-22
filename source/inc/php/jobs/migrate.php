#!/usr/bin/php
<?php
set_time_limit(0);

define('CLI',true);
define('ERROR_LEVEL',1);
define('ENV','DEV');
define('ROOT','../../../');
require_once(ROOT.'inc/config/dev.php');


/*

Load site

*/
$json = file_get_contents(ROOT.'assets/json/samueldelesque.json');
$site = json_decode($json);


$new = new stdClass;

/*
	array(
		pageid=>array(
		.	x=>(portfolio(2)/shop(3)/folder(1)),contact(4),client(5),custom HTML(6), predifined pages (7)
		.	t=>NULL,[TITLE]
		.	d=>NULL,[DESCRIPTION]
			u=>NULL,[URL]
			c=>array([CONTENT]
				itemid=>array(
					if x==folder - list of pages
					objectType,objectId
				)
			),
			m=>BOOL,[Show in Menu?]
			v=>BOOL [view mode] (0=>vertical,1=>horizontal,2=>thumbs,3=>slideshow)
			i,[CREATIONDATE]
			y[MODIFYDATE]
		)
	)
*/
function build_page($page){
	$add = new stdClass;
	$add->title = (string)$page->t;
	$add->description = (string)$page->d;
	$add->visible = (bool)$page->m;
	switch(intval($page->x)){
		case 4:
			$add->template = 'contact';
		break;

		default:
		case 2:
			switch(intval($page->v)){
				case 1:
					$add->template = 'horizontal';
				break;

				case 2:
					$add->template = 'thumbs';
				break;

				case 3:
					$add->template = 'slideshow';
				break;

				default:
				case 0:
					$add->template = 'vertical';
				break;
			}
		break;
	}
	$add->content = array();
	foreach($page->c as $obj){
		$item = new stdClass;
		$item->type = (string)$obj[0];
		$item->id = (int)$obj[1];
		$add->content[] = $item;
	}

	return $add;
}
foreach($site as $page){
	if(intval($page->x) == 1){
		$add = new stdClass;
		$add->title = $page->t;
		$add->description = $page->d;
		$add->visible = (bool)$page->m;
		$add->pages = new stdClass;
		foreach($page->c as $p){
			if($p->x == 1){die('Sub sub directory found!');}
			$add->pages->{$p->u} = build_page($p);
		}
		$new->{$page->u} = $add;
	}
	else{
		$new->{$page->u} = build_page($page);
	}
}

file_put_contents(ROOT.'assets/json/samueldelesque.new.json', json_encode($new));

?>