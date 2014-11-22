<?php
class Stack extends Object{
	protected static $kids = array('Photo','Song','Article');
	
	public $id;
	public $uid;
	public $title;
	public $genre;
	public $description;
	public $access = 3;
	public $accessCode;
	public $tags = array();
	public $rating;
	public $cover;
	public $created;
	public $modified;
	
	function __construct($o=NULL){
		parent::__construct($o);
	}
	
	
	protected static function publicFunctions(){
		return array(
			'fullView'=>true,
			'preview'=>true,
			'feed'=>true,
			'share'=>true,
			'contestPreview'=>true,
			'create'=>true,
		);
	}
	
	protected static function ownerFunctions(){
		return array(
			'edit'=>true,
			'editPreview'=>true,
			'params'=>true,
			'publish'=>true,
		);	
	}
		
	public function descriptor(){
		return array(
			'id'=>'int',
			'uid'=>'int',
			'title'=>'string',
			'genre'=>'string',
			'description'=>'string',
			'access'=>'int',			//0=>not set, 1=>private, 2=>not listed, 3=>public
			'accessCode'=>'string',
			'tags'=>'object',
			'rating'=>'int',
			'cover'=>'int',
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
			case 'deleted':
			case 'accessCode':
			case 'rating':
			case 'cover':
				return true;
			break;
			
			case 'customOrder':
				$list = explode(',',$v);
				foreach($list as $i=>$item){
					$el = explode(':',$item);
					$ob = explode('_',$el[0]);
					$object = ucfirst($ob[0]);
					if(Object::objectExists($object,$ob[1])){
						switch($object){
							case 'Photo':
								$photo = new Photo(intval($ob[1]));
								$photo->customOrder = $el[1];
								$photo->update();
							break;
							
							case 'Article':
								$article = new Article(intval($ob[1]));
								$article->customOrder = $el[1];
								$article->update();
							break;
							
							case 'Song':
								$song = new Song(intval($ob[1]));
								$song->customOrder = $el[1];
								$song->update();
							break;
							
							default:
								$this->error('Failed to query ['.$v.']',96);
								return false;
							break;
						}
					}
				}
				return true;
			break;
			
			case 'url':
				if($this->isUniqueUrl($v)){$this->$n=$v;return true;}
				else{Msg::addMsg(translate('That url is already taken!'));}
			break;
			
			case 'title':
				if(empty($v)){Msg::notify('Title cannot be empty');}
				elseif(strlen($v) < 255){
					if($this->isUniqueTitle($v)){
						$this->$n = $v;
						return true;
					}
					else{
						Msg::addMsg(translate('A stack with that name already exists!'));
						return false;
					}
				}
				else{
					Msg::addMsg('Title too long!');
				}
			break;
			
			case 'removetag':
				if(!isset($this->tags[$v])){Msg::notify(t('tag not found!'));return false;}
				else{
					unset($this->tags[$v]);
					if(IS_AJAX){
						res('script','$("#tag_'.$v.'").fadeOut();activate($("#tags"));');
						if(empty($this->tags)){res('script','$("#tags").html="'.t('No tags yet.').'"');}
					}
				}
				return true;
			break;
			
			case 'tags':
			case 'tag':
				if(!is_array($v))$tags = preg_split('/[,;]/',$v);
				else $tags = $v;
				$append = '';
				foreach($tags as $tag){
					$tag = sanitize($tag);
					$id = md5($tag);
					if(count($this->tags) > 7){
						Msg::notify(t('Max 7 tags are allowed'));
						break;
					}
					if(!empty($tag)){
						if(!isset($this->tags[$id])){
							if(strlen($tag) > 40){
								Msg::notify(t('Tags may not exceed 40 chars'));
							}
							else{
								$this->tags[$id] = $tag;
								$append .= $this->tag($tag,$id);
							}
						}
						else{
							Msg::notify($tag.' '.t('already exists.'));
						}
					}
				}
				res('script','$("#'.$this->className.'_'.$this->id.'_tags").append("'.addslashes($append).'");activate($("#'.$this->className.'_'.$this->id.'_tags"));$(".xbox").slideUp();');
				return true;
			break;
			
			case 'genre':
				if(strlen($v) < 255){
					$this->$n = $v;
					foreach($this->children() as $obj){
						$obj->genre=$v;
						$obj->update();
					}
					res('script','$("#form_Stack_'.$this->id.'.inline").fadeOut();');
					return true;
				}
				Msg::addMsg(translate('Genre must be under 255 chars.'));
			break;

			case 'access':
				if(empty($v) || !is_numeric($v)){$v = 0;}
				$this->$n = (int)$v;
				foreach($this->children() as $obj){
					$obj->access=(int)$v;
					$obj->update(true);
				}
				return true;
			break;
			
			case 'description':
				if(strlen($v) < 900){
					$this->$n = $v;
					return true;
				}
				Msg::addMsg(translate('Description must be under 900 chars.'));
			break;
		}
		return false;
	}
	
