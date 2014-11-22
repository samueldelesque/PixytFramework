<?php
class Site extends Object{
	public $uid;
	public $adminNIC;		//OVH ADMIN NIC
	public $client;			//if set, site will load the settings.php file within a folder by this name in "Clients"
	public $title;			//default page title
	public $description;	//default page description - home page description
	public $url;			//The site domain
	public $settings;		//settings Object
	public $channel;
	public $published;		//not user editable - set to true when subscription is paid
	public $style;			//CSS object
	public $alias = false;	//set domain to use this as alias
	public $cover;			//the photo to show in searches and on startpage if used
	public $content=array();//content object		
	public $modules=array();//not yet available
	public $expirationDate;
	
	
	//this should point to the site base path - if on pixyt = site/ID/method else /	
	// private $relative_path;
	
	public static $pixytSites = array('pixyt.com','pixyt.fr','pixyt.us','pixyt.dk','dev.pixyt.com','stg.pixyt.com','launch.pixyt.com','pre.pixyt.com');
	public static $LASites = array('la.samueldelesque.me','lightarchitect.com','dev.lightarchitect.com');

	protected static $styleProperties = array(
		'site'=>array('font-family'),
		'menuItem'=>array('font-size','text-transform'),
		'logo'=>array('font-family','font-size','text-transform'),
	);
	
	public static function disalowUrls(){
		return array('photography','photographer','photographe','sex','sexy','cute','nude','porn','photo','photos','create','dev','sync','share','virtualize','site','canvas','display','store','secure','account','organize','home','directory','beta','alpha','developper','api','code','pix','light','architect','lightarchitect','google','facebook','linkedin','java','corporate','corporation','corp','template','temp','tmp','file','files','trash','bin','trashbin','msg','message','object','task','tasks','compta','accounting','connect','exhibit','lightroom','access','contact','feedback','song','music','transaction','user','product','migrate','load','loading','pyxit','pyx','sell','buy','subscribe','subscriber','suggest','paypal','search','config','theme','trace','upload','123','abc','pop','carot','carotte','pops','mail','imap','conf','pearl','mac','max','hdd','money','makemoney','god','spam','spy','doom','join','joined','perfect','ass','but','yes','nej','maybe','cul','boob','reverse','rev','read','write','destroy','distro','linux','mozilla','gift','gifts','partner','partners','get','put','adobe','photoshop','illustrator','title','url','empty','vide','titre','random','math','test','lol','ban','tan','mirror','ass','add','fuck','fucked','teen','naked','girl','gig','party','yourname','votrenom','localhost','local','var','char','int','php','mysql','phpmyadmin','sql','ajax','xxx','321','987654321','p0p','map','maps','data','logo','log','error');
	}
	
	public static function disalowPageUrls(){
		return array('edit','edit','admin','monitor','site','photo','store','api','upload','error','pageError','fullView','menu','preview','feed','portfolio','storeAuthor','create','directory','selectmenu','addcontent','settings','stats','addpage','addsection','addpage','editcontent','admin','article','video','login','logout','picklang',' ','%20');
	}
	
	protected static function publicFunctions(){
		return array(
			'fullView'=>true,
			'menu'=>true,
			'adminmenu'=>true,
			'preview'=>true,
			'feed'=>true,
			'portfolio'=>true,
			'photo'=>true,
			'article'=>true,
			'storeAuthor'=>true,
			'create'=>true,
			'css'=>true,
			'directory'=>true,
			'returnMap'=>true,
		);
	}
	
	protected static function ownerFunctions(){
		return array(
			'edit'=>true,
			'editPreview'=>true,
			'selectmenu'=>true,
			'addcontent'=>true,
			'settings'=>true,
			'stats'=>true,
			'addpage'=>true,
			'addsection'=>true,
			'addcontent'=>true,
			'editcontent'=>true,
			'admin'=>true,
		);
	}
	
	public function descriptor(){
		return array (
			'id'=>'int',
			'uid'=>'int',
			'adminNIC'=>'string',
			'client'=>'string',
			'title'=>'string',
			'description'=>'string',
			'url'=>'string',
			'channel'=>'int',	// 1: public, 2:pixyt+me (not blogs etc), 3: only my website, 8:hidden, 9:private (under construction)
			'published'=>'bool',
			'style'=>'object',
			'settings'=>'object',
			'alias'=>'string',
			'cover'=>'int',
			'content'=>'object',
			'modules'=>'object',
			'expirationDate'=>'date',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		);
	}
	
	// public $colorSchemes = array(
	// 	'dark'=>array(
	// 		'body'=>array('background'=>'url(/img/texture-10.png) repeat #000','color'=>'#5B6468'),
	// 		'a,a:visited'=>array('color'=>'#aaa'),
	// 		'a:hover'=>array('color'=>'#ddd'),
	// 		'#menu'=>array('background'=>'rgba(255,255,255,0.05)'),
	// 	),
	// 	'grey'=>array(
	// 		'body'=>array('background'=>'url(/img/texture-5.png) repeat #777','color'=>'#fff'),
	// 		'a,a:visited'=>array('color'=>'#eee'),
	// 		'a:hover'=>array('color'=>'#fff','text-shadow'=>'1px 1px 8px rgba(255,255,255,1)'),
	// 		'#menu'=>array('background'=>'rgba(0,0,0,0.1)'),
	// 	),
	// 	'oldschool'=>array(
	// 		'body'=>array('background'=>'url(/img/texture-6.png) repeat #777','color'=>'#666'),
	// 		'a,a:visited'=>array('color'=>'#777'),
	// 		'a:hover'=>array('color'=>'#333','text-shadow'=>'1px 1px 8px rgba(0,0,0,0.2)'),
	// 		'#menu'=>array('background'=>'url(/img/texture-6.png) repeat','box-shadow'=>'0 0 30px rgba(20,30,40,0.2)'),
	// 	),
	// 	'light'=>array(
	// 		'body'=>array('background'=>'url(/img/texture-9.png) repeat #FFF','color'=>'#5B6468'),
	// 		'a,a:visited'=>array('color'=>'#bbb'),
	// 		'a:hover'=>array('color'=>'#777'),
	// 		'#menu'=>array('background'=>'rgba(0,0,0,0.01)'),
	// 	),
	// );
	
	// public $pagetypes  = array(
	// 	2=>'portfolio',
	// 	1=>'folder', //Site Section
	// //	3=>'shop',
	// 	4=>'contact',
	// //	5=>'client',
	// //	6=>'custom'
	// );
	// public $cur = array('id'=>0,'x'=>'','t'=>'','d'=>'','u'=>'','c'=>array(),'i'=>0,'y'=>0);//The default page
	// public $requiredFields = array('uid','url');
	
	// public $pages = array(
	// 	'login'=>array('t'=>'Log in','x'=>'7','c'=>'','u'=>'login'),
	// 	'logout'=>array('t'=>'Log out','x'=>'7','c'=>'','u'=>'logout'),
	// 	'search'=>array('t'=>'Search','x'=>'7','c'=>'','u'=>'search'),
	// 	'clientAccess'=>array('t'=>'Client access','x'=>'7','c'=>'','u'=>'clientAccess'),
	// 	'admin'=>array('t'=>'Administrate your site','x'=>'7','c'=>'','u'=>'admin'),
	// 	'exhibit'=>array('t'=>'Exhibit','x'=>'7','c'=>'','u'=>'exhibit'),
	// 	'edit'=>array('t'=>'Edit page','x'=>'7','c'=>'','u'=>'edit'),
	// 	'organize'=>array('t'=>'Organize','x'=>'7','c'=>'','u'=>'organize'),
	// 	'ajax'=>array('t'=>'Ajax request','x'=>'7','c'=>'','u'=>'ajax'),
	// 	'picklang'=>array('t'=>'Choose language','x'=>'7','c'=>'','u'=>'ajax'),
	// 	'store'=>array('t'=>'Stores','x'=>'7','c'=>'','u'=>'store'),
	// 	'termsofsales'=>array('t'=>'Terms of sales','x'=>'7','c'=>'','u'=>'termsofsales'),
	// 	'checkout'=>array('t'=>'Checkout','x'=>'7','c'=>'','u'=>'checkout'),
	// 	'project'=>array('t'=>'Project','x'=>'7','c'=>'','u'=>'project'),
	// 	'photo'=>array('t'=>'Photo','x'=>'7','c'=>'','u'=>'photo'),
	// 	'article'=>array('t'=>'article','x'=>'7','c'=>'','u'=>'article'),
	// 	'shopitem'=>array('t'=>'Shopitem','x'=>'7','c'=>'','u'=>'shopitem')
	// );
	
	// public static $menus = array(
	// 	1=>'centermenu',
	// 	2=>'topmenu',
	// 	3=>'leftmenu',
	// 	4=>'rightmenu',
	// 	5=>'bottommenu',
	// );
	
