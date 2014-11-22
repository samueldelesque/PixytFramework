<?php
class Message extends Object{
	public $uid;
	public $to_email;
	public $from;
	public $from_email;
	public $title;
	public $content;
	public $via = 'form';		//form, newsletter, notification
	public $format = 'text';
	public $unread = true;
	public $openDate;
	public $archived_sender;
	public $archived_receiver;
		
	protected static function publicFunctions($key=NULL){
		return arrayGetKey($key,array(
			'fullView'=>true,
			'preview'=>true,
			'conversation'=>true,
			'activity'=>true,
			'compose'=>true,
			'suggest'=>true,
			'gethtml'=>true,
		));
	}
	
	protected static function ownerFunctions($key=NULL){
		return arrayGetKey($key,array(
			'edit'=>true,
			'editPreview'=>true,
		));
	}
	
	public function descriptor($key=NULL){
		return arrayGetKey($key,array(
			'id'=>'int',
			'uid'=>'int',
			'to_email'=>'string',
			'from'=>'int',
			'from_email'=>'string',
			'title'=>'string',
			'content'=>'string',
			'via'=>'string',
			'format'=>'string',
			'unread'=>'bool',
			'openDate'=>'int',
			'archived_sender'=>'bool',
			'archived_receiver'=>'bool',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		));
	}

	public function archive(){
		if(App::$user->id==$this->uid){
			$this->archived_receiver = !$this->archived_receiver;
			$this->update(true);
			return true;
		}
		elseif(App::$user->id==$this->from){
			$this->archived_sender = !$this->archived_sender;
			$this->update(true);
			return true;
		}
		else{
			Msg::addMsg('You may not archive this message!');
			return false;
		}
	}
	
	public function validateData($n,$v){
		switch ($n){
			case 'id':
			case 'created':
			case 'modified':
				return true;
			break;
			
			case 'role':
			case 'name':
			case 'subject':
				$this->tmp[$n] = $v;
				return true;
			break;
			
			case 'uid':
				if(is_numeric($v)){
					$this->$n = $v;
					return true;
				}
				else{
					Msg::addMsg(translate('You must specify a recipient.'));
				}
			break;
			
			case 'from':
				if(is_numeric($v)){
					$this->$n = $v;
					return true;
				}
				elseif(isEmail($v)){
					$this->from_email = $v;
					return true;
				}
				Msg::addMsg(translate('Please provide a valid email.'));
			break;
			
			case 'title':
				if(strlen($v)>0 && strlen($v) <= 255){
					$this->$n = sanitize($v);
					return true;
				}
				else{
					Msg::addMsg(translate('Title must be between 1 and 255 characters.'));
				}
			break;
			
			case 'content':
				if(strlen($v)>0 && $v != translate('What can I help you with?')){
					$this->$n = $v;
					return true;
				}
				else{
					Msg::addMsg(translate('Message cannot be empty.'));
				}
			break;
			
			default:
				Msg::addMsg($n.' no a valid field');
				return false;
			break;
		}
		return false;
	}
	
	public function contentPreview($limit = 30){
		$c = $this->content;
		$c = preg_replace('/[^a-zA-Z0-9!?]/',' ',$c);
		if(strlen($c) < 30){return $c;}
		return substr($c, 0, $limit) . '...';
	}
	
	//DISPLAY MODES
	protected function compose($to=''){
		$r = '';
		$form = new Form('Message','',true,array('ajax'=>true));
		if(empty($to)){
			$form->uid('input','Enter user');
		}
		else{
			$form->uid('hidden',$to);
		}
		$form->from('hidden',App::$user->id);
		$form->content('textarea');
		$form->{translate('send')}('submit');
		$r .= $form->returnContent();
		return $r;
	}
	
	protected function suggest($str=''){
		$r = '';
		$col = new Collection('User');
		$col->firstname('LIKE',$str,false);
		$col->lastname('LIKE',$str);
		$col->load(0,20);
		foreach($col->results as $u){
			$r .= dv('suggestion','','data-uid="'.$u->id.'"').$u->fullName().xdv();
		}
		return $r;
	}
	
	protected function activity(){
		return $this->conversation();
	}
	
	protected function preview(){
		$r = '';
		if(self::$even){$c='even';}else{$c='uneven';}
		self::$even = !self::$even;
		$r .= dv('previewMessage '.$c,'Message_'.$this->id);
		$from = new User($this->from);
		$r .= '<h2>'.lnk($from->fullName(),'exhibit/profile/'.$from->data['id']).'</h2>'.dv('italic meta contentBox').'('.$this->title.' '.prettyTime($this->created).')'.xdv();
		$r .= 
		$r .= xdv();
		return $r;
	}
	
	protected function titleLink(){
		$r = '';
		if(self::$even){$c='even';}else{$c='uneven';}
		self::$even = !self::$even;
		$from = new User($this->from);
		$r .= '<span class="from">'.$from->fullName().'</span>: ';
		$title = $this->title;
		if(empty($title)){$title = translate('no subject');}
		$r .= '"<span class="subject">'.$title.'</span>"';
		return lnk($r,'message/'.$this->id);
	}
	
	protected function gethtml(){
		die($this->content);
	}
	
	protected function conversation(){
		$c='message';
		if($this->unread == true){$c.=' unread';}
		$r = dv($c,'Message_'.$this->id);
		$from = new User($this->from);
		if($from->id==0){$email = $this->from_email;}else{$email = lnk($from->email,'account/activity/'.$from->id);}
		$r .= '<span class="subject"><span class="from small">'.$from->firstname.' ('.$email.')</span> '.$this->title.'</span>';
		if($this->format == 'text'){$r .= '<p class="content grey">'.frmt($this->content).'</p>';}
		else{$r .= '<iframe src="'.HOME.'message/'.$this->id.'/gethtml" width="640" height="400"></iframe>';}
		$r .= '<p class="received small">'.prettyTime($this->created).'</p>';
		$r .= xdv();
		if($_SESSION['uid'] == $this->uid){$this->unread = false;$this->update();}
		return $r;
	}
	
	protected function fullView(){
		return self::directory($this->id);
		if(App::$user->data['id'] == $this->uid){$this->unread = false;}
		$this->update();
		$r = '';
		$r .= dv('fullMessage','Message_'.$this->id);
		$from = new User($this->from);
		$r .= '<span class="from">'.lnk($from->fullName(),'exhibit/profile/'.$from->data['id']).'</span>, ';
		$r .= '<span class="subject">'.$this->title.'</span>: ';
		$r .= '<span class="received">'.prettyTime($this->created).' ('.date('d/m',$this->created).')'.'</span>';
		$r .= '<p class="content">'.nl2br($this->content).'</p>';
		$r .= xdv();
		return $r;
	}
}
?>