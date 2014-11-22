<?php
abstract class Object extends Interfaces{
	//Object data - identic to all objects
	public $id;
	public $deleted;
	public $created;
	public $modified;
	
	//Childs will be preloaded automatically Children should have an index on Child.parentId
	protected static $kids = array();
	
	//status vars
	protected $isDummy = false;
	protected $isOwner = false;
	protected $ownerColumn = 'uid';
	
	//this fields should not be sent to API ever
	public $private_fields = array();
	
	public function __construct($data=NULL){
		parent::__construct();

		//set owner column (default is uid)
		if($this->className == 'User'){$this->ownerColumn='id';}
		
		//creating a new object
		if(empty($data)){
			$this->isOwner = true;
		}
		else{
			if(!is_object($data)){
				//check the ID
				if(!is_numeric($data)){
					$id = $this->getObjectId($data);
				}
				else{
					$id = intval($data);
				}
				
				//set the ID
				$this->id = $id;
				
				//data is not provided
				$this->setData(App::get($this));
			}
			
			//fill the data (from descriptor whitelist)
			else{
				$this->setData($data);
			}
	
			//if I created this object
			if($this->{$this->ownerColumn} == $_SESSION['uid']){
				$this->isOwner = true;
			}
		}
		$this->construct();
	}
	
	public function idPrefix(){
		//set ingenious Html Ids for JS edit
		if($this->id!=0){return 'update_'.$this->className.'_'.$this->id;}
		else{return 'insert_'.$this->className.'_'.$this->id;}
	}
	
	private function setData($data){
		foreach($this->descriptor() as $key=>$type){
			if(isset($data->$key)){
				$this->$key = $data->$key;
			}
		}
	}
	
	public function data(){
		if(!$this->access())return new stdClass;
		$data = new stdClass;
		foreach($this->descriptor() as $n=>$type){
			if(!in_array($n,$this->private_fields)){
				$data->$n = ($this->$n==='0')?NULL:$this->$n;
			}
		}
		return $data;
	}
	
	public function isOwner(){
		if($this->{$this->ownerColumn} == App::$user->id){return true;}
		if($this->isOwner === true){return true;}
		
		if($this->className == 'Feedback' && $this->proprietor == App::$user->id){return true;}
		if($this->className == 'Transaction' && App::$user->hasAccess(2)){return true;}
		if($this->className == 'Question' && App::$user->access < 40){return true;}
		return false;
	}
	
	protected function loadParent(){
		//For Photo/Article/Song
		if($this->kid != 0){
			$stack = new Stack($this->kid);
			if(empty($this->customOrder)){
				$this->customOrder = count($stack->children());
			}
		}
	}
	
	public function access(){
		if($this->isDummy){return false;}
		if(isset($this->deleted) && $this->deleted != 0){return false;}
		if($this->isOwner()){return true;}
		if($this->className == 'Feedback'){return true;}
		if($this->className == 'Site'){return true;}
		if($this->className == 'Follower'){return true;}
		if($this->className == 'Question'){return true;}
		if($this->className == 'Transaction' && App::$user->hasAccess(2)){return true;}
		if($this->className == 'File' && App::$user->hasAccess(1)){return true;}
		if($this->className == 'File' && isset($_REQUEST['accessCode']) && $_REQUEST['accessCode'] == $this->accessCode){return true;}
		if(isset($this->access) && $this->access >= 2){return true;}
		if(isset($this->proprietor) && $this->proprietor == App::$user->id){return true;}
		if(isset($this->public) && $this->public == true){return true;}
		if($this->className == 'Message' && $this->from == App::$user->id){return true;}
		if(!isset($_SESSION['access'])){$_SESSION['access'] = array();}
		if(!isset($_SESSION['access'][$this->className])){$_SESSION['access'][$this->className] = array();}
		if(isset($_SESSION['access'][$this->className][$this->id])){return true;}
		if($this->className == 'Photo'){
			if(!isset($_SESSION['access']['Stack'])){$_SESSION['access']['Stack'] = array();}
			if(isset($_SESSION['access']['Stack'][$this->kid])){return true;}
			if($this->channel==6){return true;}
		}
		if($this->className == 'Stack'){
			if($this->access >= 3)return true;
		}
		return false;
	}
	