	public function validateData($n,$v){
		switch($n){
			// case 'removepage':
			// 	if(!$this->pageExists($v,$edit)){
			// 		Msg::notify('That page does not exists!');
			// 	}
			// 	else{
			// 		foreach($this->content as $pageid=>$page){
			// 			if($page->u == $v){
			// 				unset($this->content->$pageid);
			// 				Msg::addMsg('The page was removed.');	
			// 				res('script','window.location.href=HOME+"'.$this->relative_path.'edit";');
			// 				return true;
			// 			}
			// 			if($page->x==1){
			// 				foreach($page->c as $sub_pageid=>$sub_page){
			// 					if($sub_page->u == $v){
			// 					unset($this->content->$pageid->c->$sub_pageid);
			// 					Msg::addMsg('The page was removed.');
			// 					res('script','window.location.href=HOME+"'.$this->relative_path.'edit";');
			// 					return true;
			// 				}
			// 			}
			// 			}
			// 		}
			// 	}
			// 	return false;
			// break;
			
			// case 'showinmenu':
			// 	$this->content->$id->m=(bool)$v;
			// 	return true;
			// break;
			
			// case 'deletelogo':
			// 	$file = new Photo($this->settings->l);
			// 	$file->delete();
			// 	$this->settings->l = '';
			// 	return true;
			// break;
			
			// case 'deleteicon':
			// 	$file = new File($this->settings->i);
			// 	$file->delete();
			// 	$this->settings->i = '';
			// 	return true;
			// break;
			
			// case 'feedback':
			// 	$this->settings->f = (bool)$v;
			// 	return true;
			// break;
			
			// case 'showtags':
			// 	$this->settings->g = (bool)$v;
			// 	return true;
			// break;
			
			// case 'showtitle':
			// 	$this->settings->v = (bool)$v;
			// 	return true;
			// break;
			
			case 'settings':
				$this->settings = $v;
				// $validate = true;
				// foreach($v as $name=>$value){
				// 	switch($name){
				// 		case 'l':
				// 			if(!empty($this->settings->$name) && preg_match('/[0-9]/',$this->settings->$name)){
				// 				$file = new File($this->settings->$name);
				// 				$file->delete();
				// 			}
				// 			if(preg_match('/[0-9]/',$value)){
				// 				$this->settings->$name = $value;
				// 			}
				// 			elseif(strlen($value) < 255){
				// 				$this->settings->$name = $value;
				// 			}
				// 			else{
				// 				Msg::addMsg(translate('Page logo must be between 5 and 255 characters.'));
				// 				$validate = false;
				// 			}
				// 			if(empty($value)){
				// 				res('script','$(".siteLogo").find("img").fadeOut().find("h1").fadeOut();');
				// 			}
				// 		break;
						
				// 		case 'm':
				// 		case 'menu':
				// 			if(isset(self::$menus[$this->settings->m])){
				// 				$oldclass=self::$menus[$this->settings->m];
				// 			}
				// 			else{
				// 				$oldclass='';
				// 			}
				// 			$this->settings->m=$value;
				// 			res('script','$("body").removeClass("'.$oldclass.'").addClass("'.$value.'");');
				// 		break;
						
				// 		case 's';
				// 		case 'size':
				// 			$this->settings->s=$value;
				// 		break;
						
				// 		case 'ga':
				// 			if(strlen($value) < 18){
				// 				$this->settings->$name = strval($value);
				// 			}
				// 			else{
				// 				Msg::notify(translate('Wrong Google Analytics account input'));
				// 			}
				// 		break;
						
				// 		case 'c':
				// 		case 'color':
				// 			if(!isset($this->colorSchemes[$value])){Msg::notify('Scheme not defined!');return false;}
				// 			foreach($this->colorSchemes[$value] as $selector=>$properties){
				// 				res('script','$("'.$selector.'").css('.json_encode($properties).');');
				// 				$this->settings->c=$value;
				// 			}
				// 			return true;
				// 		break;
						
				// 		case 't':
				// 		if(!Object::objectExists('Theme',$value)){$value=1;}
				// 			$theme = new Theme($value);
				// 			$this->settings->$name = $value;
				// 			Msg::addMsg('The theme was changed to '.$theme->title);
				// 		break;
						
				// 		default:
				// 			$this->error('Trying to set undefined settings::{'.$name.'} in Site');
				// 		break;
				// 	}
				// }
				// if($validate){				
				// 	return true;
				// 	$this->update();
				// }
			break;
			
			// case 'order':
			// 	if(!is_array($v)){$v = objectToArray(json_decode($v));}
			// 	$new = array();
			// 	$pageCount = 0;
			// 	foreach($v as $page){
			// 		if(!$this->pageExists($page->u,$edit)){
			// 			Msg::addMsg('That page does not exists ['.$page->u.']!');
			// 			return false;
			// 		}
			// 		if(isset($page->c) && is_array($page->c)){
			// 			$edit->c = array();
			// 			foreach($page->c as $sub){
			// 				if(!$this->pageExists($sub->u,$sub_edit)){
			// 					Msg::addMsg('That subpage does not exists ['.$sub->u.']!');
			// 					return false;
			// 				}
			// 				$edit->c[] = $sub_edit;
			// 				$pageCount++;
			// 			}
			// 		}
			// 		$new[] = $edit;
			// 		$pageCount++;
			// 	}
			// 	if($pageCount != $this->pageCount()){
			// 		Msg::notify('Some data was lost during page ordering. Saves were not saved.');
			// 		return false;
			// 	}
			// 	$this->content = $new;
			// 	return true;
			// break;
			
			// case 'publish':
			// 	//shortcut to publish from Stack or Photo
			// 	if(isset($_REQUEST['content'])){
			// 		$parts=explode('_',$_REQUEST['content']);
			// 		if(count($parts) != 2){
			// 			Msg::notify('Wrong content to publish.');
			// 			return false;
			// 		}
					
			// 		if(!$this->pageExists($v,$edit)&&$v!='add'){
			// 			Msg::notify('Wrong page for publishing');
			// 			return false;
			// 		}
			// 		if($v=='add'||$edit->x==1){
			// 			if(ucfirst($parts[0]) == 'Stack' && Object::objectExists('Stack',$parts[1])){
			// 				$content = new stdClass;
			// 				$stack = new Stack($parts[1]);
			// 				$content->t = $stack->title;
			// 				$content->u = normalize($stack->title);
			// 				$content->m = true;
			// 				$content->x = 2;
			// 				$content->d = $stack->description;
			// 				$content->c = array();
			// 				foreach($stack->children() as $item){
			// 					$content->c[] = array($item->className,$item->id);
			// 				}
			// 				$first = reset($this->content);
			// 				$content->v = 0;
			// 				$content->i = $content->y = time();
			// 				if($v=='add'){
			// 					$content->add=$_REQUEST['content'];
			// 					$this->validateData('page',array($v=>$content));
			// 				}
			// 				else{
			// 					$this->validateData('page',array($v=>array('addsub'=>$content)));
			// 				}
			// 			}
			// 		}
			// 		else{
			// 			//Update existing stack, skip duplicates
			// 			if(ucfirst($parts[0]) == 'Stack' && Object::objectExists('Stack',$parts[1])){
			// 				$stack = new Stack($parts[1]);
			// 				foreach($stack->children() as $item){
			// 					if(!$this->pageHasObject($v,$item->className,$item->id)){
			// 						$this->validateData('page',array($v=>array('add'=>$item->className.'_'.$item->id)));
			// 					}
			// 				}
			// 			}
			// 			else{
			// 				$o = ucfirst($parts[0]);
			// 				$object = new $o($parts[1]);
			// 				$this->validateData('page',array($v=>array('add'=>$object->className.'_'.$object->id)));
			// 			}
			// 		}
			// 		Msg::notify(translate('Your content was published.'));
			// 		res('script','$(".xbox").fadeOut();');
			// 		return true;
			// 	}
			// 	else{
			// 		Msg::notify('No content to publish.');
			// 		return false;
			// 	}
			// break;
			
			case 'page':
			case 'content':
				if(!is_array($v) && !is_object($v)){
					// Msg::notify('Site content must be an array!');
					return false;
				}
				$this->content = $v;
				return true;
				// $validate = true;
				// foreach($v as $page=>$content){
				// 	if($page === 'add'){
				// 		if($content->t==translate('my new awesome page')){$content->t='New page';}
				// 		if(!isset($content->d)||$content->d==translate('what this page contains')){$content->d='';}
				// 		if(!isset($content->m)){$content->m=true;}
				// 		if(!in_array($content->x,array_keys($this->pagetypes))){$content->x=2;}
				// 		if(!isset($content->c) || !is_array($content->c)){$content->c = array();}
				// 		$url = normalize($content->t);
				// 		$p = count($this->content);
				// 		$i=0;
				// 		while($this->pageExists($url)){$url=$url.'-'.$i;$i++;}
				// 		while(isset($this->content[$p])){$p++;}
				// 		$content->id = $p;
				// 		$content->u = $url;
				// 		if(in_array($url,self::$disalowPageUrls)){
				// 			Msg::notify(translate('You may not use that url sorry.'));
				// 			return false;
				// 		}
				// 		$content->i = $content->y = time();
				// 		$this->content[$p] = array(
				// 			'x'=>$content->x,
				// 			't'=>$content->t,
				// 			'd'=>$content->d,
				// 			'u'=>$content->u,
				// 			'c'=>$content->c,
				// 			'm'=>$content->m,
				// 			'i'=>$content->i,
				// 			'y'=>$content->y,
				// 		);
				// 		$edit = $content;
				// 	}
				// 	else{
				// 		if(!$this->pageExists($page,$edit)){
				// 			Msg::addMsg('That page does not exists!');
				// 			return false;
				// 		}
				// 		$edit->y = time();
				// 	}
				// 	$uniqueId = $edit->u;
				// 	foreach($content as $name=>$value){
				// 		switch($name){
				// 			case'order':
				// 				$new = array();
				// 				if(!is_array($value)){$value = objectToArray(json_decode($value));}
				// 				foreach($value as $i=>$position){
				// 					$input = explode('_',$position);
				// 					if(count($input) != 2){$this->error('Wrong order input value.');return false;}
				// 					$new[]=array($input[0],$input[1],time());
				// 				}
				// 				$edit->c = $new;
				// 			break;
							
				// 			case 'id':
				// 			case 'i':
				// 			case 'y':
				// 				//these are automatically set, do not allow user input
				// 			break;
							
				// 			case 'x':
				// 				if(isset($this->pagetypes[$value])){
				// 					$edit->x = $value;
				// 				}
				// 				else{
				// 					$validate = false;
				// 					Msg::addMsg(translate('Page type not valid'));
				// 				}
				// 			break;
							
				// 			case 't':
				// 				if(strlen($value) < 255){
				// 					$edit->t = $value;
				// 					$url = normalize($value);
				// 					$id = count($this->content);
				// 					if($url == 'newpage'||$url=='title'||$url==translate('title')||empty($url)){$url='page-'.$id;}
				// 					$count=0;
				// 					while($this->pageExists($url,$existing,true,$count)&&$count>1){$url=$url.$count;$count++;}
				// 					$edit->u = $url;
				// 				}
				// 				else{
				// 					Msg::addMsg(translate('Page title must be between 5 and 255 characters.'));
				// 					$validate = false;
				// 				}
				// 			break;
							
				// 			case 'd':
				// 				if(strlen($value) < 900){
				// 					$edit->d = $value;
				// 				}
				// 				else{
				// 					Msg::addMsg(translate('Page description must be between 5 and 900 characters.'));
				// 					$validate = false;
				// 				}
				// 			break;
							
				// 			case 'u':
				// 				/*
				// 				if(normalize($value) != $edit->u){
				// 					if(strlen($value) > 1 && strlen($value) < 255 && !$this->pageExists(normalize($value),$duplicate)){
				// 						$edit->u = normalize($value);
				// 					}
				// 					else{
				// 						Msg::addMsg(translate('Failed to validate url ([u='.$value.' strlen='.strlen($value).' duplicate:'.$duplicate->u.' -- '.$edit->u.'] 1-255 chars, unique)'));
				// 						$validate = false;
				// 					}
				// 				}
				// 				*/
				// 			break;
							
				// 			case 'addsub':
				// 				if($edit->x!=1){
				// 					Msg::notify('Trying to append a page to a non-section.');
				// 					return false;
				// 				}
				// 				if(is_array($edit->c) && is_array($value)){
				// 					if(!isset($value->m)){$value->m=true;}
				// 					if(!in_array($value->x,array_keys($this->pagetypes))){$value->x=2;}
				// 					if(!isset($value->c) || !is_array($value->c)){$value->c = array();}
				// 					$edit->c[] = $value;
				// 				}
				// 				else{
				// 					Msg::notify('Could not validate input for adding subpage.');
				// 				}
				// 			break;
							
				// 			case 'c':
				// 				$edit->c = $value;
				// 			break;
							
				// 			case 'v':
				// 				$edit->v = $value;
				// 			break;
							
				// 			case 'add':
				// 				$s = explode('_',$value);
				// 				if(Object::objectExists($s[0],$s[1])){
				// 					$edit->c[] = array(ucfirst($s[0]),intval($s[1]),time());
									
				// 					if($s[0]=='Photo'){
				// 						if(isset($_REQUEST['method'])){
				// 							switch($_REQUEST['method']){
				// 								case 'photo_publish':
				// 									res('script','$(".xbox").fadeOut;');
				// 								break;
												
				// 								case 'article_publish':
				// 									res('script','$(".xbox").fadeOut;');
				// 								break;

				// 								case 'site_editor':
				// 									$photo = new Photo($s[1]);
				// 									$img = $photo->selectable('smallthumb',false,array(),lnk('<img src="/img/delete-red.png" height="16px"/>','#',array('update[Site]['.$this->id.'][page]['.$edit->u.'][rmv]'=>'Photo_'.$photo->id)));
				// 									res('script','$("#'.$s[0].'_'.$s[1].'_selectable").fadeOut(200,function(){$("#pageEditor").append("'.addslashes($img).'");activate($("#pageEditor"));});');
				// 								break;
				// 							}
				// 						}
				// 					}
				// 				}
				// 				else{
				// 					$this->error('Adding unexistant photo to website.');
				// 					$validate = false;
				// 				}
				// 			break;
							
				// 			case 'rmv':
				// 				$s = explode('_',$value);
				// 				if(is_array($edit->c)){
				// 					foreach($edit->c as $p=>$item){
				// 						if(strtolower($item[0])==strtolower($s[0])&&$item[1]==$s[1]){
				// 							unset($edit->c[$p]);
				// 							res('script','$("#'.ucfirst($s[0]).'_'.$s[1].'_selectable").fadeOut();');
				// 						}
				// 					}
				// 				}
				// 			break;
							
				// 			case 'm':
				// 				$edit->m = (bool)$value;
				// 			break;
							
				// 			case 'bgd':
				// 				$edit->bgd = $value;
				// 			break;
							
				// 			default:
				// 				Msg::addMsg($name.' '.translate('is not a valid parameter for a page'));
				// 			break;
				// 		}
				// 	}
				// 	$this->savePage($uniqueId,$edit);
				// }
				return $validate;
			break;
			
			// case 'remove':
			// 	$target = explode('_',$v);
			// 	$validate = true;
			// 	foreach($this->content as $p=>$page){
			// 		if($page->u == $target[0]){
			// 			$c = array();
			// 			foreach($this->content[$p]->c as $i=>$item){
			// 				if($i != $target[1]){
			// 					$c[] = $item;
			// 				}
			// 			}
			// 			$this->content[$p]->c = $c;
			// 		}
			// 	}
			// 	if($validate){
			// 		res('script','window.location.reload();');
			// 		return true;
			// 	}
			// break;
			
			// case 'rx':
			// 	if(is_numeric($v)){
			// 		$this->settings->rx=(bool)$v;
			// 		return true;
			// 	}
			// 	else{
			// 		Msg::addMsg(translate('RX must be BOOL'));
			// 		return false;
			// 	}
			// break;
			
			case 'title':
				if(strlen($v) < 900){
					$this->$n=$v;
					return true;
				}
				else{
					Msg::addMsg(translate('Title must be between 5 and 900 characters.'));
					return false;
				}
			break;
			
			case 'description':
				if(strlen($v) < 900){
					$this->$n=$v;
					return true;
				}
				else{
					Msg::addMsg(translate('Description must be between 5 and 900 characters.'));
					return false;
				}
			break;
			
			case 'url':
				if(strlen($v) < 255 && strlen($v) > 4 && isLink($v)){
					$this->$n = $v;
					return true;
				}
				else{
					Msg::addMsg(translate('Url must be between 4 and 255 characters and be a valid url.'));
					return false;
				}
			break;
			
			case 'cover':
				if(is_numeric($v)){
					$this->$n=$v;
					return true;
				}
				else{
					Msg::addMsg(translate('Cover must be a valid photo id.'));
					return false;
				}
			break;
			
		// 	case 'style':
		// 		if(!is_array($v)){$this->error('Style not an array');return false;}
		// 		$js = array();
		// 		foreach($v as $class=>$properties){
		// 			if(isset(self::$styleProperties[$class]) && is_array($properties)){ 
		// 				$old = $this->style[$class];
		// 				foreach($properties as $property=>$value){
		// 					if(in_array($property,self::$styleProperties[$class])){
		// 						$this->style[$class][$property]=$value;
		// 					}
		// 					else{
		// 						Msg::notify($property.' not recognized');
		// 					}
		// 				}
		// 				foreach(self::$styleProperties[$class] as $property=>$value){
		// 					if(!isset($properties[$property])){
		// 						$this->style[$class][$property]=$old[$property];
		// 					}
		// 				}
		// 			}
		// 			else{
		// 				Msg::notify($class.' not recognized');
		// 			}
		// 		}
		// 		$js='';
		// 		foreach(self::$styleProperties as $class=>$data){
		// 			$js .= '$(".'.$class.'").css('.json_encode($this->style[$class]).');';
		// 		}
		// 		res('script',$js);
		// 		return true;
		// 	break;
			
		// 	case 'modules':
		// 		if(is_array($v)){$this->$n = $v;return true;}
		// 		Msg::notify('Modules should be passed in an array format!');
		// 	break;
			
		// 	case 'alias':
		// 		if(strlen($v) <= 255){$this->$n=$v;}
		// 		return true;
		// 	break;
			
		// 	case 'created':
		// 	case 'modified':
		// 	case 'id':
		// 	case 'uid':
		// 	case 'adminNIC':
		// 	case 'client':
		// 	case 'channel':
		// 	case 'expirationDate':
		// 	case 'deleted':
		// 	case 'published':
		// 		return true;
		// 	break;
			
		// 	case 'settings':
		// 		if(!is_object($v)){Msg::notify('Settings must be an object!');return false;}
		// 		foreach($v as $name=>$value){
		// 			$this->settings->$name = $value;
		// 		}
		// 		return true;
		// 	break;
			
		}
		return false;
	}
	
