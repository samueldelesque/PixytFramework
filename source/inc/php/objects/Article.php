<?php
class Article extends Object{
	public $uid;
	public $kid;
	public $prid;
	public $title;
	public $genre;
	public $content;
	public $tags;
	public $views;
	public $access;
	public $customOrder;
	
	protected static function publicFunctions(){
		return array(
			'fullView'=>true,
			'preview'=>true,
			'feed'=>true,
			'create'=>true,
		);
	}
	
	protected static function ownerFunctions(){
		return array(
			'edit'=>true,
			'editPreview'=>true,
			'selectable'=>true,
			'publish'=>true,
			'addtags'=>true,
		);
	}
		
	public function descriptor(){
		return array (
			'id'=>'int',
			'uid'=>'int',
			'kid'=>'int',
			'prid'=>'int',
			'title'=>'string',
			'genre'=>'string',
			'content'=>'string',
			'tags'=>'object',
			'views'=>'int',
			'access'=>'int',
			'customOrder'=>'int',
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
			
			case 'content':
				$this->$n=$v;
				return true;
			break;
			
			case 'title':
				if(strlen($v) <= 255){
					$this->$n = stripslashes($v);
					return true;
				}
				else{
					Msg::addMsg($n.' '.translate('too long (255 chars max)'));
					return false;
				}
			break;
			
			case 'removetag':
				if(!isset($this->tags[$v])){Msg::addMsg(translate('tag not found!'));return false;}
				else{
					unset($this->tags[$v]);
					if(IS_AJAX){
						res('script','$("#tag_'.$v.'").fadeOut();activate($("#tags"));');
						if(empty($this->tags)){res('script','$("#tags").html="'.translate('No tags yet.').'"');}
					}
				}
				return true;
			break;
			
			case 'tags':
			case 'tag':
				$tags = explode(',',$v);
				foreach($tags as $tag){
					$tag = sanitize($tag);
					$id = md5($tag);
					if(!isset($this->tags[$id])){
						$this->tags[$id] = $tag;
						res('script','$("#'.$this->className.'_'.$this->id.'_tags").append("'.addslashes($this->tag($tag,$id)).'");activate($("#tags"));$(".xbox").slideUp();');
					}
					else{
						Msg::addMsg($tag.' '.translate('already exists.'));
					}
				}
				return true;
			break;
		}
		return false;
	}
	
	public function construct(){
	}
	
	//GLOBAL FUNCTIONS
	public function summary($l=0){
		$r = '';
		$txt = htmltotext($this->content);
		$t = strlen($txt);
		if($l > $t){$l = $t;}
		$r .= substr($txt,0,$l);
		if($t > $l){$r .= '...';}
		return $r;
	}
	
	// DISPLAY MODES
	protected function fullView(){
		$r = '';
		T::$page['title'] = $this->title;
		T::$page['description'] = $this->summary(250);
		T::$page['author'] = User::$users[$this->uid]->fullName();
		$r .=
			dv('twothird left','mainFile')
				.dv('fullView article').$this->content.xdv()
			.xdv();
		$r .= dv('third right').dv('actions');
		
		
		$r .= dv('promoteTools');
		if($this->isOwner){
			$r .= lnk('<img src="'.HOME.'img/editable75-grey.png" height="40" width="40"/>','article/'.$this->id.'/edit',array(),array('title'=>translate('Edit'),'id'=>'edit'));
		}
		$r .= $this->promote().$this->heart().xdv();
		$r .= '<h1>'.$this->title.'</h1><h3>'.translate('by').' '.User::$users[$this->uid]->fullName('link').'</h3>';
		
		if(!empty($this->tags)){
			$r .= $this->tags();
		}
		$r .= $this->comments();
		$r .= '<br class="clearfloat"/>'.xdv().xdv();
		$r.= dv('whereAmI');
		$r.= lnk(translate('home'),'',array(),array('title'=>translate('Back home')));
		$r.= ' &gt; '.User::$users[$this->uid]->fullName('link');
		if($this->kid != 0){
			$r.= ' &gt; '.lnk(Stack::$stacks[$this->kid]->title,'stack/'.$this->kid,array(),array('title'=>translate('Back to serie')));
		}
		$r .= xdv();
		return $r;
	}
	