	public function folder(){
		return date('Y',$this->created).'/'.$this->genre.'/'.$this->title;
	}
	
	public function isUniqueTitle($t){
		if(!App::$db->exists(
			'Stack',
			array(
				'uid'=>$this->uid,
				'id !='=>$this->id,
				'title'=>$t,
			)
		)){
			return true;
		}
		return false;
	}
	
	public function views(){
		$i = 0;
		foreach($this->children() as $p){
			if($p->className == 'Photo'){$i += (int)$p->views;}
			if($p->className == 'Song'){$i += (int)$p->plays;}
		}
		return $i;
	}
	
	public function contentHearts(){
		$i = 0;
		foreach($this->children() as $obj){
			$i += (int)$obj->heartsCounter();
		}
		return $i;
	}
	
	//DISPLAY MODES
	public function share($medium='facebook'){
		$txt = $this->title.' '.translate('by').' '.User::$users[$this->uid]->fullName('full').' - on Pixyt';
		return $medium(HOME.'stack/'.$this->id,$txt,'');
	}
	
	protected function params(){
		$r = '';
		$r .= dv('d4x3 col');
		$form = new Form('Stack',$this->id,true,array('ajax'=>true));
		$form->description('textarea',$this->description,translate('description'));
		$form->genre('input',$this->genre,translate('genre'));
		$form->tags('input','',translate('tags'));
		$form->{translate('save')}('submit');
		$form->write('<p>'.$this->tags().'</p>');
		$r .= $form->returnContent();
		$r .= xdv().dv('d4 col');
		$r .= dv().swtch('access',array(translate('public')=>3,translate('private')=>1),$this->access,'Stack',$this->id).xdv();//translate('not listed')=>2,
		$r .= dv();
		$r .= '<br/>';
		$r .= dv('padded').lnk(translate('delete stack'),'stack/'.$this->id.'/edit',array('do'=>'delete'),array('data-type'=>'confirm','data-matter'=>translate('Are you sure you want to delete the whole stack, and all the images it contains?'),'class'=>'btn grey','ajax'=>true)).xdv();
		$r .= xdv();
		return $r;
	}
	