	public function construct(){
		if(isset($this->settings->ga) && $this->settings->ga !== false && !defined('USER_ANALYTICS')){define('USER_ANALYTICS',$this->settings->ga);}
		// if($this->url != HOST){
		// 	$this->relative_path = 'site/'.$this->id.'/';
		// }
	}

	public static function contentFromDirectory($dir,$template='',$listDir= array()){
		if($handler = opendir($dir)){
			while (($sub = readdir($handler)) !== FALSE){
				$ignore = array('.','..','_DS_Store','Thumb.db','.DS_Store','.git','.gitignore','.svn','description.txt','title.txt');
				if (!in_array($sub,$ignore)){
					if(is_file($dir.'/'.$sub)){
						$obj = new stdClass;
						$obj->type = 'img';
						$obj->path = str_replace(ROOT,'/',$dir.'/'.$sub);
						$obj->path = str_replace('../cdn/','http://cdn.pixyt.com/',$obj->path);
						// die(str_replace(ROOT,'/',$dir.'/'.$sub));
						$title_parts = explode('-', $sub);
						if(count($title_parts) >= 2){
							array_shift($title_parts);
							$title = implode(' ', $title_parts); 

							$title_parts = explode('.', $title);
							if(count($title_parts) >= 2){
								array_pop($title_parts);
								$title = implode(' ', $title_parts); 
							}
						}
						else{
							$title = '';
						}
						$obj->title = str_replace(array('-','_'),' ',$title);
						// if(preg_match('/^[0-9 ]{1,999}$/',$obj->title)){$obj->title='';}
						$listDir[] = $obj;
					}
					elseif(is_dir($dir.'/'.$sub)){
						$content = new stdClass;
						$content->content = self::contentFromDirectory($dir.'/'.$sub,$template);
						$title_parts = explode('_', $sub);
						if(count($title_parts) >= 2){
							array_shift($title_parts);
							$title = implode(' ', $title_parts); 
						}
						else{
							$title = $sub;
						}
						if(file_exists($dir.'/'.$sub.'/title.txt')){
							// die(mb_detect_encoding(file_get_contents($dir.'/'.$sub.'/title.txt')).' --');
							$content->title = htmlspecialchars(file_get_contents($dir.'/'.$sub.'/title.txt'));
						}
						else{
							$content->title = str_replace('-',' ',$title);
						}
						$content->description = '';
						if(file_exists($dir.'/'.$sub.'/description.txt')){
							$content->description = nl2br(htmlspecialchars(file_get_contents($dir.'/'.$sub.'/description.txt')));
						}
						$url = strtolower(str_replace(' ','-',trim($title)));
						$content->template = $template;
						$listDir[$url] = $content;
					} 
				} 
			}    
			closedir($handler); 
		}
		// print_r($listDir);
		return $listDir;    
	}
	
	// protected function cssArray(){
	// 	$t = new Theme($this->settings->t);
	// 	print_r(css2array($t->css));
	// 	die();
	// }
	