	public function feed($w=600){
		$r = '';
		$author = new User($this->uid);
		$t = $this->title.' '.translate('by').' '.$author->fullName('full');
		$url = HOME.'article/'.$this->id;
		$r .= dv('relative feed centerText').'<h2>'.$this->title.'</h2>'.$this->content.share($url,$t);
		$r .= dv('meta').dv('left').translate('by').' '.$author->fullName('link').xdv().dv('right').prettyTime($this->created).xdv().xdv();
		return $r.xdv();
	}
	
	protected function publish(){
		if(!$this->isOwner){return false;}
		$r = '';
		$r .= dv('padded');
		$r .= '<p>'.translate('Share it on your website:').'</p>';
		if(count(App::$user->sites()) == 0){
			$r .= '<p>'.translate('You have no websites yet!').' '.lnk('create one','site/create','',array('class'=>'btn')).'</p>';
		}
		else{
			foreach(App::$user->sites() as $site){
				if(!empty($site->alias)){break;}
				$form = new Form('Site',$site->id,'ajax',array('ajax'=>true,'class'=>'full','data'=>array('method'=>'article_publish','content'=>'Article_'.$this->id)));
				$form->write('<p>'.translate('Publish to').' '.$site->url.'</p>');
				$o=array();
				foreach($site->data['content'] as $id=>$page){
					if($page['x']==2&&is_array($page['c'])){
						$o[$page['u']]=$page['t'];
					}
					elseif($page['x']==1){
						if(is_array($page['c'])){
							foreach($page['c'] as $sub_page){
								if($sub_page['x'] == 2)$o[$sub_page['u']]='--'.$sub_page['t'];
							}
						}
					}
				}
				$form->{'publish'}('select',$o,false);
				$form->{translate('Publish')}('submit');
				$r .= dv('entry');
				$r .= $form->returnContent();
				$r.= xdv();
			}
		}
		$r .= xdv();
		return $r;
	}
	
	protected function old_publish(){
		if(isset($_REQUEST['site'])){
			if(!Object::objectExists('Site',$_REQUEST['site'])){
				Msg::addMsg('Wrong Site input.');
				return false;
			}
			$site = new Site($_REQUEST['site']);
			if(!$site->isOwner){
				Msg::addMsg('You may not edit that site.');
				return false;
			}
			
			if(!isset($_REQUEST['q'])){
				$_REQUEST['q'] = 0;
			}
			$add = array('Article',$this->id,time());
			if(!isset($site->data['content'][$_REQUEST['page']])||$site->data['content'][$_REQUEST['page']]['x']!=2){
				Msg::addMsg('Wrong page input ['.$_REQUEST['page'].'].');
				return false;
			}
			$site->data['content'][$_REQUEST['page']]['y']=time();
			$site->data['content'][$_REQUEST['page']]['c'][]=$add;
			if($site->update()){
				res('script','$(".xbox").slideUp();');
			}
			else{
				Msg::addMsg('Could not update website!');
				return false;
			}
		}
		if(!$this->isOwner){return false;}
		$r = '';
		$r .= dv('padder');
		$q = 'SELECT * FROM Site WHERE uid = "'.$this->uid.'"';
		if(LP::$db->customQuery($q,$d)){
			if(empty($d )){$r.='You have no websites yet.';}
			foreach($d as $sData){
				$site = new Site($sData['id'],$sData);
				$form = new Form(NULL,NULL,'article/'.$this->id.'/publish',array('ajax'=>true,'class'=>'full'));
				$o=array();
				foreach($site->content as $id=>$page){if($page['x']==2){$o[$id]=$page['t'];}}
				$form->site('hidden',$site->id);
				if(empty($o)){$form->write('<p class="italic">'.translate('No pages yet').'</p>');}else{$form->page('select',$o);}
				$form->{translate('Publish')}('submit');
				$r .= dv().'<span class="big">Publish to '.$site->url.'</span>'.$form->returnContent().xdv();
			}
		}
		$r .= xdv();
		return $r;
	}
	
	public function selectable($size='smallsquare',$action=false,$data=array(),$tools=''){
		return dv('preview selectable '.$size,'Article_'.$this->id.'_selectable','data-className="Article" data-id="'.$this->id.'" data-size="'.$size.'"').$this->summary(40).dv('tools').$tools.xdv().xdv();
	}
	