	public function increment($field,$value=1){
		App::$db->increment($this->className,$this->id,$field,$value);
	}
	
	public function getObjectId($name, $nameField=''){
		if(empty($name)){return 0;}
		if(empty($nameField)){
			$class = $this->className;
			switch ($class){
				case 'User':
					$nameField = 'email';
				break;
				
				case 'Site':
					$nameField = 'url';
				break;
				
				default:
					$nameField = 'title';
				break;
			}
		}
		return App::$db->exists($this->className,array($nameField=>$name));
	}
	
	
	public static function objectExists($type,$id,$idName='id',&$rid=0){return self::exists($type,$id,$idName,$rid);}
	public static function exists($type='',$id='',$idName='id',&$rid=0){
		if(empty($type)){return false;}
		if(empty($id)){return false;}
		$q = 'SELECT `id` FROM `'.$type.'` WHERE `'.$idName.'` = :'.$idName;
		if($r = App::$db->query($q,array($idName=>$id),false)){
			return $rid = $r->id;
		}
		return false;
	}
	
	public function className($plural = false){
		if($plural){
			return $this->className.'s';
		}
		else{
			return $this->className;
		}
	}
	
	private function load($id,$useCache = true){
		die('Object::load() function obsolete!');
		if($this->isDummy){return false;}
		if(isset(self::$preload[$this->className]) && is_array(self::$preload[$this->className])){
			if($useCache && isset(self::$preload[$this->className][(string)$id])){
				$this->data = self::$preload[$this->className][$id];
				return true;
			}
		}
		if(!isset(self::$preload[$this->className])){self::$preload[$this->className] = array();}
		$local = get_class_vars($this->className);
		if(self::$db->selectId($this->className,$id,'*',$rdata)){
			if(empty($rdata)){
				$this->error('Object not found',95);
				$this->isDummy = true;
				return false;
			}
			foreach($rdata as $name=>$value){
				if(!isset($local['descriptor'][$name])){
					//if(DEBUG){$this->error($this->className.'.'.$name.' should not exist.');}
				}
				else{
					if($local['descriptor'][$name] == self::OBJ){$value=objectToArray(json_decode($value));}
					if($local['descriptor'][$name] == self::VLIST){if(empty($value)){$value=array();}else{$value=explode(',',$value);}}
					$this->data[$name] = $value;
				}
			}
			self::$preload[$this->className][$id] = $this->data;
			return true;
		}
		else{
			$this->error('Failed to load '.$this->className.'::'.$id,20);
			$this->isDummy = true;
			return false;
		}	
		return false;
	}
	
	public function validateData($n,$v){
		return false;
	}
	
	function objectEdited(){
		if(!isset(self::$preload[$this->className][$this->id])){return false;}
		foreach(self::$preload[$this->className][$this->id] as $k=>$v){
			if($this->data[$k] != $v){return true;}
		}
		return false;
	}
	
	public function reviewBeforeInsert(){
		return true;
	}
	
	public function insert($force = false){
		//No dummies allowed
		if($this->isDummy){$this->error('Cannot insert dummies',80);return false;}
		
		//object must be new
		if($this->id != 0){$this->error('Trying to reinsert existing object',80);return false;}
		
		if(!$this->reviewBeforeInsert()){
			$this->error('Failed to validate data');
			return false;
		}
		
		// set data that may not be chosen by user
		if(!$force){
			if($this->className == 'Message'){$this->from = App::$user->id;$this->unread = true;}
			else{$this->{$this->ownerColumn} = App::$user->id;}
			$this->created = time();
		}
		else{
			if(!isset($this->uid)){
				$this->error('Forcing insert with missing arguments!',95);
				return false;
			}
		}
		/*
		if($this->className == 'User' && !$this->termsAccepted){
			Msg::notify('Please accept the terms and conditions.');
			return false;
		}
		*/
		
		$this->id = intval(App::$db->insert($this));
		if($this->id>0){
			$this->isOwner = true;
			$this->isDummy = false;
			if($this->postInsert()){
				return $this->id;
			}
			else{
				$this->error($this->className.' Post insert function failed',50);
				return false;
			}
		}
	}
	
