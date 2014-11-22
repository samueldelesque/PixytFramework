<?php
class Feedback extends Object{
	public $uid;
	public $proprietor;
	public $objectType;
	public $objectId;
	public $fbid;
	public $type;
	public $seen;
	public $content;
	
	protected static function publicFunctions(){
		return array(
			'fullView'=>true,
			'preview'=>true,
			'story'=>true,
			'activity'=>true,
		);
	}
	
	protected static function ownerFunctions(){
		return array(
			'edit'=>true,
			'editPreview'=>true,
		);
	}

	public function descriptor(){
		return array (
			'id'=>'int',
			'uid'=>'int',
			'proprietor'=>'int',
			'objectType'=>'string',
			'objectId'=>'int',
			'fbid'=>'int',
			'type'=>'int',
			'seen'=>'int',
			'content'=>'string',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		);
	}
	
	public function validateData($n,$v){
		switch ($n){
			case 'id':
			case 'uid':
			case 'created':
			case 'modified':
				return true;
			break;
			
			case 'objectType':
				$this->$n = intval($v);
				return true;
			break;
			
			case 'objectId':
				$this->$n = intval($v);
				return true;
			break;
			
			case 'fbid':
				$this->$n = intval($v);
				return true;
			break;
			
			case 'type':
				$this->$n=$v;
				return true;
			break;
			
			case 'seen':
				$this->$n = true;
				return true;
			break;
			
			case 'content':
				if($this->type==1 && empty($v)){
					Msg::notify(translate('Comment cannot be empty!'));
					return false;
				}
				if(strlen($v) > 255){
					Msg::notify(translate('Comment may not exceed 255 chars.'));
					return false;
				}
				else{
					$this->$n = htmlspecialchars(urldecode($v));
					return true;
				}
			break;
		}
		return false;
	}
	
	public function reviewBeforeInsert(){
		if($this->isUnique()){return true;}
		Msg::notify(translate('A similar feedback already exists. An error might have occured during the request.'));
		return false;
	}
	
	public function isUnique(){
		return !App::$db->exists('Feedback',array('objectType'=>$this->objectType,'objectId'=>$this->objectId,'uid'=>$this->uid,'content'=>$this->content));
	}
	
	public function className($plural = false){
		$r = $this->type;
		
		if($plural){
			$r .= 's';
		}
		return $r;
	}
	
	
	//DISPLAY MODES
	protected static function addComment($objectType,$objectId=-1){
		$r = '';
		$form = new Form('Feedback',NULL,true,array('ajax'=>true,'class'=>'full'));
		$r .= $form->head;
		$r .= $form->input('type',array('type'=>'hidden','value'=>1));
		$r .= $form->input('objectType',array('type'=>'hidden','value'=>$objectType));
		$r .= $form->input('objectId',array('type'=>'hidden','value'=>$objectId));
		$r .= $form->textarea('content');
		$r .= $form->button('submit');
		return $r;
	}
	
	protected function writePromoted(){
		$form = new Form('Feedback',$this->id,true,array('ajax'=>true));
		$form->content('textarea',translate('write something about this'),translate('promote'));
		$form->{translate('post')}('submit');
		return $form->returnContent();
	}
	
	protected function activity(){
		$r = '';
		$user = new User($this->uid);
		if($this->isOwner() && !$this->seen){$this->seen=true;$this->update();}
		$r .= dv($this->type.'_feedback','Feedback_'.$this->id.'_activity');
		$r .= dv('header').lnk($user->profilePicture('textsize'),'user/'.$this->uid).' '.$user->fullName('link').' ';
		switch($this->type){
			case 'comment':
				$r .= '<img src="'.HOME.'img/comment.png" class="actionIcon" width="16" height="16" title="'.translate('commented').'" alt="'.translate('commented').'"/>';
				if($this->isOwner()){
					$r .= lnk('<img src="'.HOME.'img/delete-grey.png" title="'.translate('delete').'" alt="'.translate('delete').'" width="16" height="16"/>','#',array('delete[Feedback]['.$this->id.']'=>true),array('title'=>translate('Delete this comment.'),'class'=>'delete','data-gethtml'=>'false'));
				}
				$r .= '<p class="content">'.frmt($this->content).'</p>';
			break;
			
			case 'heart':
				$r .= '<img src="'.HOME.'img/heart-red-16.png" class="actionIcon" width="16" height="16" title="'.translate('liked').'" alt="'.translate('liked').'"/> ';
			break;
			
			case 'rating':
				if(intval($this->content) < 6){return;}
				$r .= dv('padded').dv('rating').'<span class="active" width="'.($this->content*15).'px"></span>'.xdv().xdv();
			break;
			
			case 'promote':
				$r .= '<img src="'.HOME.'img/promote75-blue.png" class="actionIcon" width="16" height="16" title="'.translate('promoted').'" alt="'.translate('promoted').'"/>';
				$r .= '<p>'.frmt($this->content).'</p>';
			break;
			
			case 'validate':
				$r .= '<img src="'.HOME.'img/validate40.png" class="actionIcon" width="16" height="16" title="'.translate('vlidated').'" alt="'.translate('validated').'"/> ';
			break;
			
			default:
				return;
			break;
		}
		$r .= xdv();
		$r .= dv('content');
		switch($this->objectType){
			case 'Photo':
				$p = new Photo($this->objectId);
				$r .= lnk($p->img('medium'),'photo/'.$this->objectId);
			break;
			
			case 'Stack':
				$s = new Stack($this->objectId);
				$r .= lnk($s->photoCover(8,'smallsquare'),'stack/'.$this->objectId);
			break;
			
			case 'User':
				$p = new User($this->objectId);
				$r .= $p->fullName('link');
			break;
			
			default:
				$r .= Object::$objectTypes[$this->objectType].' #'.$this->objectId;
			break;
		}
		$r .= xdv();
		$r .= '<p class="time" title="'.date('r',$this->created).'">'.lnk(prettyTime($this->created),'search/'.urlencode(date('d F Y',$this->created))).'</p>'.xdv();
		return $r;
	}
	