	// public function siteHasObject($type,$id){
	// 	foreach($this->content as $pageid=>$d){
	// 		if(is_array($d->c)){
	// 			foreach($d->c as $obj){
	// 				if(strtolower($obj[0]) == strtolower($type) && $id == $obj[1]){
	// 					return true;
	// 				}
	// 			}
	// 		}
	// 		if(isset($d->x) && $d->x == 1){
	// 			foreach($d->c as $sub_pageid=>$sub_d){
	// 				if(is_array($sub_d->c)){
	// 					foreach($sub_d->c as $obj){
	// 						if(strtolower($obj[0]) == strtolower($type) && $id == $obj[1]){
	// 							return true;
	// 						}
	// 					}
	// 				}
	// 			}
	// 		}
	// 	}
	// 	return false;
	// }
	
	// public function pageHasObject($page,$type,$id){
	// 	if(!$this->pageExists($page,$d)){return false;}
	// 	if(is_array($d->c)){
	// 		foreach($d->c as $obj){
	// 			if(strtolower($obj[0]) == strtolower($type) && $id == $obj[1]){
	// 				return true;
	// 			}
	// 		}
	// 	}
	// 	if(isset($d->x) && $d->x == 1){
	// 		foreach($d->c as $sub_pageid=>$sub_d){
	// 			if(is_array($sub_d->c)){
	// 				foreach($sub_d->c as $obj){
	// 					if(strtolower($obj[0]) == strtolower($type) && $id == $obj[1]){
	// 						return true;
	// 					}
	// 				}
	// 			}
	// 		}
	// 	}
	// 	return false;
	// }
	
	// public function pageRemoveObject($url,$object,$id){
	// 	foreach($this->content as $pageid=>$d){
	// 		if($d->u == $url){
	// 			if(is_array($d->c)){
	// 				foreach($d->c as $pos=>$obj){
	// 					if($obj[0]==$object&&$obj[1]==$id){
	// 						unset($this->content->$pageid->c[$pos]);
	// 						return true;
	// 					}
	// 				}
	// 			}
	// 		}
	// 		if(isset($d->x) && $d->x == 1){
	// 			foreach($d->c as $sub_pageid=>$sub_d){
	// 				if($sub_d->u == $url){
	// 					if(is_array($sub_d->c)){
	// 						foreach($sub_d->c as $pos=>$obj){
	// 							if($obj[0]==$object&&$obj[1]==$id){
	// 								unset($this->content->$pageid->c[$sub_pageid]->c[$pos]);
	// 								return true;
	// 							}
	// 						}
	// 					}
	// 				}
	// 			}
	// 		}
	// 	}
	// 	return false;
	// }
	
	// public function savePage($url,$data){
	// 	foreach($this->content as $pageid=>$d){
	// 		if($d->u == $url){
	// 			$this->content->$pageid = $data;
	// 			return true;
	// 		}
	// 		if(isset($d->x) && $d->x == 1){
	// 			foreach($d->c as $sub_pageid=>$sub_d){
	// 				if($sub_d->u == $url){
	// 					$this->content->$pageid->c[$sub_pageid] = $data;
	// 					return true;
	// 				}
	// 			}
	// 		}
	// 	}
	// 	return false;
	// }
	
	// public function pageCount(){
	// 	$c=0;
	// 	foreach($this->content as $pageid=>$d){
	// 		if(isset($d->x) && $d->x == 1){
	// 			foreach($d->c as $sub_pageid=>$sub_d){
	// 				$c++;
	// 			}
	// 		}
	// 		$c++;
	// 	}
	// 	return $c;
	// }
	
	// public function pageExists($url,&$page='',$getcount=false,&$count=0){
	// 	foreach($this->content as $pageid=>$d){
	// 		if(isset($d->u) && $d->u == $url){
	// 			$page = $d;
	// 			$page->id = $pageid;
	// 			if(!$getcount)return true;
	// 			else $count++;
	// 		}
	// 		if(isset($d->x) && $d->x == 1){
	// 			foreach($d->c as $sub_pageid=>$sub_d){
	// 				if(isset($sub_d->u) && $sub_d->u == $url){
	// 					$page = $sub_d;
	// 					$page->id = $sub_pageid;
	// 					if(!$getcount)return true;
	// 					else $count++;
	// 				}
	// 			}
	// 		}
	// 	}
	// 	$page = array('x'=>2,'t'=>'Not found','d'=>'No description','u'=>randStr(),'m'=>true,'id'=>-1,'c'=>array(),'i'=>0,'y'=>0);
	// 	if($count>0&&$getcount){return true;}
	// 	return false;
	// }
	
	// public function pageGetItems($page){
	// 	if(!$this->pageExists($page,$d)){
	// 		return array();
	// 	}
	// 	$map = array();
	// 	if(is_array($d->c)){
	// 		foreach($d->c as $pos=>$item){
	// 			$map[$pos]=$item;
	// 		}
	// 	}
	// 	return $map;
	// }
	
	// protected function returnMap(){
	// 	$map = array();
	// 	foreach($this->content as $pageid=>$d){
	// 		if($d->x == 1){
	// 			$map[$pageid.':'.$d->u] = array();
	// 			foreach($d->c as $sub_pageid=>$sub_d){
	// 				$map[$pageid.':'.$d->u][$sub_pageid]=$sub_d->u;
	// 			}
	// 		}
	// 		else{
	// 			$map->$pageid=$d->u;
	// 		}
	// 	}
	// 	return(nl2br(print_r($map,true)));
	// }
	
	protected function login(){
		if(isset($_REQUEST['email'],$_REQUEST['password'])){//for static login
			if(User::login($_REQUEST['email'],$_REQUEST['password'])){
				Msg::addMsg(translate('You logged in!'));
				$_SESSION['lang'] = App::$user->settings->siteLang;
				T::$jsfoot[] = 'window.location.href="'.HOME.'";';
				return 'please wait...';
			}
			else{
				Msg::addMsg(translate('Wrong email/password combination').'.');
			}
		}
		T::$page['title'] = translate('Log in');
		$r = '';
		if(App::$user->id != 0){
			$r .= dv('settings');
			$r .= '<h2 class="padder info">';
			$r .= translate('You are already logged in.');
			$r .= '</h2>'.xdv();
		}
		else{
			$r .= dv('login');
			$r .= '<h1>'.translate('Login').'</h1>';
			$r .= dv('contentBox element').App::$user->loginForm().xdv();
			$r .= xdv();
		}
		return $r;
	}

	// protected function settings(){
	// 	$r = '';
	// 	$r .= dv('settings d750 center');
	// 	T::$page['title'] = translate('Settings');
	// 	$r .= dv('element').'<h2>'.translate('Title').'</h2>'.'<span class="editable" id="'.$this->idPrefix.'_title">'.$this->title.'</span>';
	// 	$r .= dv('logoImage');
	// 	if(!empty($this->settings->l) && is_numeric($this->settings->l)){
	// 		$file = new File($this->settings->l);
	// 		$r .= '<img src="'.$file->url().'" title="'.$this->title.'" class="editSiteLogo" alt="'.$this->title.'"/><span class="padded">'.lnk('delete','#',array('update[Site]['.$this->id.'][settings][l]'=>''),array('class'=>'btn')).'</span>';
	// 	}
	// 	$r .= Photo::uploadBtn(7,$this->id).xdv().xdv();
	// 	/*
	// 	$yesno = array('On'=>'1','Off'=>'0');
	// 	$table = new Table();
	// 	$r .= dv('site options').'<h2>'.translate('Options').'</h2>';
	// 	$table->addLine(array('<label>'.translate('Use Ajax to load page content?').'</label>',swtch('rx',$yesno,$this->settings->rx,'Site',$this->id)));
	// 	if(!isset($this->settings->f)){$this->settings->f=true;}
	// 	$table->addLine(array('<label>'.translate('Allow users to give feedback?').'</label>',swtch('feedback',$yesno,$this->settings->f,'Site',$this->id)));
	// 	if(!isset($this->settings->g)){$this->settings->g=true;}
	// 	$table->addLine(array('<label>'.translate('Display tags on your photos?').'</label>',swtch('showtags',$yesno,$this->settings->g,'Site',$this->id)));
	// 	if(!isset($this->settings->v)){$this->settings->v=true;}
	// 	$table->addLine(array('<label>'.translate('Show page title/description on top of page?').'</label>',swtch('showtitle',$yesno,$this->settings->v,'Site',$this->id)));
	// 	$r .= $table->returnContent();
	// 	*/
	// 	$r .= dv('element').'<h2>'.translate('Analytics').'</h2><p class="grey">Enter your Google Analycs account here (like UA-XXXXX-1)</p>'.'<p>'.'<span class="editable" id="'.$this->idPrefix.'_settings_ga">'.$this->settings->ga.'</span></p>'.xdv();
	// 	$m = array();//self::$menus[$this->settings->m]=>$this->settings->m);
	// 	foreach(self::$menus as $id=>$menu){
	// 		//if($id!=$this->settings->m){
	// 			$m[$menu] = $id;
	// 		//}
	// 	}
	// 	$r .= dv('element').'<h2>Menu</h2>'.swtch('settings_m',$m,$this->settings->m,'Site',$this->id).xdv();$m = array();
		
	// 	$c= array();
	// 	foreach($this->colorSchemes as $id=>$content){
	// 		$c[$id] = $id;
	// 	}
	// 	if(!isset($this->settings->c)){$this->settings->c='light';}
	// 	$r .= dv('element').'<h2>Color Scheme</h2>'.swtch('settings_c',$c,$this->settings->c,'Site',$this->id).xdv();
	// 	if(!isset($this->settings->s)){$this->settings->s=0;}
	// 	$r .= dv('element').'<h2>'.translate('Style').'</h2>';
	// 	$r .= $this->styleEditor();
	// 	$r .= xdv();
		
	// 	$r .= dv('element picktheme').'<h2>'.translate('theme').':</h2>';
	// 	if(is_array(App::$user->data->settings->accesses) && in_array(5,App::$user->data->settings->accesses)){
	// 		$r .= dv('padded').lnk(translate('Create a theme'),'theme/create',array(),array('class'=>'button')).xdv();
	// 	}
	// 	$col = new Collection('Theme');
	// 	$col->uid = App::$user->id;
	// 	$r .= $col->content('preview',0,999);
	// 	$col = new Collection('Theme');
	// 	$col->public = 1;
	// 	$col->uid('!=',App::$user->id,true);
	// 	$r .= $col->content('preview',0,999);
	// 	$r .= '<br class="clearfloat"/>'.xdv();
		
	// 	$r .= xdv();
	// 	return $r;
	// }
	
	// protected function stats(){
	// 	return '<h3>Please add your Google Analytics code in your settings</h3>The in house is not yet available...';
	// }
	
