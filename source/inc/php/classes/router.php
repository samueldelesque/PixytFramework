<?php
class Router extends Pixyt{
	function __construct($url=NULL){
		if(!$url)$url=App::$url->path;
		
	}
}
?>