	protected function story(){
		$r = '';
		if($this->proprietor==App::$user->id && !$this->seen){$this->seen=true;$this->update();}
		$r .= dv('Feedback '.$this->type,'Feedback_'.$this->id);
		$user = new User($this->uid);
		$r .= dv('content').lnk($user->profilePicture('textsize'),'user/'.$this->uid).' '.$user->fullName('link');
		switch($this->type){
			case 'comment':
				$r .= '<img src="'.HOME.'img/comment.png" class="actionIcon" width="16" height="16" title="'.translate('commented').'" alt="'.translate('commented').'"/>';
				if($this->isOwner()){
					$r .= lnk('<img src="'.HOME.'img/delete-grey.png" title="'.translate('delete').'" alt="'.translate('delete').'" width="16" height="16"/>','#',array('delete[Feedback]['.$this->id.']'=>true),array('title'=>translate('Delete this comment.'),'class'=>'delete','data-gethtml'=>'false'));
				}
				$r .= $this->content;
			break;
			
			case 'heart':
				$r .= '<img src="'.HOME.'img/heart17.png" class="actionIcon" width="17" height="17" title="'.translate('liked this').' '.$this->objectType.'" alt="'.translate('liked this').' '.$this->objectType.'"/>';
			break;
			
			case 'promote':
				$r .= '<img src="'.HOME.'img/promote75-blue.png" class="actionIcon" width="16" height="16" title="'.translate('promoted').'" alt="'.translate('promoted').'"/>';
				$r .= frmt($this->content);
			break;
			
			default:
				return;
			break;
		}
		$r .= '<span class="time right" title="'.date('r',$this->created).'">'.lnk(prettyTime($this->created),'search/'.urlencode(date('d F Y',$this->created))).'</span>'.xdv().'<br class="clearfloat"/>'.xdv();
		return $r;
	}
	
	
	//PUBLIC FUNCTIONS
	
	//OBJECT RELATED FUNCTIONS
	protected function postDelete($force = false){
		if(IS_AJAX){
			res('script','$("#Feedback_'.$this->id.'").fadeOut();');
			if($this->type==2){
				$title = translate('Add this to your favorites');
				$params = array('insert[Feedback][type]'=>'2','insert[Feedback][objectType]'=>$this->objectType,'insert[Feedback][objectId]'=>$this->objectId);
				$action = lnk('','#',$params,array(),true);
				res('script','$("#'.$this->objectType.'_'.$this->objectId.'_heart_btn").attr("href","'.$action.'");$("#'.$this->objectType.'_'.$this->objectId.'_heart_btn").attr("title","'.$title.'");$("#'.$this->objectType.'_'.$this->objectId.'_heart_img").attr("src",HOME+"img/share/heart-n.png");activate($("#'.$this->objectType.'_'.$this->objectId.'_heart_btn"));');
			}
			elseif($this->type==5){
				$title = translate('Promote this');
				$params = array('insert[Feedback][type]'=>'5','insert[Feedback][objectId]'=>$this->objectId,'insert[Feedback][objectType]'=>$this->objectType);
				$action = lnk('','#',$params,array(),true);
				res('script','$("#'.$this->objectType.'_'.$this->objectId.'_promote_btn").attr("href","'.$action.'");$("#'.$this->objectType.'_'.$this->objectId.'_promote_btn").attr("title","'.$title.'");$("#'.$this->objectType.'_'.$this->objectId.'_promote_img").attr("src",HOME+"img/share/promote-n.png");activate($("#'.$this->objectType.'_'.$this->objectId.'_promote_btn"));');
			}
		}
		return true;
	}
	
