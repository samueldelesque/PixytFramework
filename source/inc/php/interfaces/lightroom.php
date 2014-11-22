<?php
class Lightroom{
	protected function getUserStacks(){
		$arr = array();
		
		$myStacks = new Collection('Stack');
		$myStacks->uid = App::$user->id;
		$myStacks->load(0,99999);
		foreach($myStacks->results as $obj){
			$arr[] = array('id' => $obj->id,'title' => $obj->title);
		}
		return json_encode($arr);
	}
	
	protected function createStack(){
		if(!isset($_REQUEST['title']) || !isset($_REQUEST['access'])){
			return 'incorrect parameters';
		}
		
		$title = $_REQUEST['title'];
		$accessId = (int)$_REQUEST['access'];
		if($accessId < 1 || $accessId > 3)
			$accessId = 1;
		
		$stack = new Stack();
		$stack->type = 1;
		$stack->uid = App::$user->id;
		$stack->title = $title;
		$stack->access = $accessId;
		if(!$stack->insert()){
			T::$body[] = 'Failed to create stack!';
			return;
		}
		
		return 'OK:'.$stack->id;
	}
}
?>