	protected function editPreview(){
		if($this->id == 0){return 'Dummy story.';}
		$c='previewBox preview article';
		if(empty($this->content)){$c.=' empty';}
		$r= dv('file article preview softlink','Article_'.$this->id.'_editPreview','data-url="article/'.$this->id.'/edit"');
		$r .= dv('head').lnk($this->title,'article/'.$this->id.'/edit',array(),array('title'=>$this->title)).xdv().dv('content').'<p class="tiny grey">'.shorten($this->content,100).'</p>'.xdv();
		$r .= dv('tools').lnk('<img src="/img/tool-edit.png" height="25px" alt="edit"/>','article/'.$this->id.'/edit').lnk('<img src="/img/tool-delete.png" height="25" alt="delete"/>','#cur',array('delete[Article]['.$this->id.']'=>true),array('data-type'=>'confirm','data-matter'=>translate('Are you sure you want to delete this article?'))).xdv();
		$r .= xdv();
		return $r;
	}
	
	protected function addtags(){
		$r ='';	
		$form = new Form($this->className,$this->id,true,array('ajax'=>true));
		$form->tags('textarea',translate('add tags'),'(separate by ",")','this.value=\'\'');
		$form->write('<br/>');
		$form->{translate('add')}('submit');
		$r .= $form->returnContent();
		return $r;
	}
	
	protected function edit($html=''){
		$r = '';
		T::$doPrintFooter = false;
		T::$page['title'] = translate('Edit photo');
		$r .= dv('inner-page');
		$r .= dv('navigation');
		$r .= dv('tab'); 
		$r .= dv('denomination').translate('photos').'<span class="right open"></span>'.xdv();
		$r .= dv('el').'<span class="bold">'.lnk(translate('all'),'account/organize','',array('class'=>'grey')).'</span>'.xdv();
		if(!empty($this->kid)&&is_object(Stack::$stacks[$this->kid])){
			$r .= dv('el').'<span class="bold">'.lnk(Stack::$stacks[$this->kid]->title,'stack/'.$this->kid.'/edit','',array('class'=>'grey')).'</span>'.xdv();
		}
		$r .= xdv();
		$r .= dv('tab');
		$r .= dv('denomination').translate('tags').xdv();
		$r .= dv('el').$this->tags().xdv();
		$r .= dv('el ajax','','data-url="'.HOME.'article/'.$this->id.'/addtags" data-type="popup" data-gethtml="true"').'<span class="bold">+'.translate('add').'</span>'.'<span class="right"><img src="/img/addtags40.png" height="20" alt="'.translate('Add tags').'" title="'.translate('Add tags').'"/></span>'.xdv();
		$r .= xdv();
		$r .= dv('tab');
		$r .= dv('denomination').translate('info').xdv();
		$r .= dv('el').'<ol class="squares"><li>'.lnk('<img src="'.HOME.'/img/site_editor/view.png" height="30" alt="'.translate('Photo views').'" title="'.translate('Photo views').'"/> '.prettyNumber($this->views,1),'article/'.$this->id,array(),array('class'=>'grey')).'</li>';
		$r .= '<li>'.lnk('<img src="'.HOME.'/img/site_editor/heart.png" height="30" alt="'.translate('Photo hearts').'" title="'.translate('Photo hearts').'"/> '.prettyNumber($this->heartsCounter(),1),'article/'.$this->id,array(),array('class'=>'grey')).'</li>';
		$r .= '<li>'.lnk('<img src="'.HOME.'/img/site_editor/comment.png" height="30" alt="'.translate('Photo comments').'" title="'.translate('Photo comments').'"/> '.prettyNumber($this->commentsCounter(),1),'article/'.$this->id,array(),array('class'=>'grey')).'</li>';
		$r .= '<li>'.count(explode(' ',$this->content)).' '.translate('words').'</li></ol>'.xdv();
		$r .= xdv();
		$r .= xdv();
		$r .= dv('organize','pagelet');
		$r .= dv('head');
		if(!empty($this->kid)){
			$r .= dv('toolbox').lnk('<img src="/img/stack40.png">','stack/'.$this->kid.'/edit',array('s'=>$_REQUEST['s']),array('class'=>'back')).xdv();
		}
		else{
			$r .= dv('toolbox').lnk('<img src="/img/organize-grey.png">','account/organize',array('s'=>$_REQUEST['s']),array('class'=>'back')).xdv();
		}
		$r .= dv('toolbox').'<h3><span id="'.$this->idPrefix.'_title" class="editable title">'.$this->title.'</span></h3>'.xdv();
		$r .= dv('toolbox right').lnk('<img src="/img/trashbin40.png"/>','stack/'.$this->kid.'/edit',array('delete[Article]['.$this->id.']'=>true)).xdv();
		$r .= dv('toolbox right').lnk('<img src="/img/addtags40.png" alt="add tags" title="Add tags"/>',strtolower($this->className).'/'.$this->id.'/addtags',array(),array('data-type'=>'popup')).xdv();
		$r .= dv('toolbox right').lnk('<img src="/img/share40.png"/>','article/'.$this->id.'/publish',array(),array('data-type'=>'popup')).xdv();
		if(!isset($_REQUEST['html'])){
			$r .= dv('toolbox right').lnk('edit html','article/'.$this->id.'/edit',array('html'=>'1'),array('class'=>'btn')).xdv();
		}
		else{
			$r .= dv('toolbox right').lnk('rich text','article/'.$this->id.'/edit',array(),array('class'=>'btn')).xdv();
		}
		$r .= xdv();
		
		
		$r .= dv('','articleContainer');
		$form = new Form('Article',$this->id,'',array('class'=>'article'));
		$form->content('textarea',$this->content,false);
		$form->{translate('Save')}('submit');
		if(!isset($_REQUEST['html'])){
		$r .= '<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script><script type="text/javascript">
tinyMCE.init({
	// General options
	mode : "textareas",
	theme : "advanced",
	//other plugins: pagebreak,style,layer,advhr,emotions,insertdatetime,media,searchreplace,print,directionality,fullscreen,noneditable,xhtmlxtras,template
	plugins : "autolink,lists,spellchecker,table,save,advimage,advlink,iespell,inlinepopups,preview,contextmenu,paste,visualchars,nonbreaking",
	
	// Theme options
	//other options:save,newdocument,|,,|,styleselect,formatselect,,strikethrough
	theme_advanced_buttons1 : "bold,italic,underline,fontselect,fontsizeselect,|,justifyleft,justifycenter,justifyright,justifyfull,|,image",
	//theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
	// theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
	//theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "hidden",
	theme_advanced_resizing : false,
	
	// Skin options
	skin : "o2k7",
	skin_variant : "silver",
	
	// Example content CSS (should be your site CSS)
	content_css : "/css/lp.css",
	
	// Drop lists for link/image/media/template dialogs
	//template_external_list_url : "js/template_list.js",
	//external_link_list_url : "'.HOME.'/js/tiny_mce/js/link_list.js",
	//external_image_list_url : "js/image_list.js",
	//media_external_list_url : "js/media_list.js",
});
</script>';
		}
		$r .= dv('article').$form->returnContent().xdv().xdv();
		$r .= xdv();
		
		$r .= '<br class="clearfloat"/>';
		$r .= xdv();
		$r .= xdv();
		return $r;
	}
	