	protected function postInsert(){
		$object = new $this->objectType($this->objectId);
		$this->proprietor = $object->uid;
		$this->update();
		
		$owner = new User($this->proprietor);
		$user = new User($this->uid);
		$msg = '';
		switch($this->type){
			case 'comment':
				$msg = $user->fullName('link').' '.translate('just commented on your').' '.lnk($ol,$ol.'/'.$this->objectId,array('from'=>'email'));
			break;
			
			case 'heart':
				$msg = $user->fullName('link').' '.translate('just liked your').' '.lnk($ol,$ol.'/'.$this->objectId,array('from'=>'email'));
			break;
			
			case 'rating':
				if(intval($this->content) < 6){$this->seen=1;$this->update(true);return true;}
				$msg = translate('A member of the jury (or agency) rated this highly:');
			break;
			
			case 'promote':
				$msg = $user->fullName('link').' '.translate('just promoted your').' '.lnk($ol,$ol.'/'.$this->objectId,array('from'=>'email'));
				//$t = new Tumblr('contact@samueldelesque.com','KoreTumblr','lightarchitect');
			break;
			
			case 'share':
				$msg = $user->fullName('link').' '.translate('just shared your').' '.lnk($ol,$ol.'/'.$this->objectId,array('from'=>'email'));
			break;
			
			case 'validate':
				$msg = translate('Congratulations, your account has been validated! Your photos will now gain more visibility to the public, and you have been granted an additional 150MB of space to upload your great photos.');
			break;
			
			default:
				return true;
			break;
		}
		switch($this->objectType){
			case 'Photo':
				$msg .= '<br/><br/>'.lnk($object->img('medium'),$ol.'/'.$this->objectId,array('from'=>'email'));
			break;
			
			case 'User':
				$msg .= '<br/><br/>'.lnk($object->picture('text').' '.$object->fullName('full').' <img src="'.HOME.'img/validate-22-blue.png" alt="Validated"/>',$ol.'/'.$this->objectId,array('from'=>'email'));
			break;
			
			case 'Stack':
				$table = new Table();
				$c = array();
				$h=300;
				$t = count($object->children());
				$w = round(735/$t);
				foreach($object->children() as $p){
					if($p->className == 'Photo'){
						if(!file_exists(ROOT.'cdn/'.$w.'x'.$h.'/'.$p->id.'.jpg')){$p->mkthumb($w,$h);}
						$c[]=lnk('<img src="http://cdn.pixyt.com/'.$w.'x'.$h.'/'.$p->id.'.jpg"/>','photo/'.$p->id,array('from'=>'email'));
					}
				}
				$table->addLine($c);
				$msg .= '<br/><br/>'.$table->returnContent();
			break;
		}
		$owner->notify($msg,$this->type);
		
		if(IS_AJAX){
			res('script','$("#'.$this->objectType.'_'.$this->objectId.'_comments").append("'.addslashes($this->display('story')).'");activate($("#Feedback_'.$this->id.'"));');
			if($this->type==2){
				$title = translate('Remove this from your favorites');
				$params = array('delete[Feedback]['.$this->id.']'=>'1');
				$action = lnk('','#',$params,array(),true);
				res('script','$("#'.$this->objectType.'_'.$this->objectId.'_heart_btn").attr("href","'.$action.'");$("#'.$this->objectType.'_'.$this->objectId.'_heart_btn").attr("title","'.$title.'");$("#'.$this->objectType.'_'.$this->objectId.'_heart_img").attr("src",HOME+"img/share/heart-y.png");activate($("#'.$this->objectType.'_'.$this->objectId.'_heart_btn"));');
			}
			elseif($this->type==5){
				$title = translate('Unpromote this');
				$params = array('delete[Feedback]['.$this->id.']'=>'1');
				$action = lnk('','#',$params,array(),true);//$("body").append("'.addslashes($this->writePromoted()).'");
				res('script','$("#'.$this->objectType.'_'.$this->objectId.'_promote_btn").attr("href","'.$action.'");$("#'.$this->objectType.'_'.$this->objectId.'_promote_btn").attr("title","'.$title.'");$("#'.$this->objectType.'_'.$this->objectId.'_promote_img").attr("src",HOME+"img/share/promote-y.png");activate($("#'.$this->objectType.'_'.$this->objectId.'_promote_btn"));');
			}
		}
		return true;
	}
	
	function postUpdate(){
		return true;
	}
}
?>