	protected function postInsert(){
		return true;
	}
	
	protected function postUpdate(){
		return true;
	}
	
	public function update($force = false){
		//No dummies allowed
		if($this->isDummy){$this->error('Cannot insert dummy',50);return;}
		
		//if new object, we cannot updated some ex iting one
		if(!$this->id){$this->error('Object cannot be updated as it doesnt exist yet',80);return false;}
	
		// set auto data
		if(!$force){
			if(!$this->isOwner()){$this->error('Trying to object third party object.');return false;}
			$this->modified = time();
		}

		if(App::$db->update($this)){
			return $this->postUpdate();
		}
		return false;
	}
	
	public function total($filter=''){
		if($data = self::$db->query('SELECT COUNT(*) as `count` FROM `'.$this->className.'` '.$filter,$t)){
			return $data->count;
		}
		return null;
	}
	
	public function content($mode = 4){
		if($this->isDummy){return '<h2 class="padder">'.$this->className.translate(' unavailable').'</h2>';}
		//if object should return HTML content
		if((int)$mode >= 2){
			//dummies cannot return content
			if($mode == 4){
				//declare returned string
				$r = '';
				
				$r .= '<div class="pageContent">';
				
				//get the actual content
				$r .= $this->{'mode'.$mode}();
				
				$r .= '</div>';
				
				return $r;
			}
		}
		//mode 3 = read data
		//mode 4 = rw data
		return $this->{'mode'.$mode}();
	}
	
	//data mode
	public function mode1(){
		return $this->data;
	}
	
	//table line
	public function mode2(){
		$fields = array();	
		return 'Not available.';
	}
	
	//preview (thumb)
	public function mode3(){
		return $this->preview();
	}
	
	//view (full page)
	public function mode4(){
		return $this->fullView();
	}
	
	public function getName(){
		switch($this->className){
			case 'User':
				return $this->fullName();
			break;
			
			default:
				if(empty($this->id)){return translate('new').' '.$this->className;}
				if(method_exists($this,'title')){return $this->title();}
				elseif(!empty($this->title)){return $this->title;}
				else{return $this->className.' #'.$this->id;}
			break;
		}
	}
	
	public function suggestion(){
		return dv('suggestion',$this->className.'_'.$this->id,'onclick="alert(\'Suggestions are not available yet\')"').$this->getName('full').xdv();
	}
	
	public function delete($force=false){
		if($this->isDummy){
			return true;
		}
		if($this->className == 'User'){
			$q = new Query();
			$results = array();
			$results[] = $q->update('Message')->set(array('deleted'=>time()))->where('uid',$this->id)->get();
			/*
			if(App::$user->access < 40 && App::$user->id !== $this->id){
				foreach(self::$objects as $o){
					$o = ucfirst($o);
					if($o != $this->className){
						self::$db->deleteData($o,$this->id,'uid',99999);
					}
				}
				$messages = new Collection('Message');
				$messages->from = $this->id;
				$messages->load(0,99999);
				foreach($messages->results as $msg){
					$msg->from = 0;
					$msg->update(true);
				}
				App::$db->deleteAll('Feedback',array('proprietor'=>$this->id),99999);
				if(self::$db->deleteData($this->className,$this->id)){
					$this->postDelete($force);
					res('script','$(".User_'.$this->id.'_tableLine").fadeOut();');
					return true;
				}
			}
			else{
				$this->hide();
			}
			*/
			return true;
		}
		else{
			if($force!==true && !$this->isOwner()){
				$this->error('Trying to delete non proprietary object.',70);
				return false;
			}
			if(self::$db->deleteData($this->className,$this->id)){
				$this->postDelete($force);
				return true;
			}
		}
		return false;
	}
	
