<?php
class Subscriber extends Object{
	public $uid;
	public $name;
	public $email;
	public $status=1;		//1:on; 0:off; 2:unsubscribed,
	public $secret;			//secret key included in links to unsubscribe
	public $lang;
	public $network=1;			//specific status
	public $corp;
	public $artsales=1;
	public $from;
	
	public function descriptor(){
		return array (
			'id'=>'int',
			'uid'=>'int',
			'name'=>'string',
			'email'=>'string',
			'status'=>'int',
			'secret'=>'string',
			'lang'=>'string',
			'network'=>'int',
			'corp'=>'int',
			'artsales'=>'int',
			'from'=>'string',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		);
	}
	
	public function construct(){
		if($this->id==0){
			$this->lang=$_SESSION['lang'];
			$this->secret = md5(randStr());
			$this->from = HOST;
		}
	}
	
	public $requiredFields = array('email');
	
	public function validateData($n,$v){
		switch ($n){
			case 'id':
			case 'uid':
			case 'created':
			case 'modified':
				return true;
			break;
			
			case 'name':
				$this->$n=$v;
				return true;
			break;
			
			case 'from':
				$this->$n=$v;
				return true;
			break;
			
			case 'lang':
				$this->$n=$v;
				return true;
			break;
			
			case 'email':
				if(!empty($v) && isEmail($v)){
					if(Object::objectExists('Subscriber',$v,'email')){
						Msg::addMsg(translate('You seem to already be in our mailing lists.'));
						$this->isDummy = true;
						return false;
					}
					else{
						$this->$n=$v;
						return true;
					}
				}
				else{
					Msg::addMsg(translate('Please enter a valid email.'));
				}
			break;
		}
	}
	
	public function tableHead(){
		return array('Name','Email','Status','date joined');
	}
	
	public function tableData(){
		return array($this->name,$this->email,$this->status,prettyTime($this->created));
	}
	
	public function directory(){
		if(App::$user->class > 40){return '<h2>You may not access this area!</h2>';}
		$c = new Collection('Subscriber');
		return $c->returnTable();
	}
	
	protected function postInsert(){
		//Msg::addMsg('Thank you for subscribing to our mailing list.');
		$data = array('host'=>HOST);
		templateEmail($this->email,'Pixyt Crew','crew@pixyt.com','A present for you, from Pixyt.','assets/themes/pixyt/tpl/email/launch_signup.html',$data,false);
		//templateEmail($this->email,'Sam','crew@pixyt.com','A present for you, from Pixyt.','assets/themes/pixyt/tpl/email/launch_signup.html',array('title'=>'Thank you for signing up for Pixyt!'));
		return true;
	}
}
?>