	// protected function edit(){
	// 	$r = '';
	// 	T::$page['title'] = translate('Add content');
	// 	$r .= dv('','siteEditor','data-siteid="'.$this->id.'" data-relativepath="'.$this->relative_path.'"');
	// 	$r .= dv('navigation','pages');
	// 	$r .= dv('tab all');
	// 	$r .= dv('denomination').translate('pages').xdv();
	// 	if(empty($this->content)){
	// 		$active = 'add';
	// 	}
	// 	else{
	// 		$c = reset($this->content);
	// 		$active = $c->u;
	// 		foreach($this->content as $page){
	// 			$yesno = array('O'=>true,'X'=>false);
	// 			if($page->x == 1){
	// 				$c = 'section';
	// 				if(count($page->c)==0){$c .= ' empty';}
	// 				$r .= dv('tab drag '.$c,'','data-u="'.$page->u.'"');
	// 				$r .= dv('denomination').'<span class="ajax lnk" data-containerid="pagelet" data-gethtml="true" data-url="'.HOME.$this->relative_path.'editcontent/'.$page->u.'"><img src="/img/site_editor/section.png" alt="Section"/ class="icon">'.shorten($page->t,12).'</span>'.xdv();
	// 				if(empty($page->c)){
	// 					$r .= dv('el drag placeholder').'<img src="/img/site_editor/page.png" alt="Page" class="icon"/><span class="dragme">dummy</span>'.xdv();
	// 				}
	// 				else{
	// 					foreach($page->c as $sub){
	// 						$c='';
	// 						if(isset($_REQUEST['page']) && $sub->u==$_REQUEST['page']){$active = $sub->u;$c.=' active';}
	// 						$r .= dv('el drag ajax lnk'.$c,'','data-url="'.HOME.$this->relative_path.'editcontent/'.$sub->u.'" data-u="'.$sub->u.'" data-containerid="pagelet" data-gethtml="true"').'<img src="/img/site_editor/page.png" alt="Page" class="icon"/>'.shorten($sub->t,12).xdv();
	// 					}
	// 				}
	// 				$r .= xdv();
	// 			}
	// 			else{
	// 				$c='';
	// 				if(isset($_REQUEST['page']) && $page->u==$_REQUEST['page']){$active = $page->u;$c.=' active';}
	// 				$r .= dv('el drag ajax lnk'.$c,'','data-url="'.HOME.$this->relative_path.'editcontent/'.$page->u.'" data-u="'.$page->u.'" data-containerid="pagelet" data-gethtml="true"').'<img src="/img/site_editor/page.png" alt="Page" class="icon"/>'.shorten($page->t,12).xdv();
	// 			}
	// 		}
	// 	}
	// 	$r .= xdv();
	// 	$r .= dv('ajax lnk','','data-url="'.HOME.$this->relative_path.'addpage" data-containerid="pagelet" data-gethtml="true"').'<span class="bold">+'.translate('add page').'</span>'.xdv();
	// 	$r .= dv('ajax lnk','','data-url="'.HOME.$this->relative_path.'addsection" data-containerid="pagelet" data-gethtml="true"').'<span class="bold">+'.translate('add section').'</span>'.xdv();
	// 	$r .= xdv();
	// 	$r .= xdv();
	// 	$r .= dv('organize','pagelet');
	// 	if($active=='add'){
	// 		$r .= $this->addpage();
	// 	}
	// 	else{
	// 		$r .= $this->editcontent($active);
	// 	}
	// 	$r .= xdv().'<br class="clearfloat"/>';
	// 	$r .= xdv();
	// 	return $r;
	// }
	
	// protected function addsection(){
	// 	$form = new Form('Site',$this->id,$this->relative_path.'edit',array('insert'=>true,'class'=>'full'));
	// 	$form->{'page_add_t'}('input',translate('title'),false);
	// 	$form->{'page_add_d'}('hidden','');
	// 	$form->{'page_add_x'}('hidden',1);
	// 	$form->{translate('create')}('submit');
	// 	return $form->returnContent();
	// }
	
	// protected function addpage(){
	// 	$r = '';
	// 	if(empty($this->content)){
	// 		$r .= '<h3>'.translate('Create your first page.').'</h3>';
	// 	}
	// 	$form = new Form('Site',$this->id,$this->relative_path.'edit',array('insert'=>true,'class'=>'full'));
	// 	$form->{'page_add_t'}('input',translate('title'),false);
	// 	$form->{'page_add_d'}('textarea',translate('Description'),translate('Good for google etc.'));
	// 	$form->{'page_add_x'}('hidden',2);
	// 	$form->{'page_add_m'}('hidden',true);
	// 	$form->{translate('create')}('submit');
	// 	return $r.$form->returnContent();
	// }
	
	// protected function editcontent($page=''){
	// 	if(empty($this->content)){
	// 		return '<h2>No pages - '.lnk(translate('Add one'),$this->relative_path.'addpage',array(),array('data-type'=>'popup')).'</h2>';
	// 	}
	// 	$r = '';
	// 	if(!$this->pageExists($page,$edit)){
	// 		return '<h2>Wrong page: '.$page.'</h2>';
	// 	}
	// 	if(empty($edit->c)){$edit->c=array();}
	// 	if(!isset($edit->v)){$edit->v=0;}
	// 	if($edit->x == 6 &&!is_array($edit->c)){$edit->c = array(0=>array('html',$edit->c));}
	// 	if($edit->x==1){
	// 		$r .= dv('head');
	// 		$r .= dv('toolbox').'<span class="update editable title" id="update_Site_'.$this->id.'_page_'.$edit->u.'_t">'.$edit->t.'</span>'.xdv();
	// 		$r .= dv('toolbox right').lnk('<img src="/img/tool-delete.png" height="36" alt="delete" title="delete"/>',$this->relative_path.'edit',array('update[Site]['.$this->id.'][removepage]'=>$edit->u),array('ajax'=>true,'data-type'=>'confirm','data-matter'=>translate('Really delete this page?'))).xdv();
			
	// 		$table = new Table();
	// 		$r .= dv('site options');
	// 		$r .= '<br class="clearfloat"/>'.xdv();
	// 		$r .= '<h4>This is a category page containing '.count($edit->c).' pages</h4>';
	// 		$r .= dv('padder');
	// 		foreach($edit->c as $p){
	// 			$r .= dv('subpage').$this->pageoptions($p->u);
	// 			$r .= '<br class="clearfloat"/>'.xdv();
	// 		}
	// 		$r .= xdv();
	// 	}
	// 	else{
	// 		$r .= $this->pageoptions($page).$this->selectables($edit->u);
	// 	}
	// 	return $r;
	// }
	
	// private function pageoptions($page){
	// 	$r = '';
	// 	if(!$this->pageExists($page,$edit)){
	// 		return '<h2>Wrong page: '.$page.'</h2>';
	// 	}
	// 	$r .= dv('head');
	// 	$r .= dv('toolbox').'<span class="update editable title" id="update_Site_'.$this->id.'_page_'.$edit->u.'_t">'.$edit->t.'</span>'.xdv();
	// 	$r .= dv('toolbox right').lnk('<img src="/img/tool-delete.png" height="36" alt="delete" title="delete"/>',$this->relative_path.'edit',array('update[Site]['.$this->id.'][removepage]'=>$edit->u),array('ajax'=>true,'data-type'=>'confirm','data-matter'=>translate('Really delete this page?'))).xdv();
	// 	$r .= dv('toolbox right').dv('viewmode');
	// 	$c = 'vertical';
	// 	if(!isset($edit->v)){$edit->v=0;}
	// 	if($edit->v == 0){$c.=' active';}
	// 	$r .= lnk('vertical','#',array('update[Site]['.$this->id.'][page]['.$edit->u.'][v]'=>0),array('class'=>$c));
		
	// 	$c = 'horizontal';
	// 	if($edit->v == 1){$c.=' active';}
	// 	$r .= lnk('horizontal','#',array('update[Site]['.$this->id.'][page]['.$edit->u.'][v]'=>1),array('class'=>$c));
		
	// 	$c = 'thumbs';
	// 	if($edit->v == 2){$c.=' active';}
	// 	$r .= lnk('thumbs','#',array('update[Site]['.$this->id.'][page]['.$edit->u.'][v]'=>2),array('class'=>$c));
		
	// 	$c = 'full';
	// 	if($edit->v == 3){$c.=' active';}
	// 	$r .= lnk('full','#',array('update[Site]['.$this->id.'][page]['.$edit->u.'][v]'=>3),array('class'=>$c));
	// 	$r .= xdv().xdv();
	// 	$r .= dv('toolbox right').'<span class="padded">'.swtch('page_'.$edit->u.'_m',array('Visible'=>'1','Hidden'=>'0'),$edit->m,'Site',$this->id).'</span>'.xdv();
	// 	$r .= dv('toolbox right').lnk('<img src="/img/site_editor/add_photo.png" height="40" title="Add photos"/>',$this->relative_path.'addcontent',array('page'=>$edit->u),array('data-type'=>'popup')).xdv();
	// 	$r .= '<br class="clearfloat"/>'.xdv();
	// 	return $r;
	// }
	
	// private function selectables($page){
	// 	$r = '';
	// 	if(!$this->pageExists($page,$edit)){
	// 		return '<h2>Wrong page: '.$page.'</h2>';
	// 	}
	// 	if($edit->x == 4){
	// 		return 'Wrong page type';
	// 	}
	// 	else{
	// 		$r .= dv('','pageEditor','data-siteid="'.$this->id.'" data-u="'.$page.'"');
	// 		if(!is_array($edit->c)){$edit->c = array(array($edit->c,'html'));}
	// 		foreach($edit->c as $pos=>$item){
	// 			if(!Object::objectExists(ucfirst($item[0]),$item[1])){
					
	// 			}
	// 			else{
	// 				switch(strtolower($item[0])){
	// 					case 'photo':
	// 						$photo = new Photo($item[1]);
	// 						$r .= $photo->selectable('smallthumb',false,array(),lnk('<img src="/img/site_editor/delete.png" height="16" alt="X" title="Delete"/>','#',array('update[Site]['.$this->id.'][page]['.$edit->u.'][rmv]'=>'Photo_'.$photo->id,'method'=>'site_editor')));
	// 					break;
						