	protected function preview(){
		if($this->id == 0){return 'Dummy story.';}
		$r= dv('previewBox article thumb','Article_'.$this->id);
		$r .= '<h3>'.lnk($this->title,'article/'.$this->id,array(),array('title'=>$this->title.' '.translate('by').' '.User::$users[$this->uid]->fullName())).'</h3><p class="padded">'.$this->summary(200).'</p>';
		$r .= xdv();
		return $r;
	}
	
	protected function tableData(){
		return array($this->title,$this->summary(40));
	}
	
	
	//PUBLIC FUNCTIONS
	public function directory(){
		$c = new Collection('Article');
		return $c->content('preview');
	}
	
	public static function create(){
		$r = '';
		$article = new Article();
		$article->title = translate('New article');
		if(isset($_REQUEST['kid'])){$article->kid = $_REQUEST['kid'];}
		$article->insert();
		res('script','window.location.href="'.HOME.'article/'.$article->id.'/edit";');
		//$r .= $article->editPreview();
		return $r;
	}
	
	//OBJECT RELATED FUNCTIONS
	protected function postDelete($force = false){
		if(is_object($this->story) && !$this->story->delete()){
			$this->error('Failed to delete story.',90);
			return false;
		}
		if(IS_AJAX){
			res('script','$("#Article_'.$this->id.'_editPreview").fadeOut();');
		}
		return true;
	}
	
	protected function postInsert(){
		return true;
	}
	
	protected function postUpdate(){
		if($this->kid != 0){Stack::$stacks[$this->kid]->update();}
		return true;
	}
}
?>