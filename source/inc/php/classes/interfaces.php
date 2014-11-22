<?php
class Interfaces extends Pixyt{
	function __construct(){
		parent::__construct();
	}
	
	public function fetch($method=NULL){
		if(!$method){$method = 'directory';}
		
		$response = $this->$method();
		if(is_array($response)){return $response;}
		return array('html'=>$response);
	}
	
	public function data(){
		return new stdClass;
	}
	
	public function directory(){
		require_once(ROOT.'inc/php/interfaces/error.php');
		return Error::e404();
	}
	
	public function __call($m,$a){
		return $this->directory();
	}
	
	public function noaccess(){
		header('Location: '.HOME.'home/login?msg=login-first&backurl='.urlencode($_REQUEST['url']));
		exit();
	}
}
?>