	// 					case 'article':
	// 						$article = new Article($item[1]);
	// 						$r .= $article->display('selectable',array('smallthumb','',array(),lnk('<img src="/img/site_editor/delete.png" height="16" alt="X" title="Delete"/>','#',array('update[Site]['.$this->id.'][page]['.$edit->u.'][rmv]'=>'Article_'.$article->id))));
	// 					break;
						
	// 					case 'stack':
	// 						$stack = new Stack($item[1]);
	// 						$r .= $stack->selectable('',lnk('<img src="/img/site_editor/delete.png" height="16" alt="X" title="Delete"/>','#',array('update[Site]['.$this->id.'][page]['.$edit->u.'][rmv]'=>'Stack_'.$stack->id))).xdv();
	// 					break;
						
	// 					case 'html':
	// 						$f = new Form('Site',$this->id,'edit',array('class'=>'full editor'));
	// 						$f->{'page_'.$edit->u.'_c_'.$pos}('textarea',$item[1],false);
	// 						$f->{translate('save')}('submit');
	// 						$r .= $f->returnContent();
	// 					break;
	// 				}
	// 			}
	// 		}
	// 		$r .= xdv();
	// 	}
	// 	return $r;
	// }
	
	// protected function addcontent($page=''){
	// 	$r = '';
	// 	$max = 280;
	// 	if(isset($_REQUEST['page'])){$page = $_REQUEST['page'];}
		
	// 	if(!$this->pageExists($page,$edit) || $edit->x != 2){Msg::notify('Wrong page ['.$page.'].');return false;}
		
	// 	if(!isset($_REQUEST['loadmore'])){
	// 		$r .= dv('tabs');
	// 		$q = 'SELECT `genre` FROM `Photo` WHERE `uid` = "'.App::$user->id.'" GROUP BY `genre`';
	// 		if(!LP::$db->customQuery($q,$d)){$this->error('Failed to load genres.',90);}
	// 		$genres=array();
	// 		$g->any = translate('any');
	// 		foreach($d as $genre){
	// 			if(!empty($genre['genre']))$g[$genre['genre']] = $genre['genre'];
	// 		}
	// 		$form = new Form('','',true,array('class'=>'inline'));
	// 		$form->genre('select',$g,false,'','','$(\'#selectPhotos\').attr(\'data-genre\',this.value);query(HOME+\''.$this->relative_path.'addcontent\',{gethtml:1,genre:this.value,loadmore:true,page:\''.$edit->u.'\'},$(\'#selectPhotos\'));');
	// 		$r .= $form->returnContent();
	// 		$r .= xdv();
	// 		$r .= dv('','selectPhotos','data-gethtml="1" data-s="'.($_REQUEST['s']+1).'"');
	// 	}
	// 	$col = new Collection('Photo');
	// 	$skip=array();
	// 	if(empty($edit->c)){$edit->c=array();}
	// 	foreach($edit->c as $item){
	// 		if(ucfirst($item[0]) == 'Photo'){$skip[] = $item[1];}
	// 	}
	// 	$col->id('NOT IN',$skip,true);
	// 	$col->uid=App::$user->id;
	// 	if(!isset($_REQUEST['genre'])){$_REQUEST['genre']='any';}
	// 	if($_REQUEST['genre']!='any'){
	// 		$col->genre=$_REQUEST['genre'];
	// 	}
	// 	$col->load($_REQUEST['s']*$max,$max,true);
	// 	foreach($col->results as $photo){
	// 		$r .= $photo->selectable('smallthumb','ajax',array('page'=>$edit->u,'update[Site]['.$this->id.'][page]['.$edit->u.'][add]'=>'Photo_'.$photo->id,'method'=>'site_editor'));
	// 	}
	// 	return $r;
	// }
	
	public function folder(){
		//Where profile and wall post photos are stored (Like local/files/UID/site/TARGET)
		return 'site/'.$this->id;
	}
	
	// protected function preview(){
	// 	if(empty($this->content) || $this->channel > 2){return NULL;}
	// 	$r = '<div class="preview previewBox marger softlink" data-url="'.$this->url.'" id="siteid_'.$this->id.'" title="'.$this->title.'">';
	// 	$r .= '<h3>'.$this->title.'</h3><br/>';
	// 	$r .= '<div class="content">'.xtlnk('http://'.$this->url,$this->generateCover(3,'stack')).'</div>';
	// 	$r .= '</div>';
	// 	return $r;
	// }
	
	// protected function link(){
	// 	if(empty($this->content) || $this->channel > 2){return NULL;}
	// 	return dv('siteLink').xtlnk('http://'.$this->url,$this->generateCover(1,'smallthumb')).'<br/>'.$this->title.xdv();
	// }
	
	protected function generateCover($max = 8,$s='thumb'){
		if(empty($this->content) || !is_array($this->content)){return translate('No content.');}
		$r='';
		$i=0;
		foreach($this->content as $folder){
			if(isset($folder->c) && is_array($folder->c) && !empty($folder->c)){
				foreach($folder->c as $pos=>$file){
					if(isset($file[0]) && strtolower($file[0]) == 'photo' && $i < $max && !empty($file[1])){
						$p=new Photo($file[1]);
						$r .= $p->img($s);
						$i++;
					}
				}
			}
			if(isset($folder->x) && is_array($folder->c) && $folder->x == 1){
				foreach($folder->c as $pos=>$page){
					if(isset($page->c) && is_array($page->c) && !empty($page->c)){
						foreach($page->c as $pos=>$file){
							if(isset($file[0]) && strtolower($file[0]) == 'photo' && $i < $max && !empty($file[1])){
								$p=new Photo($file[1]);
								$r .= $p->img($s);
								$i++;
							}
						}
					}
				}
			}
		}
		if(empty($r)){return translate('No content.');}
		return $r;
	}
	
	// protected function adminmenu($pageurl=''){
	// 	$r = '';
	// 	$r .= dv('','adminmenu');
	// 	if(App::$user->id == 0){
	// 		$r .= lnk('<img src="/img/site_editor/login.png" alt="'.translate('login').'" title="'.translate('login').'"/>','login',array(),array('data-type'=>'popup'));
	// 	}
	// 	elseif(App::$user->id == $this->uid){
	// 		$r .= lnk('<img src="/img/site_editor/edit.png" alt="'.translate('Edit content').'" title="'.translate('Edit content').'"/>','edit',array('page'=>$pageurl),array('id'=>'editBtn'));
	// 		$r .= lnk('<img src="/img/site_editor/stats.png" alt="'.translate('Views stats').'" title="'.translate('Views stats').'"/>','stats',array(),array('id'=>'statBtn'));
	// 		$r .= lnk('<img src="/img/site_editor/settings.png" alt="'.translate('Settings').'" title="'.translate('Settings').'"/>','settings',array(),array('id'=>'settingsBtn'));
	// 		$r .= lnk('<img src="/img/site_editor/logout.png" alt="'.translate('Sign out').'" title="'.translate('Sign out').'"/>','logout',array(),array());
	// 	}
	// 	else{
	// 		//follow button
	// 	}
	// 	$r .= xdv();
	// 	return $r;
	// }
	
	// public function menu($pageurl=''){
	// 	if(IS_AJAX){return '';}
	// 	$r = '';
	// 	if(!isset(self::$menus[$this->settings->m])){$this->settings->m=2;}
	// 	$r .= $this->adminmenu($pageurl);
	// 	$c='';
	// 	$r .= dv($this->menuClass,'menu');
	// 	if(!empty($this->settings->l)&&preg_match('/[0-9]/',$this->settings->l) && Object::objectExists('File',$this->settings->l)){
	// 		$file = new File($this->settings->l);
	// 		$logo = lnk('<img src="'.$file->url().'" class="siteLogo" title="'.$this->title.'" alt="'.$this->title.'"/>');
	// 	}
	// 	elseif(!empty($this->settings->l)&&isLink($this->settings->l)){
	// 		$logo = lnk('<img src="'.HOME.$this->settings->l.'" title="'.$this->title.'" alt="'.$this->title.'"/>');
	// 	}
	// 	else{
	// 		$logo = lnk($this->title);
	// 	}
	// 	if($this->url=='okchannel.fr'){
	// 		T::$icon = 'img/6906.ico';
	// 		T::$leftColumn = true;
	// 		T::$rightColumn = true;
	// 		T::$lcol[] = xtlnk('http://twitter.com/OKChannelmusic','<img src="'.HOME.'img/social/twitter-bird3.png" alt="Twitter" class="social"/>',array('rel'=>'nofollow'));
	// 		T::$lcol[] = xtlnk('http://www.facebook.com/pages/OK-Channel/167705156577337?fref=pixyt','<img src="'.HOME.'img/social/facebook-logo.png" alt="Facebook" class="social" />',array('rel'=>'nofollow'));
	// 		T::$lcol[] = xtlnk('http://soundcloud.com/ok-channel','<img src="'.HOME.'img/social/soundcloud.png" alt="Soundcloud" class="social" />',array('rel'=>'nofollow'));
	// 	}
	// 	elseif(!empty($this->settings->i) && is_numeric($this->settings->i)){
	// 		$file = new File($this->settings->i);
	// 		T::$icon = $file->path();
	// 	}
	// 	elseif($this->represented != 0){
	// 		T::$icon = 'img/lightarchitect.ico';
	// 	}
	// 	else{
	// 		T::$icon = 'img/pixyt.ico';
	// 	}
	// 	//if(!User::$isMobile)
	// 	$r .= '<h1 class="logo">'.$logo.'</h1>';
	// 	foreach($this->content as $pageid=>$page){
	// 		if(!isset($page->m)){$page->m=true;}
	// 		if($page->m === true){
	// 			$c = 'menuItem';
	// 			if($page->u == $pageurl){$c.=' active';}
	// 			if(isset($page->x) && $page->x == 1){//submenu
	// 				$submenuId = randStr().time();
	// 				$r .= '<span class="'.$c.' getSubmenu" data-submenuId="'.$submenuId.'">'.lnk($page->t,$page->u,array(),array('title'=>$page->d));
	// 				$r .= '<span class="submenu" id="'.$submenuId.'">';
	// 				foreach($page->c as $subpage){
	// 					if(!isset($subpage->t)){break;}
	// 					if($subpage->m !== true){break;}
	// 					$r .= '<span class="subMenuItem">'.lnk($subpage->t,$subpage->u,array(),array('title'=>$subpage->d)).'</span>';
	// 				}
	// 				$r .= '</span></span>';
	// 			}
	// 			else{
	// 				if(!isset($page->d)){$page->d='';}
	// 				$r .= '<span class="'.$c.'">'.lnk($page->t,$page->u,array(),array('title'=>$page->d)).'</span>';
	// 			}
	// 		}
	// 	}
	// 	if(false&&User::$users[$this->uid]->settings->merchant == 1){
	// 		if(!empty($_SESSION['cart'])){
	// 			T::$rcol[] = lnk('<img src="'.HOME.'/img/cart.jpg" height="16px" style="opacity:0.1;" alt="cart" title="'.translate('Your shopping cart').'"/> '.translate('cart').' ('.count($_SESSION['cart']).')','checkout/cart',array(),array('title'=>translate('Review and edit your cart.'),'class'=>'btn'));
	// 		}
	// 		if(!isset($_SESSION['access'])){$_SESSION['access']=array();}
	// 		if(!empty($_SESSION['access']) || isset($_REQUEST['accesscode'])){
	// 			T::$rcol[] = lnk(translate('My photos'),'access',array(),array('class'=>'btn'));
	// 			T::$rcol[] = dv('assistance').'<h3>'.translate('need help?').'</h3><p>+33(0) 6 65 53 80 02</p><p>'.xtlnk('mail@lightarchitect.com').'</p>'.xdv();
	// 		}
	// 		else{
	// 			$form = new Form('','','access','','','inlineForm');
	// 			$form->accesscode('input',translate('access code'),false);
	// 			$form->ok('submit');
	// 			T::$rcol[] = dv('accesscode').$form->returnContent().xdv();
	// 		}
	// 	}
	// 	$r .= xdv();
	// 	return $r;
	// }
	
