<?php
class Photos extends Interfaces{
	public function directory(){
		return $this->all();
	}
	
	public function all(){
		$stacks = new Collection('Photo');
		if(isset($_REQUEST['uid'])){
			if($_REQUEST['uid'] == 'me' && $_SESSION['uid'] != 0){$_REQUEST['uid'] = $_SESSION['uid'];}
			$stacks->where('uid',$_REQUEST['uid']);
		}
		if(isset($_REQUEST['kid'])){
			$stacks->where('kid',$_REQUEST['kid']);
		}
		
		$stacks->load();
		return $stacks->data();
	}
}
?>