	protected function postDelete($force=false){
		return true;
	}
	
	public function heartCounter(){
		return App::$db->count($this->className,array('id'=>$this->id));
	}
	
	
	//PUBLIC FUNCTIONS
	public function display($mode='fullview',$settings=array()){
		if(!method_exists($this,$mode)){Msg::addMsg(404);return false;}
		if(!$this->access()){Msg::addMsg(401);return false;}
		return call_user_func_array(array($this,$mode),$settings);
	}
	
	public function tags(){
		$r = '';
		if(isset($this->tags) && !empty($this->tags)){
			$r .= dv('','tags');
			$r .= '<p id="'.$this->className.'_'.$this->id.'_tags" class="tags">';
			$i=0;
			foreach($this->tags as $id=>$tag){
				if($i>7){break;}
				$r.= $this->tag($tag,$id);
				$i++;
			}
			$r .= '</p>'.xdv();
		}
		return $r;
	}
	
	public function tag($tag,$id=''){
		if(empty($tag)){return false;}	
		$r= ' <span class="tag" id="tag_'.$id.'">'.lnk($tag,'search/'.prettyUrl($tag),array(),array('title'=>translate('this photo was tagged').' '.$tag,'rel'=>'tag'));
		if($this->isOwner()){$r .= lnk('X','#',array('update['.$this->className.']['.$this->id.'][removetag]'=>$id),array('title'=>translate('Delete tag'),'class'=>'delete'));}
		$r.='</span>';
		return $r;
	}
	
	public function children(){
		switch($this->className){
			case 'Stack':
				$kids = array();
				$photos = new Collection('Photo');
				$photos->where('kid',$this->id);
				return $photos->get();
			break;
		}
	}

	public function next(){
		$items = array();
		if(isset(Site::$current)&&is_object(Site::$current)){
			$items = Site::$current->pageGetItems(Site::$current->cur['u']);
		}
		elseif($this->kid == 0){
			if(!isset(self::$unsorted[$this->uid])){return $this->id;}
			$items = self::$unsorted[$this->uid];
		}
		else{
			$stack = new Stack($this->kid);
			$elements = $stack->children();
			if(!is_array($elements)){return 0;}
			foreach($elements as $p){$items[]=array($p->className,$p->id);}
		}
		$pos=0;
		foreach($items as $o=>$obj){
			if($obj[0]==$this->className&&$obj[1]==$this->id){
				$pos = $o;
			}
		}
		if(isset($items[$pos+1])){
			return $items[$pos+1];
		}
		return reset($items);
	}
	
	public function previous(){
		$this->loadParent();
		$items = array();
		if(isset(Site::$current)&&is_object(Site::$current)){
			$items = Site::$current->pageGetItems(Site::$current->cur['u']);
		}
		elseif($this->kid == 0){
			if(!isset(self::$unsorted[$this->uid])){return $this->id;}
			$items = self::$unsorted[$this->uid];
		}
		else{
			$stack = new Stack($this->kid);
			$elements = $stack->children();
			if(!is_array($elements)){return 0;}
			foreach($elements as $p){$items[]=array($p->className,$p->id);}
		}
		$pos=0;
		foreach($items as $o=>$obj){
			if($obj[0]==$this->className&&$obj[1]==$this->id){
				$pos = $o;
			}
		}
		if(isset($items[$pos-1])){
			return $items[$pos-1];
		}
		return end($items);
	}
	