	// protected function fullView($pageurl=''){
	// 	$r = '';
	// 	//T::$jsfoot[] = 'if($(window).height() > $("#mainColumn").height()){$("#mainColumn").css({height:$(window).height()});}initScroll("#mainColumn");';
	// 	if($pageurl==''){$pages = $this->content;$first = reset($pages);$pageurl=$first->u;}
	// 	if(!$this->pageExists($pageurl,$this->cur)){Msg::addMsg(404);return;}
	// 	if(isset($this->cur->bgd)){$r .= dv('background').$this->cur->bgd.xdv();}
	// 	T::$page['title'] = $this->cur->t.' | '.$this->title;
		
	// 	if(!IS_AJAX && !empty($this->modules)){
	// 		foreach($this->modules as $id=>$module){
	// 			switch($module->x){
	// 				case 'player':
	// 					T::$jsincludes[] = 'jQuery.jPlayer.2.1.0/jquery.jplayer.min.js';
	// 					T::$jsincludes[] = 'jQuery.jPlayer.2.1.0/add-on/jplayer.playlist.min.js';
	// 					$player = '';
	// 					$player .= '<a href="javascript:;" onclick="$(\'#module_'.$id.'_player\').jPlayer(\'play\')" class="play">play</a>';
	// 					$list = array();
	// 					foreach($module->c as $item){
	// 						if($item[0] == 'Song'){
	// 							$s = new Song($item[1]);
	// 							$file = new File($s->fileid);
	// 							$list[] = array('id'=>$s->id,'title'=>$s->title.' ('.$s->length.')','artist'=>$s->artist,'mp3'=>$file->url());
	// 						}
	// 					}
	// 					T::$rcol[] = playerHTML();
	// 					T::$jsfoot[] = playerJS($list);
	// 				break;
	// 			}
	// 		}
	// 	}
	// 	if(!isset($this->cur->x)){$this->cur->x=2;}
	// 	if($this->cur->x == 1){
	// 		$r .= dv('subindex');
	// 		$page = reset($this->cur->c);
	// 		if(isset($page->u)){
	// 			return $this->fullView($page->u);
	// 		}
	// 		$r .= xdv();
	// 	}
	// 	elseif($this->cur->x == 2){
	// 		if(empty($this->content)){
	// 			$r .= dv('item').'<h2>Online portfolio</h2><h3>'.translate('No photos').'</h3><p>This user has not yet shared any content on his website.</p>'.xdv();
	// 		}
	// 		else{
	// 			$r .= $this->portfolio($pageurl);
	// 		}
	// 	}
	// 	elseif($this->cur->x == 3){
	// 		$r .= $this->shop();
	// 	}
	// 	elseif($this->cur->x == 4){
	// 		$r .= $this->contact();
	// 	}
	// 	elseif($this->cur->x == 5){
	// 		$r .= $this->client();
	// 	}
	// 	elseif($this->cur->x == 6){
	// 		$r .= $this->custom();
	// 	}
	// 	return $r;
	// }
	
	// protected function photo($id='',$page=''){
	// 	$id=intval($id);
	// 	if(!$this->pageExists($page,Site::$current->cur)){return '<h2>That page does not exists ('.$page.')</h2>';}
	// 	if(empty($id)||!Object::objectExists('Photo',$id)){Msg::addMsg(404);return;}
	// 	$obj = new Photo($id);
	// 	T::$page['title'] = $obj->title.' | '.$this->title.' | Full Photo';
	// 	return dv('item centerText').$obj->showImage($page).xdv();
	// }
	
	// protected function article($id=''){
	// 	$id=intval($id);
	// 	if(empty($id)||!Object::objectExists('Article',$id)){Msg::addMsg(404);return;}
	// 	$obj = new Article($id);
	// 	$com = '';$tags='';
	// 	$t = $obj->title.' '.translate('by').' '.User::$users[$this->uid]->fullName('full');
	// 	$url = HOME.'article/'.$id;
	// 	return dv('item').'<h1>'.$obj->title.'</h1>'.dv('content large centerText').frmt($obj->content).xdv().$tags.$com.xdv();
	// }
	
	// protected function vertical(){
	// 	$r = '';
	// 	if(empty($this->cur->c) || !is_array($this->cur->c)){return 'Invalid call.';}
	// 	$r .= dv('d600 center');
	// 	foreach($this->cur->c as $item){
	// 		$o = ucfirst($item[0]);
	// 		if(!in_array($o,Object::$objectTypes)){$this->error('Wrong objecttype ['.$o.'] in Site::vertival()');break;}
	// 		if(Object::objectExists($o,$item[1])){
	// 			$obj = new $o($item[1]);
	// 			switch($o){
	// 				case 'Photo':
	// 					$r .= dv('item photo_item').'<h3>'.$obj->title.'</h3>'.$obj->img('medium').xdv();
	// 				break;
					
	// 				case 'Article':
	// 					$r .= dv('item article_item').'<h3>'.$obj->title.'</h3>'.frmt($obj->content).xdv();
	// 				break;
					
	// 				case 'Stack':
	// 					$_REQUEST['content'] = 'Stack_'.$item[1];
	// 					if($this->validateData('publish',$this->cur->u)){
	// 						$this->pageRemoveObject($this->cur->u,'Stack',$item[1]);
	// 						$this->update();
	// 					}
	// 					else{
	// 						$r .= '<p class="grey quote padder">Please remove this stack from the site then republish it.</p>';
	// 					}
	// 				break;
	// 			}
	// 		}
	// 	}
	// 	$r .= xdv();
	// 	return $r;
	// }
	
	// protected function horizontal(){
	// 	$r = '';
	// 	if(empty($this->cur->c) || !is_array($this->cur->c)){return 'Invalid call.';}
	// 	$table = new Table('photoLine');
	// 	$m = array();
	// 	$d = array();
	// 	foreach($this->cur->c as $item){
	// 		$o = ucfirst($item[0]);
	// 		if(!in_array($o,Object::$objectTypes)){$this->error('Wrong objecttype ['.$o.'] in Site::vertival()');break;}
	// 		if(Object::objectExists($o,$item[1])){
	// 			$obj = new $o($item[1]);
	// 			switch($o){
	// 				case 'Photo':
	// 					$m[] = $obj->img('horizont');
	// 					$d[] = '<h4>'.$obj->title.'</h4>';
	// 				break;
					
	// 				case 'Article':
	// 					$m[] = dv('padded article_item').dv('h600 d600').urldecode($obj->content).xdv().xdv();
	// 					$d[] = '';
	// 				break;
					
	// 				case 'Stack':
	// 					$_REQUEST['content'] = 'Stack_'.$item[1];
	// 					if($this->validateData('publish',$this->cur->u)){
	// 						$this->pageRemoveObject($this->cur->u,'Stack',$item[1]);
	// 						$this->update();
	// 					}
	// 					else{
	// 						$r .= '<p class="grey quote padder">Please remove this stack from the site then republish it.</p>';
	// 					}
	// 				break;
	// 			}
	// 		}
	// 	}
	// 	$table->addLine($m);
	// 	$table->addLine($d);
	// 	T::$jsfoot[] = 'var s = $(".photoLine");';
	// 	$r .= $table->returnContent();
	// 	return $r;
	// }
	
	// protected function grid(){
	// 	$r = '';
	// 	if(empty($this->cur->c) || !is_array($this->cur->c)){return 'Invalid call.';}
	// 	$r .= dv('d1100 center');
	// 	foreach($this->cur->c as $item){
	// 		$o = ucfirst($item[0]);
	// 		if(!in_array($o,Object::$objectTypes)){$this->error('Wrong objecttype ['.$o.'] in Site::vertival()');break;}
	// 		if(Object::objectExists($o,$item[1])){
	// 			$obj = new $o($item[1]);
	// 			switch($o){
	// 				case 'Photo':
	// 					$r .= lnk($obj->img('thumb'),'photo/'.$item[1].'/'.$this->cur->u);
	// 				break;
					
	// 				case 'Article':
	// 					$r .= dv('item article_item').'<h3>'.$obj->title.'</h3>'.frmt($obj->content).xdv();
	// 				break;
					
	// 				case 'Stack':
	// 					$_REQUEST['content'] = 'Stack_'.$item[1];
	// 					if($this->validateData('publish',$this->cur->u)){
	// 						$this->pageRemoveObject($this->cur->u,'Stack',$item[1]);
	// 						$this->update();
	// 					}
	// 					else{
	// 						$r .= '<p class="grey quote padder">Please remove this stack from the site then republish it.</p>';
	// 					}
	// 				break;
	// 			}
	// 		}
	// 	}
	// 	$r .= xdv();
	// 	return $r;
	// }
	