	protected function publish(){
		if(!$this->isOwner){return false;}
		$r = '';
		$r .= dv('padded');
		if(empty($this->accessCode)){$this->accessCode = randStr();$this->update();}
		$r .= '<p>'.translate('Share this with your friends using the following link:').'</p>';
		$r .= '<p class="blckbgd blue padded scroll">http://pixyt.com/stack/'.$this->id.'?accessId='.$this->id.'&accessType=1&accessCode='.$this->accessCode.'</textarea></form></p>';
//		$r .= '<p class="blckbgd blue padded">http://pixyt.com/download.php?stack='.$this->id.'&accessCode='.$this->accessCode.'</p>';
		/*
		if($this->access<3){
			$r .= lnk('publish to pixyt','#cur',array('update[Stack]['.$this->id.'][access]'=>3),array('class'=>'btn'));
		}
		else{
			$r .= lnk('Remove from pixyt','#cur',array('update[Stack]['.$this->id.'][access]'=>2),array('class'=>'btn'));
		}
		*/
		$r .= xdv();
		$r .= dv('padded');
		$r .= '<p>'.translate('Or share it on your website:').'</p>';
		if(count(App::$user->sites()) == 0){
			$r .= '<p>'.translate('You have no websites yet!').' '.lnk('create one','site/create','',array('class'=>'btn')).'</p>';
		}
		else{
			foreach(App::$user->sites() as $site){
//				$r .= dv('site','Site_'.$site->id).'<span class="big">Publish to '.$site->url.':</span>';
				$form = new Form('Site',$site->id,'ajax',array('ajax'=>true,'class'=>'full','data'=>array('method'=>'stack_publish','content'=>'Stack_'.$this->id)));
				$form->write('<p>'.translate('Publish to').' '.$site->url.'</p>');
				$o=array('add'=>'new');
				foreach($site->content as $id=>$page){
					if($page['x']==2&&is_array($page['c'])){
						$o[$page['u']]=$page['t'];
					}
					elseif($page['x']==1){
						$o[$page['u']]=$page['t'];
						if(is_array($page['c'])){
							foreach($page['c'] as $sub_page){
								if($sub_page['x'] == 2)$o[$sub_page['u']]='--'.$sub_page['t'];
							}
						}
					}
				}
				$form->{'publish'}('select',$o,false);
				//$form->q('radio',array('0'=>'All',-1=>'★★★★★',5=>'5',10=>'10',15=>'15'),translate('how many'));
				//$form->orderBy('radio',array('created'=>'Date added','rating'=>'Rating','dateShot'=>'Date shot'),translate('Order by'));
				$form->{translate('Publish')}('submit');
				$r .= dv('entry');
				$r .= $form->returnContent();
				$r.= xdv();
			}
		}
		$r .= xdv();
		return $r;
	}
	
	protected function diskspace(){
		$space = 0;
		foreach($this->children() as $obj){
			$file = new File($obj->fileid);
			$space += (int)$file->size;
		}
		return $space;
	}
	