	public function heart(){
		if($_SESSION['uid'] == 0){return lnk('<img src="'.HOME.'img/share/heart-n.png" id="'.$this->className.'_'.$this->id.'_heart_img" alt="'.translate('Like this photo').'" title="'.translate('Like this photo').'"/>','login',array(),array('data-type'=>'popup','rel'=>'nofollow','class'=>'addheart socialBtn'));}
		if($this->isDummy){return false;}
		$r='';
		$heart = App::$db->exists('Feedback',array('objectType'=>$this->className,'objectId'=>$this->id,'uid'=>$_SESSION['uid']));
		if(!$heart){
			$c='n';
			$title = translate('Add this photo to your favorites');
			$param = array('insert[Feedback][type]'=>'2','insert[Feedback][objectType]'=>$this->className,'insert[Feedback][objectId]'=>$this->id);
		}
		else{
			$c='y';
			$title = translate('Remove this from your favorites');
			$param = array('delete[Feedback]['.$heart.']'=>'1');
		}
		$r .= lnk('<img src="'.HOME.'img/share/heart-'.$c.'.png" id="'.$this->className.'_'.$this->id.'_heart_img" alt="'.translate('Like this photo').'" title="'.translate('Like this photo').' ('.$this->heartCounter().' like this)"/>','#',$param,array('title'=>translate('Like this photo'),'data-gethtml'=>'false','id'=>$this->className.'_'.$this->id.'_heart_btn','class'=>'addheart socialBtn'));
		return $r;
	}
	
	public function socialFeedback(){
		if(App::$user->id == 0){return lnk('Please login to comment','login',array(),array('data-type'=>'popup','rel'=>'nofollow','class'=>'addheart socialBtn'));}
		if($this->isDummy){return false;}
		$r='';
		if($this->myHeart($id)){
			$c='liked';
			$title = translate('Remove this from your favorites');
			$param = array('delete[Feedback]['.$id.']'=>'1');
		}
		else{
			$c='like';
			$title = translate('Add this photo to your favorites');
			$param = array('insert[Feedback][type]'=>'heart','insert[Feedback][objectType]'=>$this->className,'insert[Feedback][objectId]'=>$this->id);
		}
		$r .= lnk('<img src="'.HOME.'img/like/'.$c.'.png" width="30" id="'.$this->className.'_'.$this->id.'_heart_img" alt="'.translate('Like this photo').'" title="'.translate('Like this photo').' ('.$this->heartCounter().' like this)"/>','#',$param,array('title'=>translate('Like this photo'),'data-gethtml'=>'false','id'=>$this->className.'_'.$this->id.'_heart_btn'));
		return $r;
	}

	public function promote(){
		if(!App::$user->hasAccess(4)){return;}
		
		$r='';
		if($id = $this->myPromote()){
			$c='y';
			$title = translate('Unpromote this photo');
			$param = array('delete[Feedback]['.$id.']'=>true);
		}
		else{
			$c='n';
			$title = translate('Promote this photo');
			$param = array('insert[Feedback][type]'=>'promote','insert[Feedback][objectType]'=>$this->className,'insert[Feedback][objectId]'=>$this->id);
		}
		
		$r .= lnk('<img src="'.HOME.'img/share/promote-'.$c.'.png" id="'.$this->className.'_'.$this->id.'_promote_img" alt="'.translate('Promote this photo').'" title="'.translate('Promote this photo').'"/>','#',$param,array('title'=>translate('Promote this photo'),'data-gethtml'=>'false','id'=>$this->className.'_'.$this->id.'_promote_btn','class'=>'socialBtn promoteBtn'));
		return $r;
	}
	
	public function construct(){return;}
	
	public function comments(){
		$r = '';
		$r .= dv('comments').dv('previous',$this->className.'_'.$this->id.'_comments');
		$col = new Collection('Feedback');
		$col->where(array('objectType'=>$this->className,'objectId'=>$this->id));
		$comments = $col->load();
		foreach($comments as $comment){
			$r .= $comment->story();
		}
		$r .= xdv();
		$r .= Feedback::addComment($this->className,$this->id);
		$r .= xdv();
		return $r;
	}
	
	public function heartsCounter(){
		return App::$db->count('Feedback',array('objectType'=>$this->className,'objectId'=>$this->id,'type'=>'heart'));
	}
	
	public function commentsCounter(){
		return App::$db->count('Feedback',array('objectType'=>$this->className,'objectId'=>$this->id,'type'=>'comment'));
	}
	
	public function searchQuery($x='',$owner=false){
		return false;
	}
}
?>