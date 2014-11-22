<?php
class Connect extends Interfaces{
	public function display($mode,$settings=array()){
		return call_user_func_array(array($this,$mode),$settings);
	}
	public function twitter(){
		require_once(ROOT.'inc/php/connections/twitter/twitter.php');
		$r = '';
		$twit = new Twitter();
		$twit->requestCredentials();
		return $r;
	}
	
	public function tumblr(){
		$r = '';
		$r .= 'No functions defined here yet.';
		return $r;
	}
}
?>