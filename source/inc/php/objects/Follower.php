<?php
class Follower extends Object{
	public $uid;
	public $who;
	
	public function descriptor(){
		return array (
			'id'=>'int',
			'uid'=>'int',
			'who'=>'int',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		);
	}
	
	protected static function publicFunctions(){
		return array(
			'preview'=>true,
		);
	}
	
	protected static function ownerFunctions(){
		return array();
	}
	
	function validateData($n,$v){
		switch ($n){
			case 'id':
			case 'uid':
			case 'created':
			case 'modified':
				return true;
			break;
			
			case 'who':
				if(isset(App::$user->following[$v])){
					Msg::notify('You are already following that person!');
					return false;
				}
				if(Object::objectExists('User',$v)){
					$this->$n=ucfirst($v);
					return true;
				}
				else{
					Msg::notify('User does not exists!');
				}
			break;
		}
	}
	
	//DISPLAY MODES
	protected function preview(){
		$r = '';
		$r .= dv('follower');
		if($this->uid != App::$user->id){
			$r .= self::followBtn($this->uid);
			$r .= '<span class="name padded">'.User::$users[$this->uid]->fullName('link').'</span>';
		}
		else{
			$r .= self::followBtn($this->who);
			$r .= '<span class="name padded">'.User::$users[$this->who]->fullName('link').'</span>';
		}
		$r .= xdv();
		return $r;
	}
	
	public static function followBtn($who){
		if(App::$user->id == 0){return lnk(translate('follow'),'login',array('backurl'=>urlencode($_REQUEST['url'])),array('class'=>'btn','data-type'=>'popup'));}
		if(isset(App::$user->following[$who])){return lnk(translate('Following'),'ajax',array('delete[Follower]['.App::$user->following[$who]->id.']'=>true),array('title'=>translate('Following'),'ajax'=>true,'class'=>'btn active follow_'.$who));}
		else{return lnk(translate('follow'),'ajax',array('insert[Follower][who]'=>$who),array('title'=>translate('Follow'),'ajax'=>true,'class'=>'btn btn-info follow_'.$who));}
	}
	
	protected function postInsert(){
		$who = new User($this->who);
		$message = '<h4>'.translate('Congrats,').' '.App::$user->fullName('full').' '.translate('started following you.').'</h4>';
		$message .= '<p>'.translate('You might want to').' <a class="btn" href="http://pixyt.com/user/'.App::$user->id.'">'.translate('follow back').'</a></p>';
		$who->notify($message,'followed');
		res('script','$(".follow_'.$this->who.'").attr("href","ajax?delete[Follower]['.$this->id.']").addClass("active").html("'.translate('Following').'");');
		return true;
	}
	
	protected function postDelete($force = false){
		res('script','$(".follow_'.$this->who.'").attr("href","ajax?insert[Follower][who]='.$this->who.'").removeClass("active").html("follow");');
		return true;
	}
}
?>