	protected function edit(){
		$r='';
		$maxcols=floor(WINDOWW/240)-1;
		$w = ($maxcols*240);
		if(isset($_REQUEST['do'])&&$_REQUEST['do']=='delete'){
			foreach($this->children() as $obj){
				$obj->delete();
			}
			$this->delete();
			if(!empty($this->prid)){$url = HOME.'project/'.$this->prid.'/edit';}else{$url = HOME.'account/organize';}
			
			if(!IS_AJAX){header('Location: '.$url);}
			else{res('script','document.location.href ="'.$url.'";');}
			
			T::$page['title'] = translate('Photo deleted');
			return dv('padder info').'<h2>'.translate('Your stack was deleted.').'</h2>'.xdv();
		}
		T::$page['title'] = $this->title;
		
		if(isset($_REQUEST['help']) || (count($this->children()) == 0 && !isset(App::$user->data['settings']['tip']['edit-stack-top']))){
			$close = lnk(translate('got it'),'#cur',array('update[User]['.App::$user->id.'][dismissTip]'=>'edit-stack-top'),array('class'=>'btn right small grey'));
			//$r .= dv('help','tip_edit-stack-top').$close.'<h2>'.translate('A stack is a collection of content').'</h2>'.'<h3>'.translate('Start adding files by dropping them below').' [jpg|gif|png|mp3] </h3>'.'<br class="clearfloat"/>'.xdv();
			Msg::notify('<h2>'.translate('A stack is a collection of content').'</h2>'.'<h3>'.translate('Start adding files by dropping them below').' [jpg|gif|png|mp3] </h3>',0);
		}
		
		$sidebar = dv('navigation');
		$sidebar .= dv('tab');
		$sidebar .= dv('denomination').translate('photos').xdv();
		$sidebar .= dv('el softlink','','data-url="account/organize"').'<span class="bold">'.translate('all').'</span>'.xdv();
		$sidebar .= dv('el softlink','','data-url="account/organize?genre='.urlencode($this->genre).'"').'<span class="bold">'.$this->genre.'</span>'.xdv();
		$sidebar .= xdv();
		$sidebar .= dv('tab');
		$sidebar .= dv('denomination').translate('info').xdv();
		$sidebar .= dv('el').'<ol class="squares"><li>'.lnk('<img src="'.HOME.'/img/site_editor/view.png" height="30" alt="'.translate('Stack views').'" title="'.translate('Stack views').'"/> '.prettyNumber($this->views(),1),'stack/'.$this->id,array(),array('class'=>'grey')).'</li>';
		$sidebar .= '<li>'.lnk('<img src="'.HOME.'/img/site_editor/heart.png" height="30" alt="'.translate('Stack hearts').'" title="'.translate('Stack hearts').'"/> '.prettyNumber($this->heartsCounter(),1),'stack/'.$this->id,array(),array('class'=>'grey')).'</li>';
		$sidebar .= '<li>'.lnk('<img src="'.HOME.'/img/site_editor/comment.png" height="30" alt="'.translate('Stack comments').'" title="'.translate('Photo comments').'"/> '.prettyNumber($this->commentsCounter(),1),'stack/'.$this->id,array(),array('class'=>'grey')).'</li>';
		$sidebar .= '<li>'.prettyBytes($this->diskspace()).'</li></ol>'.xdv();
		$sidebar .= xdv();
		$sidebar .= xdv();
		$this->sidebar($sidebar);
		
		$head = '';
		$head .= dv('toolbox').lnk('<img src="/img/organize-grey.png">','account/organize',array('s'=>$_REQUEST['s']),array('class'=>'back')).xdv();
		$head .= dv('toolbox').'<h3><span id="Stack_'.$this->id.'_title" class="editable title">'.$this->title.'</span></h3>'.xdv();
		$head .= dv('toolbox').Photo::uploadBtn(1,$this->id,0,'Stack_'.$this->id.'_content').xdv();
		/*
		$form = new Form('Stack',$this->id,true,array('ajax'=>true,'class'=>'inline'));
		$form->title('input',$this->title,false);
		$form->{translate('save')}('submit');
		$head .= dv('toolbox').$form->returnContent().xdv();
		*/
		$head .= dv('toolbox right').lnk('<img src="/img/param40.png"/>','stack/'.$this->id.'/params',array(),array('data-containerid'=>'params','title'=>translate('Edit settings'),'data-type'=>'popup')).xdv();
		$head .= dv('toolbox right').lnk('<img src="/img/share40.png"/>','stack/'.$this->id.'/publish',array(),array('data-type'=>'popup')).xdv();
		$head .= dv('toolbox right').lnk('<img src="/img/addarticle40.png"/>','article/create',array('kid'=>$this->id),array('data-containerid'=>'Stack_'.$this->id.'_content','ajax'=>true,'title'=>translate('Write an article'))).xdv();
		if(empty($this->genre) || $this->genre == 'other'){
			$form = new Form('Stack',$this->id,true,array('ajax'=>true,'class'=>'inline'));
			$form->genre('input','Which genre is this?',false,'clear');
			$form->{translate('save')}('submit');
//			$head .= dv('toolbox right').$form->returnContent().xdv();
			T::$jsfoot[] = 'callAction(urldecode("'.urlencode(dv('centerText').$form->returnContent().xdv()).'"));';
		}
		$head .= '<br class="clearfloat"/>';
		$this->header($head);
		T::js('
$(function(){
	$(".sortable").sortable({
		update:function(){
			var list = [];
			var i = 0;
			$(this).find(".file").each(function(){
				list.push(this.id+":"+i);
				$(this).attr("data-customOrder",i);
				i++;
			});
			var self = $(this);
			query(HOME+"ajax",{"gethtml":false,"datatype":"json","update[Stack]['.$this->id.'][customOrder]":list.join(",")});
		}
	});
	$(".sortable").disableSelection();
});
$("body").unbind("keydown").bind("keydown",function(e) {
	if(e.keyCode == 27){
		$(".organize").slideUp(200,function(){window.location.href=HOME+"account/organize";});
	}
});
');
		//.dv('slideToggle','params','style="display:none"').xdv().dv('','publish','style="display:none"').xdv()
		if(count($this->children()) == 0){$c=' empty';}else{$c='';}
		$r .= dv('uploadBox'.$c).dv('addPhotos sortable','Stack_'.$this->id.'_content');
		
		foreach($this->children() as $index=>$obj){
			$r .= $obj->display('editPreview');
		}
		$r .= xdv().'<br class="clearfloat"/>'.xdv();
		return $r;
	}
	
	protected function fullView(){
		$r = '';
		$r .= dv('Stack d900 center');
		T::$page['title'] = $this->title.' '.translate('by').' '.User::$users[$this->uid]->fullName();
		T::$page['author'] = User::$users[$this->uid]->fullName('full');
		if($this->isDummy){return dv('stack','Stack_'.$this->id).'<h1>'.translate('Not available').'</h1>'.xdv();}
		$r .= dv('stack','Stack_'.$this->id);
		//IF BROWSING ONE ALBUM
		$r .= dv('post').'<h2>'.lnk(User::$users[$this->uid]->profilePicture('smallsquare'),'user/'.$this->uid).' '.User::$users[$this->uid]->fullName('link').'</h2>';
		if(!empty($this->title)){
			T::$page['title'] = $this->title;
			$r .= '<h1>'.$this->title.'</h1>';
		}
		else{
			$r .= '<h1 class="small lightgrey faded">'.translate('Stack').' #'.$this->id.'</h1>';
			T::$page['title'] = translate('Stack by').' '.User::$users[$this->uid]->fullName('full');
		}
		if(!empty($this->description)){
			$desc = '<h3>'.frmt($this->description).'</h3>';
			T::$page['description'] = $this->description;
		}
		else{
			$desc='';
			$i=0;
			foreach($this->children() as $item){if($item->className=='Photo')$i++;}
			T::$page['description'] = translate('Stack containing').' '.$i.' '.translate('photos');
		}
		$r .= $desc.xdv();
		$maxcols=floor(WINDOWW/240)-1;
		$r .= dv('center d'.strval((240*$maxcols)).'').dv('photos');
		foreach($this->children() as $index=>$obj){
			$r .= $obj->display('preview');
		}
		$r .= xdv().'<br class="clearfloat"/>'.xdv();
		
		$r .= dv('meta');
		$r .= $this->share('facebook');
		$r .= $this->share('tweet');
		$r .= $this->share('pin');
		$r .= $this->heart();
		$r .= $this->promote();
		if($this->isOwner){
			$r .= lnk('<img src="'.HOME.'img/share/edit.png"/>','stack/'.$this->id.'/edit',array(),array('title'=>translate('Edit'),'id'=>'edit','class'=>'socialBtn edit'));
		}
		$r .= $this->tags();
		if(App::$user->id==1){
		//	$r .= $this->rate();
		}
		$r .= '<span class="time" title="'.date('r',$this->created).'">'.lnk(prettyTime($this->created),'search/'.urlencode(date('d F Y',$this->created))).'</span>'.xdv();
		$r .= $this->comments();
		$r .= xdv();
		$r .= xdv();
		
		return $r;
	}
	
	public function selectable($opt='',$tools=''){
		$r = '';
		$r .= dv('preview previewBox selectable','Stack_'.$this->id.'_selectable','data-className="Stack" data-id="'.$this->id.'"').$this->photoCover(6,'smallsquare',true).dv('tools').$tools.xdv().xdv();
		return $r;
	}
	
	protected function preview(){
		if($this->isDummy){return '<div class="previewBox"><h3 class="title">'.translate('Serie unavailable').'</h3></div>';}
		return dv('previewBox preview stack').dv('head').translate('Serie').': '.lnk($this->title,'stack/'.$this->id).'<span class="right">'.$this->heart().'</span>'.xdv().$this->photoCover(6,'text').xdv();
	}
	
	
	protected function editPreview(){
		$r = dv('folder','Stack_'.$this->id.'_editPreview').dv('cover').lnk('<img src="'.$this->cover().'" width="80" height="80" alt="'.$this->title.'" title="'.$this->title.'"/>','stack/'.$this->id.'/edit').xdv();
		$r .= dv('title').shorten($this->title,14).xdv();
//		$r .= dv('tools').lnk('<img src="/img/trashbin.png" height="20px" alt="delete"/>','stack/'.$this->id.'/edit',array('do'=>'delete'),array('class'=>'confirm','data-matter'=>translate('Are you sure you want to delete the whole stack, and all the images it contains?'))).xdv();
		$r .= xdv();
		return $r;
	}
	
	public function __call($n,$v){
		if(is_numeric($n)){
			$s = new Stack($n);
			T::$body[] = $s->$v();
		}
	}
	
	protected function filelist($mode='f'){
		foreach($this->children() as $obj){
			$file = new File($obj->fileid);
			switch($mode){
				case 'f':
					$data[] = array('id'=>$file->id,'url'=>$file->url,'hash'=>$file->hash,'path'=>$this->folder());
				break;
			}
		}
		T::$body[] = print_r($data,true);
	}
	
	protected function feed(){
		
		if($this->isDummy || count($this->children()) == 0){return '';}
		$r='';
		if($_SESSION['lang'] == 'fr_FR'){
			$d=translate(date('l',$this->created)).' '.date('j',$this->created).' '.translate(date('F',$this->created));
		}
		else{
			$d=translate(date('l',$this->created)).', '.translate(date('F',$this->created)).' '.date('j',$this->created);
		}
		$c='feed stack';
		$r .= dv($c).'<h3>'.lnk($this->title,'stack/'.$this->id).'</h3>'.dv('content').$this->photoCover(40,'stack',true,true).xdv().'<br class="clearfloat"/>'.'<p class="meta"><span class="date">'.translate('posted on').' '.$d.'</span> <span class="author">'.translate('by').' '.User::$users[$this->uid]->fullName('link').'</span></p>'.xdv();
		return $r;
	}
	
	
	//DISPLAY MODES
	public function summary(){
		return $this->title.' '.$this->description.' by '.User::$users[$this->uid]->fullName('full');
	}
	
	public function cover(){
		$r = '';
		if(!empty($this->cover) && Object::objectExists('Photo',$this->cover)){
			$cover = new Photo($this->cover);
			$img = $cover->img('stack',$path);
			return $path;
		}
		$pics = $this->children();
		if(empty($pics)){return '/img/blank.png';}
		shuffle($pics);
		foreach($pics as $obj){
			if($obj->className=='Photo'){
				$this->cover = $obj->id;
				$this->update();
				$img = $obj->img('stack',$path);
				return $path;
			}
		}
		return '/img/blank.png';
	}
	
	public function photoCover($number,$size='small',$link=true,$preview=false){
		$r = '';
		$pics = $this->children();
		if(empty($pics)){return lnk('<img src="/img/img80.png"/><img src="/img/img80.png"/><img src="/img/img80.png"/><img src="/img/img80.png"/><img src="/img/img80.png"/><img src="/img/img80.png"/>','stack/'.$this->id.'/edit');}
		shuffle($pics);
		foreach($pics as $i=>$obj){
			if($i<$number && $obj->className=='Photo'){
				if($preview){
					$r .= lnk($obj->img($size),'photo/'.$obj->id);
				}
				else{
					$r .= $obj->img($size);
				}
			}
		}
		return $r;
	}
	
	//OBJECT RELATED FUNTIONS
	
	protected function postDelete($force = false){
		if(IS_AJAX){
			res('script','$("#Stack_'.$this->id.'_editPreview").fadeOut();');
		}
		foreach($this->children() as $obj){
			$obj->delete();
		}
		return true;
	}
}
?>