	// protected function slide(){
	// 	$r = '';
	// 	if(empty($this->cur->c) || !is_array($this->cur->c)){return 'Invalid call.';}
	// 	$r .= dv('fullscreen slider','','data-effect="fade"');
	// 	foreach($this->cur->c as $item){
	// 		$o = ucfirst($item[0]);
	// 		if(!in_array($o,Object::$objectTypes)){$this->error('Wrong objecttype ['.$o.'] in Site::vertival()');break;}
	// 		if(Object::objectExists($o,$item[1])){
	// 			$obj = new $o($item[1]);
	// 			switch($o){
	// 				case 'Photo':
	// 					$r .= dv('slide').$obj->img('full').xdv();
	// 				break;
					
	// 				case 'Article':
	// 					$r .= dv('slide').frmt($obj->content).xdv();
	// 				break;
					
	// 				case 'Stack':
	// 					$_REQUEST['content'] = 'Stack_'.$item[1];
	// 					if($this->validateData('publish',$this->cur->u)){
	// 						$this->pageRemoveObject($this->cur->u,'Stack',$item[1]);
	// 						$this->update();
	// 					}
	// 					else{
	// 						$r .= '<p class="grey quote padder">Please remove this stack from the site then republish it.</p>';
	// 					}
	// 				break;
	// 			}
	// 		}
	// 	}
	// 	$r .= xdv();
	// 	return $r;
	// }
	
	// protected function portfolio($pageurl='',$id=''){
	// 	$r = '';
	// 	if(!isset($this->cur->v)){$this->cur->v=0;}
	// 	if(!is_array($this->cur->c)){
	// 		return dv('item').$this->cur->c.xdv();
	// 	}
	// 	elseif(User::$isMobile){
	// 		return $this->mobile();
	// 	}
	// 	elseif(empty($this->cur->c)){return dv('item').'<h2>Uh oh.. This page is still empty</h2>'.xdv();}
	// 	elseif($this->cur->v==0){return $this->vertical();}
	// 	elseif($this->cur->v==1){return $this->horizontal();}
	// 	elseif($this->cur->v==2){return $this->grid();}
	// 	elseif($this->cur->v==3){return $this->slide();}
	// 	if(!isset($this->settings->v)){$this->settings->v=true;}
	// 	if($this->settings->v){
	// 		$r .= dv('item').'<h1>'.$this->cur->t.'</h1>';
	// 		$r .= '<h2>'.frmt($this->cur->d).'</h2>'.xdv();
	// 	}
	// 	if(!Object::objectExists('Theme',$this->settings->t)){$this->settings->t=1;$this->update(true);}
	// 	$theme = new Theme($this->settings->t);
	// 	$l = count($this->cur->c)-1;
	// 	if(!is_array($this->cur->c)){
	// 		$r .= dv('item').$this->cur->c.xdv();
	// 	}
	// 	else{
	// 		foreach($this->cur->c as $id=>$item){
	// 			if(strtolower($item[0]) == 'photo'){
	// 				if(!Object::objectExists('Photo',$item[1])){
	// 					unset($this->content[$page['id']]->c[$id]);
	// 					$this->update(true);
	// 				}
	// 				else{
	// 					$img = new Photo($item[1]);
	// 					if($img->isDummy){unset($this->content[$page->id]->c[$id]);$this->update(true);}
	// 					else{
	// 						$r .= dv('item').$img->feed($theme->photosize,'',false).xdv();
	// 					}
	// 				}
	// 			}
	// 			elseif(strtolower($item[0]) == 'article'){
	// 				if(!Object::objectExists('Article',$item[1])){
	// 					unset($this->content[$page->id]->c[$id]);
	// 					$this->update(true);
	// 				}
	// 				else{
	// 					$article = new Article($item[1]);
	// 					$r .= dv('item').'<h2>'.$article->title.'</h2>'.dv('permalink').lnk('permalink','article/'.$article->id,array(),array('class'=>'btn')).xdv().dv('content article').frmt($article->content).xdv().xdv();
	// 				}
	// 			}
	// 			else{
	// 				$r .= '<p class="error item">'.translate('Only photos are currently supported in this display mode.').'</p>';
	// 			}
	// 		}
	// 	}
	// 	return $r;
	// }
	
	// protected function mobile(){
	// 	$r = '';
	// 	if(User::$isTablet){$s=1024;}
	// 	else{$s=640;}
	// 	foreach($this->cur->c as $id=>$item){
	// 		if(strtolower($item[0]) == 'photo'){
	// 			if(Object::objectExists('Photo',$item[1])){
	// 				$img = new Photo($item[1]);
	// 				$r .= dv('item').$img->img('large').xdv();
	// 			}
	// 		}
	// 		elseif(strtolower($item[0]) == 'article'){
	// 			if(Object::objectExists('Article',$item[1])){
	// 				$article = new Article($item[1]);
	// 				$r .= dv('item').'<h2>'.$article->title.'</h2>'.dv('content article').frmt($article->content).xdv().xdv();
	// 			}
	// 		}
	// 		else{
	// 			$r .= '<p class="error item">'.translate('Only photos are currently supported in this display mode.').'</p>';
	// 		}
	// 	}
	// 	return $r;
	// }
	
	// protected function custom(){
	// 	if(is_array($this->cur->c) && isset($this->cur->c[0]) && isset($this->cur->c[0][1])){$html = strval($this->cur->c[0][1]);}
	// 	else{$html = strval($this->cur->c);}
	// 	return dv('item').$html.xdv();
	// }
	
	// protected function contact(){
	// 	$r = '';
	// 	$i=0;
	// 	$r .= dv('item contact');
	// 	if(is_array($this->cur->c)){
	// 		foreach($this->cur->c as $o){
	// 			switch(strtolower($o[0])){
	// 				case 'article':
	// 					$article = new Article($o[1]);
	// 					$r .= dv('item').dv('content article').frmt($article->content).xdv().xdv();
	// 				break;
	// 			}
	// 		}
	// 	}
	// 	else{
	// 		if(isset($this->cur->c)&&strlen($this->cur->c)>1){
	// 			$r .= dv('about').frmt($this->cur->c).xdv().'<br class="clearfloat"/>';
	// 		}
	// 	}
	// 	$form = new Form('Message',NULL,'contact',array('class'=>'full'));
	// 	$form->uid('hidden',User::$users[$this->uid]->id);
	// 	if(App::$user->id != 0){
	// 		$form->from('hidden',App::$user->id);
	// 	}
	// 	else{
	// 		$form->from('input',false,translate('your email'));
	// 	}
	// 	$form->content('textarea',translate('What can I help you with?'),false);
	// 	$form->send('submit','','','','grey');
	// 	$r .= $form->returnContent().xdv();
	// 	return $r;
	// }
	
	// public function urlToId($url){
	// 	foreach($this->content as $key => $page){
	// 		if($page->u == $url){
	// 			return $key;
	// 		}
	// 	}
	// 	return false;
	// }
	
	// protected function feed(){
	// 	$cover = $this->generateCover(6,'mediumthumb');
	// 	if(empty($this->content) || !is_array($this->content) || $cover==translate('No content.')){return '';}
	// 	return dv('feed').dv().xtlnk(makeUrl($this->url),$cover).xdv().dv().'<h3 class="right">'.xtlnk(makeUrl($this->url),$this->title).'</h3><br class="clearfloat"/>'.xdv().xdv();
	// }
	
	// protected function styleEditor(){
	// 	$r = '';
	// 	$form = new Form('Site',$this->id,true,array('ajax'=>false,'class'=>'full'));
	// 	foreach(self::$styleProperties as $class=>$properties){
	// 		$form->write('<br/><h3>'.$class.'</h3>');
	// 		if(!isset($this->style[$class])){$this->style[$class]=array();}
	// 		foreach($properties as $property){
	// 			if(!isset($this->style[$class][$property])){$this->style[$class][$property]='';}
	// 			switch($property){
	// 				case 'font-family':
	// 					$opt = array();
	// 					foreach(htmlsafefonts() as $font){
	// 						if($this->style[$class][$property] == $font){$opt[$font] = array($font,$font,true);}
	// 						else{$opt[$font] = $font;}
	// 					}
	// 					$form->{'style_'.$class.'_'.$property}('select',$opt,$property);
	// 				break;
					
	// 				case 'font-size':
	// 					$opt = array();
	// 					for($i=11;$i<31;$i++){
	// 						if($this->style[$class][$property] == $i.'px'){$opt[] = array($i.'px',$i.'px',true);}
	// 						else{$opt[] = array($i.'px',$i.'px',false);}
	// 					}
	// 					$form->{'style_'.$class.'_'.$property}('select',$opt,$property);
	// 				break;
					
	// 				case 'text-transform':
	// 					$opt = array();
	// 					$texttransformations = array('uppercase','lowercase','capitalize');
	// 					foreach($texttransformations as $o){
	// 						if($this->style[$class][$property] == $o){$opt[] = array($o,$o,true);}
	// 						else{$opt[] = array($o,$o,false);}
	// 					}
	// 					$form->{'style_'.$class.'_'.$property}('select',$opt,$property);
	// 				break;
					
	// 				case 'color':
	// 					$form->{'style_'.$class.'_'.$property}('input');
	// 				break;
	// 			}
	// 		}
	// 	}
	// 	$form->{translate('save')}('submit');
	// 	$r .= $form->returnContent();
	// 	return $r;
	// }
	
	// protected function css(){
	// 	$r = '';
	// 	if(!isset($this->settings->c)||!isset($this->colorSchemes[$this->settings->c])){
	// 		$this->settings->c='light';
	// 	}
	// 	foreach($this->colorSchemes[$this->settings->c] as $selector=>$properties){
	// 		$r .= $selector.'{';
	// 		foreach($properties as $property=>$value){
	// 			$r .= $property.':'.$value.';';
	// 		}
	// 		$r .= '}'.PHP_EOL;
	// 	}
	// 	foreach($this->style as $class=>$properties){
	// 		$r .= '.'.$class.'{';
	// 		if(!isset(self::$styleProperties[$class])){unset($this->style[$class]);$this->update(true);}
	// 		foreach($properties as $property=>$value){
	// 			$r .= $property.':'.$value.';';
	// 		}
	// 		$r .= '}'.PHP_EOL;
	// 	}
	// 	return $r;
	// }
	
	//OBJECT RELATED FUNCTIONS
	protected function postUpdate(){
		return true;
	}
	
	protected function postInsert(){
		return true;
	}
	
	protected function postDelete($force = false){
		return true;
	}
}
?>