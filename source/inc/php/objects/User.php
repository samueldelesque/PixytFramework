<?php
class User extends Object{
	//Object values
	public $password;
	public $allocation = 52428800;//50Mo
	public $plan = 'basic';
	public $firstname;
	public $lastname;
	public $email; 
	public $birthday;
	public $gender;
	public $profile;
	/*
		'function'=>'',			//photographer/model/agent/buyer/galery/visitor
		'img'=>'/path/to/img'
		 OBS 'photos'=>array(), 		//in order of change. Last picture is current profile.
		'languages'=>array(),
		'about'=>'',
		'location'=>'',
		'headline'=>'',
		'websites'=>array(),
		'networks'=>array(),
		'phones'=>array(),
		'adresses'=>array(),
		'emails'=>array(),
	*/
	public $settings;
	/*
		//array(IP=>array(loc,time))
		'ip'=>array(),
		'validationCode'=>NULL,
		'recovery'=>array('count'=>0,'code'=>''),
		'emailVerified'=>false,
		//timestamp of last time homepage was loaded
		'homenotif'=>'',
		'mailsettings'=>array('promoted'=>true,'liked'=>true,'commented'=>true,'newsletter'=>true),
		'language'=>DEFAULT_LANG,
		 //add 1 for admin, 2 for comptability, 3 for translate, 4 for promote, 5 for creating themes, 6 for rating
		'accesses'=>array()
	*/
	public $hide=false;
	public $validated;
	
	//misc variables
	private $followers = false;
	private $following = false;
	private $allowPasswordChange = false;
	public static $me;
	public $termsAccepted = false;
	
	//this fields should not be sent to API ever
	public $private_fields = array('password','hide','validated');
	
	public function construct(){
		if(!$this->settings){
			$this->settings = new stdClass;
			$this->settings->language = '';
			$this->settings->validationCode = randStr();
		}
		if(!$this->profile){
			$this->profile = new stdClass;
			$this->profile->function=1;
			$this->profile->headline=NULL;
			$this->profile->location=NULL;
			$this->profile->about=NULL;
			$this->profile->phones=array();
			$this->profile->adresses=array();
			$this->profile->emails=array();
			$this->profile->photos=array();
			$this->profile->websites=array();
			$this->profile->languages=array();
		}
	}
	
	protected static function publicFunctions(){
		return array(
			'fullView'=>true,
			'preview'=>true,
			'tableData'=>true,
			'link'=>true,
		);
	}
	
	protected static function ownerFunctions(){
		return array(
			'edit'=>true,
			'editPreview'=>true,
			'settings'=>true,
			'addexp'=>true,
			'selectphoto'=>true,
			'notify'=>true,
		);
	}
	
	public function hasAccess($area){
		if(!is_object($this->settings)){return false;}
		if(!isset($this->settings->accesses)){$this->settings->accesses=array();}
		//1 for admin, 2 for comptability, 3 for translate, 4 for promote, 5 for creating themes, 6 for rating
		return in_array($area,$this->settings->accesses);
	}
	
	public function descriptor($key=NULL){
		return arrayGetKey($key,array(
			'id'=>'int',
			'validated'=>'bool',
			'password'=>'string',
			'allocation'=>'int',
			'plan'=>'string',
			'firstname'=>'string',
			'lastname'=>'string',
			'email'=>'string',
			'birthday'=>'string',
			'gender'=>'string',
			'profile'=>'object',
			'settings'=>'object',
			'hide'=>'bool',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		));
	}
	
	public static function industryfunctions($key=NULL){
		return arrayGetKey($key,array(
			1=>'photographer',
			2=>'retoucher',
			3=>'model',
			4=>'make-up artist',
			5=>'set designer',
			6=>'creative director',
			//7=>'editor',
			//8=>'videast',
			//9=>'musician',
			10=>'clothe designer',
			11=>'agent',
			//12=>'buyer',
			13=>'gallery',
			//14=>'painter',
			15=>'stylist',
			16=>'publisher',
			//-1=>'visitor',
			-2=>'other',
		));
	}
	
	public function remainingDiskSpace(){
		return round(File::getUsage($this->id,true)/$this->allocation,2);
	}
	
	public function usage(){
		return dv('progress').dv('bar','','style="width:'.($this->remainingDiskSpace()*100).'%"').xdv().xdv();
	}
	
	public function hide(){
		$this->hide = true;
		$this->update();
		unset($_SESSION['uid']);
		T::$page['title'] = translate('Fare well').' '.$this->firstname.'!';
		T::$body[] = dv('xbox').dv('padder').'<h2>'.translate('Your data has been suspended and is no longer accessible. We will remove your files completely upon request.').'</h2>';
		T::$body[] = '<h3>'.translate('We would love to hear why you are leaving the ship, please send us feedback at').' '.xtlnk('mailto:crew@pixyt.com','crew@pixyt.com').'</h3>'.xdv().xdv();
		App::$user = new User();
		return true;
	}
	
	public function tableHead(){
		return array('Full name','','Email','location','from','Language','Last login','Disk usage','Tools');
	}
	
	public function tableData(){
		$btns = '';
		if($this->hide){
			$btns.=lnk('erase',true,array('delete[User]['.$this->id.']'=>true),array('class'=>'btn','data-type'=>'confirm','data-matter'=>translate('Are you 100% sure you wish to delete this data permanently?')));
		}
		if($this->validated >= 100){
			$v = '<img src="/img/validate40.png" alt="validated" title="'.$this->validated.' %"/>';
		}
		else{
			$v = '<img src="/img/unvalidate40.png" alt="not validated" title="'.$this->validated.' %"/>';
		}
		if($this->settings->emailVerified == true){$echeck='green';}else{$echeck='red';}
		$btns.= lnk('<img src="/img/add50Mo.png" alt="increase" title="add 50Mo allocation"/>','admin/increase/'.$this->id,array('allocation'=>52428800),array('ajax'=>true));
		if(!isset($this->settings->from)){$this->settings->from= 'unknown';}
		return array('#'.$this->id.' '.$this->fullName('link'),$v,'<span class="'.$echeck.'">'.$this->email.'</span>',$this->profile->location,$this->settings->from,$this->settings['language'],prettyTime($this->modified),$this->usage(),$btns);
	}
	
	public function birthday(){
		return $this->birthday;
	}
	
	public function age(){
		if(empty($this->birthday) || $this->birthday == '0000-00-00'){return 0;}
		list($year,$month,$day) = explode("-",$this->birthday);
		$year_diff  = date("Y") - $year;
		$month_diff = date("m") - $month;
		$day_diff   = date("d") - $day;
		if ($day_diff < 0 || $month_diff < 0)
		$year_diff--;
		return $year_diff;
	}
	
	public function currentParam($field = 'photos'){
		if(!isset($this->profile->$field)){return false;}
		switch($field){
			case 'locations':
			case 'schools':
			case 'workplaces':
			case 'status':
				if(!is_array($this->profile->$field)){$this->profile->$field = array();}
				return end($this->profile->$field);
			break;
			
			case 'photos':
				if(!is_array($this->profile->$field)){$this->profile->$field = array();}
				if(Object::exists('Photo',end($this->profile->$field))){return end($this->profile->$field);}
				else{
					foreach($this->profile->$field as $photo){
						if(Object::objectExists('Photo',$photo)){return $photo;}
					}
				}
			break;
			
			case 'phones':
			case 'websites':
			case 'adresses':
			case 'emails':
				if(!is_array($this->profile->$field)){$this->profile->$field = array();}
				return end($this->profile->$field);
			break;
			
		}
		return false;
	}
	
	public function validateData($n,$v){
		switch ($n){
			case 'access':
			case 'pwd':
			case 'id':
			case 'year':
			case 'month':
			case 'day':
			case 'created':
			case 'modified':
			case 'deleted':
			case 'hide':
			case 'plan':
			case 'allocation':
			case 'validated':
		    		return true;
		    break;
			
			case 'settings':
				if(!is_object($v)){Msg::notify('Settings must be an object!');return false;}
				foreach($v as $name=>$value){
					$this->settings->$name = $value;
				}
				return true;
			break;
			
		   	case 'birthday':
				if(is_array($v)){$v=$v['year'].'-'.$v['month'].'-'.$v['day'];}
				$time = strtotime($v);
				$t=time();
				$age = ($time < 0) ? ($t + ($time * -1) ) : ($t - $time);
				$year = 60 * 60 * 24 * 365.25;
				if($age/$year >= 13){$this->$n=$v;return true;}
		   		else{Msg::notify(translate('You must be over 13 to create an account.'));}
			break;
			
			case 'password':
				$this->$n=hash('SHA256',SITE_KEY.$v);
				$this->settings->recovery = array('count'=>0,'code'=>randStr());
				return true;
			break;
			
			case 'curpassword':
				if(empty($v)){return true;}//No changes
				if(hash('SHA256',SITE_KEY.$v) == $this->password						//(default login)
					|| $v == $this->password											//(called from User class)
					|| hash('SHA256',SITE_KEY.hash('SHA256',$v)) == $this->password		//(JSKrypt login)
				){
					$this->allowPasswordChange=true;
					return true;
				}
				Msg::notify(translate('Wrong password.'));
			break;
			
			case 'newpassword':
				if(empty($v)){return true;}//No changes
				if(App::$user->id == $this->id){
					if(strlen($v) < 5){
						Msg::addMsg(translate('You password must be at least 5 characters long!'));
						return false;
					}
					elseif($this->allowPasswordChange){
						$this->password=hash('SHA256',SITE_KEY.$v);
						Msg::addMsg('Password Updated!');
						return true;
					}
				}
				Msg::addMsg(translate('You are not authorized to do that.'));
			break;
			
			case 'email':
				$v = strtolower($v);
				if(isEmail($v)){
					if($this->email == $v){
						return true;
					}
					elseif(Object::objectExists('User',$v,'email')){
						Msg::notify(translate('Email is already in use. To log in, please go to log in page.'));
						$this->isDummy = true;
						return false;
					}
					else{
						$this->$n=$v;
						return true;
					}
				}
				else{
					Msg::notify(translate('Please enter a valid email.'));
				}
			break;
			
			case 'gender':
			   	$this->$n=$v;
				return true;
			break;
			
			case 'firstname':
			case 'lastname':
				if(strlen($v) == 0){return false;}
				if(strlen($v) > 255){Msg::notify(t('Firstname and lastname may not exceed 255 chars.'));return false;}
				$this->$n=ucfirst($v);
				return true;
			break;
			
			case 'profile':
				if(!is_object($v)){
					Msg::notify('Profile not an object!',40);
					return false;
				}
				else{
					foreach($v as $name=>$value){
						$validate = true;
						switch($name){
							case 'about':
								if(strlen($value) < 900){	
									$this->profile->about = $value;
								}
								else{
									Msg::addMsg(translate('Bio may not exceed 900 chars.'));
									$validate = false;
								}
							break;
									
							case 'location':
								if(strlen($value) > 255){Msg::notify(translate('Location may not exceed 255 chars.'));$validate = false;}
								else{$this->profile->location=$value;}
							break;
							
							case 'headline':
								if(strlen($value) > 255){
									Msg::notify(t('Headline may not exceed 255 chars.'));
									$validate = false;
								}
								else{$this->profile->headline=$value;}
							break;
									
							case 'function':
								$this->profile->function = $value;
							break;
							
							default:
								Msg::notify($n.' is was not recognised.');
							break;
						}
					}
					return $validate;
				}
			break;
		}
		return false;
	}
		
	public function emailPreferences(){
		/*
		 * 1:PM,   2:Notifications....
		 *
		 */
		if(empty($this->emailPreferences)){return array();}
		return explode(',',$this->emailPreferences);
	}
	
	public function fullAdress(){
		return $this->zipCode.' '.$this->city;
	}
	
	public function folder(){
		//Where profile and wall post photos are stored (Like local/files/UID/user/TARGET)
		return 'user/'.$this->id;
	}
	
	protected function selectphoto(){
		$r = '';
		$col = new Collection('Photo');
		$col->where('uid',$this->id);
		$col->where('channel',6);
		$r .= dv('smallUploadBox','profilePhotoUploader').Photo::uploadBtn(6,$this->id,'','profilePhotoUploader').xdv();
		$r .= dv('separator');
		$col->load(0,50);
		foreach($col->load() as $photo){
			$r .= $photo->selectable('stack',true,array('update[User]['.$this->id.'][profile][usephoto]'=>$photo->id));
		}
		$r .= xdv();
		return $r;
	}
	
	protected function link(){
		return $this->fullName('link').' ';
	}
	
	public function fullName($grammaticalForm = 'noun'){
		if($this->id == 0){return 'guest';}
	    switch($grammaticalForm){
			case 'url':
				$url = prettyUrl(strtolower($this->firstname.$this->lastname));
				
				$accept = false;
				while($accept === false){
					$query = 'SELECT COUNT(*) FROM Url WHERE `url` = "'.$url.'"';
					if(!self::$db->customQuery($query,$data)){
						$this->error('Failed to get user url',90);
						return false;
					}
					$exists = $data[0]['COUNT(*)'];
					if((int)$exists < 1){$accept = true;}
					else{$url .= '2';}
				}
				return $url;
			break;
			
			case 'full':
				return ucfirst($this->firstname).' '.ucfirst($this->lastname);
			break;
			
			case 'adj':
			case 'adjective':
				if($this->id == App::$user->id){return translate('your');}
				return ucfirst($this->firstname).' '.ucfirst($this->lastname).'\'s';
			break;
			
			case 'link':
				$n=strtolower($this->firstname.' '.$this->lastname);
				if(HOST!='pixyt.com'){return '<span class="capitalize">'.xtlnk('http://pixyt.com/user/'.$this->id,$n).'</span>';}
				return lnk('<span class="capitalize">'.$n.'</span>','user/'.$this->id);
			break;
			
			case 'authorLink':
				return '//pixyt.com/user/'.$this->id;
			break;
			
			case 'noun':
			default:
				if($this->id == App::$user->id){return translate('you');}
				return ucfirst($this->firstname).' '.ucfirst($this->lastname);
			break;
	    }
	}
	
	public function setNewPassword($code){
		if($this->isDummy){return false;}
//		if(isset($this->vars->{$this->id.'.recovery.code'}) && $this->vars->{$this->id.'.recovery.code'} == $code){
		if($this->settings->recovery['count'] > 0 && isset($this->settings->recovery['code']) && !empty($code) && $this->settings->recovery['code'] == $code){
			if(!self::login($this->email,$this->password,true)){
				$this->error('Failed to auto-login user for setting new password',90);
			}
			$form = new Form('User',$this->id,'account/settings');
			$form->password('password',1,translate('Password'));
			$form->{translate('set password')}('submit');
			return '<div class="info">'.$form->returnContent().'</div>';
		}
		else{
			return '<p class="pattern padder">'.translate('The code given does not match the verification code on the account (if you sent several recovery codes, please make sure to use the last received email).').'</p>';
		}
	}
	
	public function sendPasswordRecoveryCode(){
		$code = randStr();
		if(!isset($this->settings->recovery)){$this->settings->recovery = array('count'=>1,'code'=>$code);}
		else{$this->settings->recovery = array('count'=>($this->settings->recovery['count']+1),'code'=>$code);}
		$this->update();
		if($this->settings->recovery['count'] > 10){
			T::$body[] = '<p class="pattern">'.translate('You have reached the maximum tries for password recovery. If you believe you should try once again, please contact admin.').'</p>';
			return false;
		}
		else{
			$msg = translate('You have requested a new password. To confirm press here: ');
			$msg .= xtlnk('http://pixyt.com/passwordrecovery?uid='.$this->id.'&code='.$code,translate('Recover password'),array('class'=>'btn'));
			$this->notify($msg,'recover',translate('Recover your password'));
			return true;
		}
	}
	
	protected function preview(){
		$r = '';
		$r .= dv('preview padded','User_'.$this->id.'_preview');
		$r .=  dv('content').'<h4>'.$this->fullName('link').'</h4>';
		$r .= '<p class="grey">'.translate('joined').' '.prettyTime($this->created).'</p>'.xdv();
		$r .= '<p class="tools">'.lnk(translate('message'),'message',array('to'=>$this->id),array('class'=>'btn')).'</p>';
		$r .= xdv();
		return $r;
	}
	
	public static function loginForm(){
		$r = '';
		$form = new Form(NULL,NULL,'login',array('class'=>'login'));
		$r .= $form->head;
		if(PROTOCOL !== 'https://'){
		//	$form->write('<p class="red">WARNING: you are not connected through a secure connection.</p>');
		}
		$r .= $form->input('email',array('placeholder'=>t('Email'))).'<br/>';
		$r .= $form->input('password',array('type'=>'password'));
		if(isset($_REQUEST['backurl'])){
			$backurl = urlencode(HOME.$_REQUEST['backurl']);
		}
		elseif($_REQUEST['url'] != 'login'){
			$backurl = urlencode(HOME.$_REQUEST['url']);
		}
		else{
			$backurl = urlencode(HOME);
		}
		$r .= $form->input('backurl',array('type'=>'hidden','value'=>$backurl));
		$r .= '<p>'.lnk(t('Forgot your password?'),'passwordrecovery').'</p>';
		$r .= '<p><a class="btn btn-grey fblogin" href="https://pixyt.com/fblogin?backurl='.$backurl.'"><img src="/img/share/facebook-white-100x100.png" height="20" width="20" alt="connect with facebook"/>connect</a><button type="submit" class="btn right btn-green">'.translate('Log in').'</button><p>';
		return $r;
	}
	
	function genre($g,$id=''){
		return ' <span class="genre" id="genre_'.$id.'">'.$g.lnk('X','#',array('update[User]['.$this->id.'][removegenre]'=>$id),array('title'=>translate('remove genre'),'class'=>'delete')).'</span>';
	}
	
	function website($w,$id=''){
		if($this->isOwner()){$del=lnk('X','#',array('update[User]['.$this->id.'][profile][rmv][websites]'=>$id),array('title'=>translate('remove website'),'class'=>'delete'));}else{$del='';}
		return ' <div class="website" id="websites_'.$id.'">'.xtlnk(makeUrl($w),$w,array('rel'=>'nofollow','class'=>'grey')).$del.'</div>';
	}
	
	public function picture($s='text'){
		if(isset($this->profile->picture) && Object::objectExists('Photo',$this->profile->picture)){
			$p = new Photo($this->profile->picture);
			return $p->img($s);
		}
		$profile = $this->currentParam('photos');
		if(!empty($profile)){
			$p = new Photo($profile);
			return $p->img($s);
		}
		else{
			return '<img src="/img/profile/'.$s.'.png" class="nocopy" alt="'.$this->fullName().'" title="'.$this->fullName().'"/>';
		}
	}
	
	public function profilePicture($s='small'){
		$profile = $this->currentParam('photos');
		if(!empty($profile)){
			$p = new Photo($profile);
			return $p->img('text');
		}
		else{
			return '<img src="/img/profile/'.$s.'.png" class="nocopy" alt="'.$this->fullName().'" title="'.$this->fullName().'"/>';
		}
	}
	
	function edit(){
		if($this->id == 0){return $this->register();}
		if(!$this->isOwner || $this->isDummy){return '<h2>'.translate('You may only edit your own profile.').'</h2>';}
		$r = '';
		$r .= dv('splitLeft');
		$r .= dv('padder');
		$r .= dv('contentBox').$this->about().xdv();
		$r .= dv('contentBox').'<h2>'.translate('Websites').'</h2>'.dv('editarea','websites');
		$form = new Form('User',$this->id,true,array('class'=>'inline','ajax'=>true));
		$form->addwebsite('input',translate('add website'),false,'this.value=\'\'');
		$form->{translate('add')}('submit');
		$r .= $form->returnContent();
		$q = 'SELECT * FROM Site WHERE uid = "'.$this->id.'"';
		if(LP::$db->customQuery($q,$d)){
			foreach($d as $site){
				$r .= dv('locked website').makeUrl($site['url']).'<img src="'.HOME.'img/locked.png" title="'.translate('To change domain name, please contact admin.').'" height="14px"/>'.xtlnk(makeUrl($site['url'].'/login'),'<img src="'.HOME.'img/edit_black.png" title="'.translate('Edit website.').'" height="14px"/>').xdv();
			}
		}
		foreach(App::$user->data['profile']['websites'] as $id=>$w){
			$r.=$this->website($w,$id);
		}
		$r .= xdv();
		$r .= xdv();
		$r .= dv('contentBox').'<h2>'.translate('Social networks').'</h2>'.'<ul>';
		if(!isset($this->profile->networks)){$this->profile->networks=array('facebook'=>'','twitter'=>'');}
		$placeholder = '...................';
		if(!empty($this->profile->networks->facebook)){$placeholder = $this->profile->networks->facebook;}
		$r .= '<li><img src="'.HOME.'img/links/Facebook.png" title="Facebook" height="16px"/> <span class="lightgrey">facebook.com/<span class="update" id="update_User_'.$this->id.'_facebook">'.$placeholder.'</span></span></li>';
		$placeholder = '...................';
		if(!empty($this->profile->networks->twitter)){$placeholder = $this->profile->networks->twitter;}
		$r .= '<li><img src="'.HOME.'img/links/Twitter.png" title="Twitter" height="16px"/> <span class="lightgrey">twitter.com/<span class="update" id="update_User_'.$this->id.'_twitter">'.$placeholder.'</span></span></li>';
		$r .= '</ul>'.xdv();
		
		
		if(empty($this->type)){$this->type=3;}
		$r .= dv('contentBox').'<h2>'.translate('Account usage').'</h2>'.'<ul>';
		$r .= '<li>'.File::getUsage($this->id).'/'.prettyBytes(self::$diskspace[$this->type]).'</li>';
		$r .= '</ul>'.xdv();
		
		$r .= dv('padder');
		$r .= xdv();
		$r .= xdv();
		$r .= xdv();
		$actions = array('commented','liked','promoted');
		$form = new Form('User',$this->id);
		$r .= dv('splitRight').dv('padder rightText').dv('contentBox').'<h3>'.translate('Email me when my content gets:').'</h3>';
		$yesno = array('yes'=>'1','no'=>'0');
		foreach($actions as $n=>$v){
			$r .= dv('padded').swtch($v,$yesno,$this->settings->mailsettings->$v,'User',$this->id).xdv();
		}
		$r .= xdv();
		$langs=array($this->settings->language=>Translation::$languages[$this->settings->language]);
		foreach(Translate::$languages as $n=>$v){$langs[$n]=$v;}
		$form = new Form('User',$this->id,true,array('class'=>'inline','ajax'=>'true'));
		$form->language('select',$langs,false);
		$form->{translate('save')}('submit');
		$r .= dv('contentBox').'<h2>'.translate('Language').'</h2>'.$form->returnContent().xdv();
		$r .= dv('contentBox').'<h2>'.translate('Close account').'</h2>'.lnk(translate('close my account'),true,array('deleteaccount'=>'true'),array('title'=>translate('Are you sure you wish to permanently delete you account?'),translate('Are you sure you wish to permanently delete you account?'),'class'=>'button')).xdv();
		$r .= xdv();
		$r .= xdv();
		return $r;
	}
	
	protected function settings($tab=''){
		$r = '';
		if(isset($_REQUEST['tab'])){$tab = $_REQUEST['tab'];}
		if(empty($tab)){$tab = 'profile';}
		T::$page['title'] = translate('Settings');
		$this->sidebar(' ');
		$r .= dv('panel settings');
		$r .= '<h1>'.translate('Settings').'</h1>';
		$r .= '<ul class="tabs">';
		if($tab == 'profile'){$class=' class="active"';}else{$class='';}
		$r .= '<li'.$class.'>'.lnk(translate('profile'),'account/settings/profile').'</li>';
		if($tab == 'account'){$class=' class="active"';}else{$class='';}
		$r .= '<li'.$class.'>'.lnk(translate('account'),'account/settings/account').'</li>';
		if($tab == 'websites'){$class=' class="active"';}else{$class='';}
		$r .= '<li'.$class.'>'.lnk(translate('websites'),'account/settings/websites').'</li>';
		if($tab == 'notifications'){$class=' class="active"';}else{$class='';}
		$r .= '<li'.$class.'>'.lnk(translate('notifications'), 'account/settings/notifications').'</li>';
		if($tab == 'network'){$class=' class="active"';}else{$class='';}
		//$r .= '<li'.$class.'>'.lnk(translate('network'),'account/settings/network').'</li>';
		if($tab == 'close'){$class=' class="active"';}else{$class='';}
		$r .= '<li'.$class.'>'.lnk(translate('close account'),'account/settings/close').'</li>';
		$r .= '</ul>';
		
		$r .= dv('settings-inner');
		if(isset($_REQUEST['deleteaccount'])){
			$r .= '<p class="alert-danger padded">'.translate('Are you sure you wish to permanently delete you account?').'</p>';
			$r .= lnk(translate('cancel'),'user/'.$this->id.'/settings',array(),array('title'=>translate('cancel'),'class'=>'btn btn-info')).' '.lnk(translate('confirm'),'',array('delete[User]['.$this->id.']'=>'1'),array('title'=>translate('Are you sure you wish to permanently delete you account?'),'class'=>'btn'));
			return $r.xdv();
		}
		
		if(!isset($_REQUEST['tab'])){$_REQUEST['tab']='';}
		switch($tab){
			case 'account':
				$r .= dv('topBar');
				$r .= dv('stacks').lnk('<img src="'.HOME.'/img/stack40.png" alt="photo"/> '.prettyNumber(App::$db->count('Stack',array('uid'=>$this->id)),1),true,array('otype'=>'stack')).xdv();
				$r .= dv('photos').lnk('<img src="'.HOME.'/img/img40.png" alt="photo"/> '.prettyNumber(App::$db->count('Photo',array('uid'=>$this->id)),1),true,array('otype'=>'photo')).xdv();
				$r .= dv('files').'<img src="'.HOME.'/img/hdd.png" alt="photo"/> '.File::getUsage($this->id).'/'.prettyBytes($this->allocation).xdv();
				$r .= $this->usage();
				$r .= xdv();
				$r .= '<br class="clearfloat"/>';
				$form = new Form('User',$this->id,true,array('class'=>'full'));
				$r .= $form->head();
				$r .= dv('');
				$r .= '<h4>'.t('Basic info').'</h4>';
				$r .= $form->input('firstname',array('value'=>$this->firstname));
				$r .= $form->input('lastname',array('value'=>$this->lastname));
				$r .= $form->input('email',array('value'=>$this->email,'type'=>'email'));
				$r .= xdv();
				
				$r .= dv('');
				$r .= '<h4>'.t('Password').'</h4>';
				$r .= $form->input('curpassword',array('type'=>'password'));
				$r .= $form->input('newpassword',array('type'=>'password'));
				$r .= xdv().'<br class="clearfloat"/>';
				
				$r .= '<h4>'.t('Language').'</h4>';
				$langs = array();
				foreach(Translate::languages() as $n=>$label){
					$opt=array('value'=>$n,'label'=>$label);
					if($n == $this->settings->language){$opt['selected']=true;}
					$langs[]=$opt;
				}
				$r .= $form->select('language',array('options'=>$langs));
				$r .= $form->button(t('save'));
				$r .= $form->foot();
			break;
			
			case 'close':
				$r .= '<h2>'.translate('Close my account permenantly').'</h2><br/>'.lnk(translate('Close account'),true,array('deleteaccount'=>'true'),array('class'=>'btn white btn-danger','title'=>translate('Are you sure you wish to permanently delete you account?'),translate('Are you sure you wish to permanently delete you account?')));
			break;
			
			case 'notifications':
				$form = new Form('User',$this->id,true,array('class'=>'full'));
				$r .= $form->head();
				$actions = array('commented','liked','promoted');
				$r .= '<h2>'.translate('Notifications').'</h2>';
				$yesno = array('On'=>'1','Off'=>'0');
				/*
				if(!isset($this->settings->mailsettings)){$this->settings->mailsettings = new stdClass();}
				if(!isset($this->settings->mailsettings->commented)){$this->settings->mailsettings->commented=true;}
				if(!isset($this->settings->mailsettings->liked)){$this->settings->mailsettings->liked=true;}
				if(!isset($this->settings->mailsettings->promoted)){$this->settings->mailsettings->promoted=true;}
				if(!isset($this->settings->mailsettings->newsletter)){$this->settings->mailsettings->newsletter=true;}
				if(!isset($this->settings->mailsettings->followed)){$this->settings->mailsettings->followed=true;}
				*/
				$r .= '<h3>'.t('Receive an email for:').'</h3>';
				
				$r .= t('Comments:').' '.$form->onOff('mailsettings_commented',array('value'=>$this->settings->mailsettings->commented));
				$r .= t('Likes:').' '.$form->onOff('mailsettings_liked',array('value'=>$this->settings->mailsettings->liked));
				$r .= t('Promotes:').' '.$form->onOff('mailsettings_promoted',array('value'=>$this->settings->mailsettings->promoted));
				$r .= t('Follows:').' '.$form->onOff('mailsettings_followed',array('value'=>$this->settings->mailsettings->followed));
				
				$r .= t('Newsletters:').' '.$form->onOff('mailsettings_newsletter',array('value'=>$this->settings->mailsettings->newsletter));
/*
				$r .= $form->free('<label>'.t('comments my content').'</label><p>'.swtch('mailsettings_commented',$yesno,$this->settings->mailsettings->commented,'User',$this->id).'</p>');
				$r .= $form->free('<label>'.translate('likes my content').'</label><p>'.swtch('mailsettings_liked',$yesno,$this->settings->mailsettings->liked,'User',$this->id).'</p>');
				$r .= $form->free('<label>'.translate('promote my content').'</label><p>'.swtch('mailsettings_promoted',$yesno,$this->settings->mailsettings->promoted,'User',$this->id).'</p>');
				$r .= $form->free('<label>'.translate('Someone follows you').'</label><p>'.swtch('mailsettings_followed',$yesno,$this->settings->mailsettings->followed,'User',$this->id).'</p>');
				$r .= $form->free('<label>'.translate('Occasional updates').'</label><p>'.swtch('mailsettings_newsletter',$yesno,$this->settings->mailsettings->newsletter,'User',$this->id).'</p>');
				*/
				$r .= '<br/>';
				$r .= $form->button(t('Save'));
				$r .= $form->foot();
			break;
			
			case 'websites':
			
				$r .= '<h2>'.translate('Websites').'</h2>';
				$sites = $this->sites();
				if(empty($sites)){
					$r .= '<h3>'.translate('You have no websites yet!').'</h3>';
				}
				else{
					$r .= '<ul class="websites">';
					foreach($sites as $site){
						$r .= '<li>';
						if($site->expirationDate != '0000-00-00'){
							$ex = strtotime($site->expirationDate);
							$expire = round(($ex - time())/86400);
							if($expire < 20){
								$r .= dv('alert alert-error alert-block').translate('This domain will expire on').' '.$site->expirationDate.xdv();
							}
							elseif($expire < 60){
								$r .= dv('alert alert-warning alert-block').translate('This domain will expire on').' '.$site->expirationDate.xdv();
							}
							else{
								$r .= dv('alert alert-info alert-block').translate('This domain will expire on').' '.$site->expirationDate.xdv();
							}
						}
						$r .= '<p><img src="/img/sale/domain.png" alt="website" height="14px"/> ';
						$r .= makeUrl($site->url).' ';
						$r .= xtlnk(makeUrl($site->url.'/login'),'<img src="'.HOME.'img/edit_black.png" title="'.translate('Edit website.').'" height="14px"/>');
						$r .= '<span class="right">';
						$r .= lnk(translate('renew'),'website/validate',array('renew'=>$site->id),array('class'=>'btn btn-success'));
						$r .= '</span></p>';
						$r .= '</li>';
					}
					$r .= '</ul>';
				}
				$r .= lnk('Create a website','site/create',array('from'=>'settings'),array('class'=>'btn btn-inverse')).'</li>';
			break;
			
			case 'profile':
			default:
				$r .= '<h2>'.translate('About you').'</h2>';
				$form = new Form('User',$this->id,true,array('class'=>'full'));
				$f=array();
				foreach(User::industryFunctions() as $id=>$function){$f[$id]=t($function);}
				$r .= $form->head;
				$r .= $form->select('profile_function',array('options'=>$f,'placeholder'=>t('Function?')));
				$r .= $form->input('profile_location',array('value'=>$this->profile->location,'placeholder'=>t('Your location')));
				$r .= $form->input('profile_headline',array('value'=>$this->profile->headline,'placeholder'=>t('Headline')));
				$r .= $form->textarea('profile_about',array('value'=>$this->profile->about,'placeholder'=>t('Your story')));
				$r .= $form->button('Save');
				$r .= $form->foot;
				
			break;
		}
		//ENCRYPTED
		/*
		if($this->id == 1){
			$r .= dv('encrypted element').'<h2>'.translate('My encypted files').'</h2>';
			$r .= dv().File::uploadEncryptedBtn(0).xdv();
			$r .= dv('myfiles').xdv();
		}
		*/
		$r .= xdv();
		$r .= xdv();
		return $r;
	}
	
	public function about(){
		$about = '';
		if(!empty($this->profile->about)){$about=$this->profile->about;}
		if($this->isOwner){if(empty($about)){$about=translate('No bio yet');}$about = '<span class="editable" id="'.$this->idPrefix.'_about">'.nl2br($about).'</span>';}
		else{$about=nl2br($about);}
		return '<p class="about">'.$about.'</p>';
	}
	
	public function headline(){
		if(!isset($this->profile->headline)){$this->profile->headline='';}
		$headline = $this->profile->headline;
		return '<h2>'.$headline.'</h2>';
	}
	
	public static function login($email, $password,$force=false){
		if(!isset($email,$password)){return false;}
		$email = strtolower($email);
		if(Object::exists('User',$email,'email',$id)){
			$user = new User($id);
			if($user->id != 0){
				if(hash('SHA256',SITE_KEY.$password) == $user->password){
					if($user->hide||$user->deleted){
						Msg::nofity(t('Your account is suspended. To restore it, please contact admin.'),0,Msg::CRITICAL);
						return false;
					}
					else{
						$_SESSION['uid'] = $user->id;
						App::$user = $user;
						return true;
					}
				}
				else{
					Msg::addMsg('Wrong password');
					$user->data['stats']['failedlogin'][time()] = USER_IP;
					$user->update();
				}
			}
			else{
				Msg::addMsg('User not found!');
			}
		}
		else{
			Msg::addMsg('User not found!');
		}
		$_SESSION['uid'] = 0;
		if(!isset($_SESSION['loginfailures'])){
			$_SESSION['loginfailures']=array();
		}
		$_SESSION['loginfailures'][]=time();
		return false;
	}
	
	public function activity($who,$did,$what){
	}
	
	protected function addexp(){
		$r = '';
		if(!isset($_REQUEST['id'])){
			$id = 'add';
		}
		else{
			$id = $_REQUEST['id'];
		}
		$form = new Form('User',$this->id,true,array('class'=>'full','ajax'=>true));
		$form->{'profile_experiences_'.$id.'_t'}('input',translate('brief job title'),false,'clear');
		$form->{'profile_experiences_'.$id.'_m'}('input',translate('Employer'),false,'clear');
		$form->{'profile_experiences_'.$id.'_d'}('textarea',translate('Tasks, environment...'),false,'clear');
		$form->{'profile_experiences_'.$id.'_ongoing'}('checkbox',false,translate('ongoing'),'','','toogleDisabledOngoing();');
		$form->{'profile_experiences_'.$id.'_b'}('date','',false);
		$form->{'profile_experiences_'.$id.'_e'}('date','',false);
		$form->{'add'}('submit');
		T::$js[] = '
var toogleDisabledOngoing = function(){
if($("#update_User_'.$this->id.'_profile_experiences_add_ongoing").is(":checked")){
	$("#update_User_'.$this->id.'_profile_experiences_add_e_year").attr("disabled","disabled");
	$("#update_User_'.$this->id.'_profile_experiences_add_e_month").attr("disabled","disabled");
	$("#update_User_'.$this->id.'_profile_experiences_add_e_day").attr("disabled","disabled");
}
else{
	$("#update_User_'.$this->id.'_profile_experiences_add_e_year").removeAttr("disabled");
	$("#update_User_'.$this->id.'_profile_experiences_add_e_month").removeAttr("disabled");
	$("#update_User_'.$this->id.'_profile_experiences_add_e_day").removeAttr("disabled");
}
}
';
		$r .= $form->returnContent();
		return $r;
	}
	
	protected function fullView(){
		$r = '';
		$r .= dv('profile');
		$max = 5;
		T::$page['title'] = $this->fullName('full');
		if(isset($this->profile->function) && !empty($this->profile->function)){
			T::$page['title'] .= ', '.t($this->profile->function);
		}
		$q = new Collection('Photo');
		$q->where(array('uid'=>$this->id,'access >='=>'3'))->limit(55);
		$photos = $q->get();
		if(!empty($photos)){
			$r .= dv('relative d1000 nooverflow images-header');
			foreach($photos as $p){
				$r .= lnk($p->img('text'),'photo/'.$p->id);
			}
			$r .= xdv();
		}
		$r .= '<h1>';
		if($this->isOwner()){
			$r .= lnk($this->picture('small'),'user/'.$this->id.'/selectphoto',array(),array('data-type'=>'popup','class'=>'profilePhotoLink'));
		}
		else{
			$r .= $this->picture('small');
		}
		
		T::$page['description'] = $this->fullName('full').' ';
		$summary = '';
		$r .= '<span class="name">'.$this->fullName('full').'</span>';
		if(isset($this->profile->headline)){
			$r .= '<span class="headline">'.$this->headline().'</span>';
			T::$page['title'].=' | '.$this->headline();
			T::$page['description'] .= ' &laquo;'.$this->headline().'&raquo;';
		}
		$r .= '</h1>';
		
		//$r .= $this->about();
		$sidebar = dv('buttons');
		if($this->isOwner()){
			$sidebar .= lnk(t('Add some photos'),'account/organize',array(),array('class'=>'btn'));
			//$r .= lnk(translate('Complete resume'),'user/'.$this->id.'/addexp',array(),array('data-type'=>'popup','title'=>translate('Edit my profile'),'class'=>'btn'));

		}
		else{
			$sidebar .= lnk(translate('message'),'message/compose/'.$this->id,array(),array('title'=>translate('Send a message to').' '.$this->fullName(),'data-type'=>'popup','class'=>'btn padded')).' ';
			$sidebar .= Follower::followBtn($this->id);
		}
		$sidebar .= xdv();
		$max=4;
		if(!isset($this->profile->websites)){
			$this->profile->websites = array();
		}
		$sites = $this->profile->websites;
		foreach($this->sites() as $site){$sites[] = $site->url;}
		foreach($sites as $i=>$site){
			$sidebar .= dv('sites').xtlnk(makeUrl($site),strtolower(shorten(str_replace(array('https://','http://','www.'),'',$site),34))).xdv();
		}
		
		
		$sidebar .= dv('resume');
		$sidebar .= '<p class="small lightgrey">(born '.date('d/m/y',strtotime($this->birthday)).')</p>';
		if(isset($this->profile->function) && !empty($this->profile->function)){
			$lnk = lnk(t($this->profile->function),'creatives/'.$this->profile->function);
			$sidebar .= t('{$1} is a {$2}',array($this->firstname,$lnk));
		}
		
		if(isset($this->profile->location)){
			T::$page['description'] .= ' '.t('based in {$1}.',array($this->profile->location));
			$sidebar .= ' '.t('based in {$1}.',array(lnk($this->profile->location,'maps/'.urlencode($this->profile->location),array('uid'=>$this->id),array('data-type'=>'popup'))));
		}
		else{
			$sidebar .= '.';
		}
		/*
		if(!empty($this->profile->experiences)){
			$r .= ' '.translate('Previous work experience includes').': ';
			T::$page['description'] .= ' '.translate('Worked at').': ';
			$i=0;
			$last = count($this->profile->experiences)-1;
			foreach($this->profile->experiences as $id=>$exp){
				if(is_bool($exp['e'])){$stop=translate('to now');}
				elseif(is_int($exp['b'])){$stop='- '.date('Y',$exp['e']);}
				else{$stop='- '.$exp['e'];}
				if(is_int($exp['b'])){$started = date('Y',$exp['b']);}
				else{$started = $exp['b'];}
				$r .= '<span id="exp_'.$id.'">'.$exp['m'].' <span class="time">('.$started.')</span>';
				if($this->isOwner){
					$r .= lnk('X','user/'.App::$user->id,array('update[User]['.$this->id.'][profile][experiences][rmv]'=>$id),array('class'=>'delete','ajax'=>'true'));
				}
				T::$page['description'] .= $exp['m'];
				if($i!=$last){T::$page['description'] .= ', ';$summary.=', ';}
				$r.='</span> ';
				$i++;
			}
		}
		*/
		
		if(isset($this->profile->about) && !empty($this->profile->about)){
			$sidebar .= dv('about').$this->profile->about.xdv();
		}
		$sidebar .= xdv();
		
		$this->sidebar($sidebar);
		
		$q = new Query();
		$q = new Collection('Photo');
		$q->where(array('uid'=>$this->id,'access >='=>'3'))->limit(12);
		$stacks = $q->get();
		
		foreach($stacks as $stack){
			$r .= $stack->display('feed');
		}
		$r .= xdv();
		$r .= xdv();
		$r .= xdv().'<br class="clearfloat"/>';
		return $r;
	}
	
	public function xpFormat($id,$exp){
		$r = '';
		$r .= dv('experience preview','exp_'.$id);
		if(is_bool($exp['e'])){$stop=translate('to now');}
		elseif(is_int($exp['b'])){$stop='- '.date('Y/m',$exp['e']);}
		else{$stop='- '.$exp['e'];}
		$r .= '<h4>'.$exp['t'].'</h4>';
		$r .= '<h5>'.$exp['m'].'</h5>';
		if(is_int($exp['b'])){$started = date('Y/m',$exp['b']);}
		else{$started = $exp['b'];}
		$r .= '<span class="date">'.$started.' '.$stop.'</span><p class="description">'.$exp['d'].'</p>';
		if($this->isOwner){
			$r .= dv('tools').lnk('delete','#user/'.$this->id,array('update[User]['.$this->id.'][profile][experiences][rmv]'=>$id),array('class'=>'button')).xdv();
		}
		$r .= xdv();
		return $r;
	}
	
	public function sites(){
		$sites = new Collection('Site');
		$sites->where('uid',$this->id);
		return $sites->get();
	}
	
	public function notify($msg='',$type='newsletter',$subject=''){
		switch($type){
			case 1:
			case 'commented':
				$subject = translate('Someone commented on your work.');
				if(!isset($this->settings->mailsettings->commented)){$this->settings->mailsettings->commented=1;}
				if($this->settings->mailsettings->commented == 0){
					return false;
				}
			break;
			
			case 2:
			case 'liked':
				$subject = translate('Someone likes your work.');
				if(!isset($this->settings->mailsettings->liked)){$this->settings->mailsettings->liked=1;}
				if($this->settings->mailsettings->liked == 0){
					return false;
				}
			break;
			
			case 3:
			case 'rated':
				$subject = translate('Someone rated your work highly.');
				if(!isset($this->settings->mailsettings->rated)){$this->settings->mailsettings->rated=1;}
				if($this->settings->mailsettings->rated == 0){
					return false;
				}
			break;
			
			case 5:
			case 'promoted':
				$subject = translate('Your content was promoted!');
				if(!isset($this->settings->mailsettings->promoted)){$this->settings->mailsettings->promoted=1;}
				if($this->settings->mailsettings->promoted == 0){
					return false;
				}
			break;
			
			case 6:
			case 'shared':
			
			break;
			
			case 7:
			case 'validated':
			
			break;
			
			case 'followed':
				$subject = App::$user->firstname.' '.translate('started following you!');
				if(!isset($this->settings->mailsettings->followed)){$this->settings->mailsettings->followed=1;}
				if($this->settings->mailsettings->followed == 0){
					return false;
				}
			break;
			
			case 'recover':
				
			break;
			
			case 'newsletter':
				$msg = '';
				if(!isset($this->settings->mailsettings->promoted)){$this->settings->mailsettings->promoted=1;}
				if($this->settings->mailsettings->promoted == 0){
					return false;
				}
				switch($this->settings['language']){
					case 'fr_FR':
						$l='fr';
						$subject = 'Newletter Pixyt';
						$sub = 'Activité sur vos photos ce mois:';
					break;
					default:
						$l='en';
						$subject = 'Pixyt Newsletter';
						$sub = 'Feedbacks you received this month:';
					break;
				}
				
				//ABOUT YOU
				$msg .= dv('article').'<h2>'.$sub.'</h2>';
				$me = new Table('monitor');
				$t = time()-60*60*24*31;
				
				$col = new Collection('Feedback');
				$col->created('>',$t,true);
				$col->proprietor=$this->id;
				$col->type=1;
				$comments = $col->total(true);
				
				$col = new Collection('Feedback');
				$col->created('>',$t,true);
				$col->proprietor=$this->id;
				$col->type=2;
				$hearts = $col->total(true);
				
				$col = new Collection('Feedback');
				$col->created('>',$t,true);
				$col->proprietor=$this->id;
				$col->type=5;
				$promotes = $col->total(true);
				$me->addLine(array(t('Comments'),t('Hearts'),t('Promotes')));
				$me->addLine(array('<p class="counter">'.$comments.'</p>','<p class="counter">'.$hearts.'</p>','<p class="counter">'.$promotes.'</p>'));
				
				$msg .= $me->returnContent();
				
				$msg .= dv('article');
				switch($l){
					case 'fr':
						$msg .= '<h2>Exposition Synchrodogs</h2><a href="http://pixyt.com/contest/synchrodogs_jury_prize"><img id="Photo_8720_horizont" title="" src="//pix.yt/cdn/263/horizont/8720.jpg" alt="Synchrodogs"/></a>';
						$msg .= '<p class="big">'.nl2br('
Les gagnant du concours Pixyt - un duo de photographe Ukrainiens s\'appelant Synchrodogs - ont gagnés une exposition à Lille qui commence aujourd\'hui! 

Nous vous invitons a aller voir les photos grand format, offertes par le laboratoire l\'Instant de Monaco. C\'est jusqu\'au 30 Mai, à la galerie Quai26, 62 rue d\'Angleterre dans le vieux Lille.').'</p>';
					break;
					
					case 'en':
					default:
						$msg .= '<h2>Synchrodogs Exhibition</h2><a href="http://pixyt.com/contest/synchrodogs_jury_prize"><img id="Photo_8720_horizont" title="" src="//pix.yt/cdn/263/horizont/8720.jpg" alt="Synchrodogs"/></a>';
						$msg .= '<p class="big">'.nl2br('
The Pixyt contest winners, a talented duo of photographers from Ukraine called Synchrodogs, won an exhibition in Lille which is starting today!

If you are nearby, we invite you to come and see the large format prints granted by l\'Instant (Monaco) at the Quai26 gallery in Lille. The photos will be exhibited until May 30th, 62 rue d\'Angleterre in the old part of Lille.').'</p>';
					break;
				}
				$msg .= xdv();
				
				
				$msg .= dv('article');
				switch($l){
					case 'fr':
						$msg .= '<h2>LAM magazine</h2>';
						$msg .= '<p class="big">Light Architect viens de lancer son magazine (LAM) - que nous vous invitons à venir découvrir ici:<br/>';
						$msg .= xtlnk('http://bit.ly/YymoAF','http://bit.ly/YymoAF',array('class'=>'btn'));
						$msg .= '<br/><br/>La second édition s\'intitule "The Grandma issue", et nous recherchons les photographes à publier! Nous vous invitons a nous envoyer vos photos de grand-mères à mail@lightarchitect.com ou bien les poster sur Pixyt avec le tag #grandma</p>';
					break;
					
					case 'en':
					default:
						$msg .= '<h2>LAM magazine</h2>';
						$msg .= '<p class="big">Light Architect just launched its first edition of its magazine (LAM) - which you can find here:<br/>';
						$msg .= xtlnk('http://bit.ly/YymoAF','http://bit.ly/YymoAF',array('class'=>'btn'));
						$msg .= '<br/><br/>The second edition is "The Grandma issue" for which we are looking for photographers to publish right now! Please submit your grandma pictures to mail@lightarchitect.com on post them on Pixyt with the hash tag #grandma<br/></p>';
					break;
				}
				$msg .= xdv();
				
				$msg .= dv('article');
				switch($l){
					case 'fr':
						$msg .= '<h2>Site web</h2>';
						$msg .= '<p class="big">Enfin, pixyt permet désormais de créer son site internet, de manière simple et efficasse - venez découvrir nos offres tout compris à partir de 2,90€/mois<br/><br/>';
						$msg .= xtlnk('http://pixyt.com/website?lang=fr_FR&utm_source=newsletter&utm_medium=email&utm_campaign=sales_launch','Découvrir les offres',array('class'=>'btn btn-success'));
						$msg .= '<br/></p>';
					break;
					
					case 'en':
					default:
						$msg .= '<h2>Websites</h2>';
						$msg .= '<p class="big">Finally, Pixyt now allows to create and maintain your website super easily. Check out our offers starting at 2,90€ all inclusive!<br/><br/>';
						$msg .= xtlnk('http://pixyt.com/website?lang=en_GB&utm_source=newsletter&utm_medium=email&utm_campaign=sales_launch','View offers',array('class'=>'btn btn-success'));
						$msg .= '<br/></p>';
					break;
				}
				$msg .= xdv();
				$msg .= dv('signature');
				switch($l){
					case 'fr':
						$msg .= '<br/>Bon weekend,<br/><br/>l\'équipe Pixyt';
					break;
					
					case 'en':
					default:
						$msg .= '<br/>Have a great weekend,<br/><br/>Pixyt Crew';
					break;
				}
				$msg .= xdv();
			break;
		}
		
		$message = array(array('<h1>'.lnk('<img src="http://pixyt.com/img/logo/logo128.png" height="40" width="40" style="position:relative;top:10px;" alt="pixyt" title="pixyt"/>','',array('from'=>'email')).' &nbsp; '.translate('Hi').' '.$this->firstname.'!</h1>'),array('<div class="message">'.$msg.'</div>'),array('<br/><p style="color:#888;margin:4px;border-top:1px dotted #aaa;">email settings '.xtlnk('http://pixyt.com/account/settings','pixyt.com/account/settings').' - '.translate('Pssst!: You can answer this email to tell us something').' :) </p>'));
		return sendMail($this->email, 'Pixyt', 'crew@pixyt.com', $subject, $message);
	}
	
	public function followers(){
		if($this->followers === false){
			$followers = new Collection('Follower');
			$followers->who = $this->id;
			$followers->load(0,999);
			foreach($followers->results as $who){
				$this->followers[$who->uid] = $who;
			}
		}
		return $this->followers;
	}
	
	public function following(){
		if($this->following === false){
			$followers = new Collection('Follower');
			$followers->where('uid',$this->id);
			$data = $followers->load(0,999);
			$this->following = array();
			foreach($data as $who){
				$this->following[$who->who] = $who;
			}
		}
		return $this->following;
	}
	
	protected function postUpdate(){
		App::$user = $this;
		return true;
	}

	protected function postInsert(){
		$this->settings->language = $_SESSION['lang'];
		$this->settings->from = $_SESSION['from'];
		$code = randStr();
		$this->settings->validationCode = $code;
		$this->update();
		$_SESSION['uid'] = $_REQUEST['uid'] = $this->id;
		App::$user = $this;
		
		$url = 'http://pixyt.com/validate?uid='.$this->id.'&code='.$code;
		$link = xtlnk($url,translate('Validate my account'));
		$msg = t('You created an account on Pixyt.');
		$msg .= ' '.t('To confirm your email click here: ').$link.PHP_EOL;
		$msg .= '<p class="small grey">'.t('If the above link does not work please copy the following url into your browser:').' '.$url.'</p>';
		$msg .= PHP_EOL.PHP_EOL.'Best regards,'.PHP_EOL.'Pixyt'.PHP_EOL;
		$msg = nl2br($msg);
		
		$message = array(array('<h1>'.lnk('<img src="http://pixyt.com/img/logo/logo1.028x28.png" alt="Pixyt" title="Pixyt" height="28">','',array('from'=>'email')).' Welcome to Pixyt!</h1>'),array('<div class="message">'.$msg.'</div>'),array('<br/><p style="color:#aaa;border-top:1px dotted #aaa;">email settings '.xtlnk('http://pixyt.com/account/settings','pixyt.com/account/settings').'</p>'));
		sendMail($this->email, 'Pixyt crew', 'crew@pixyt.com', 'Pixyt registration', $message);
		return true;
	}
	
	/**
	 * Returns messages authorization code of this user.
	 * If the last saved authorization code is invalid, a new one will be generated and returned.
	 */
	 public function messagesAuthCode($force = false)
	 {
		 if($force || !isset($this->settings->messagesAuthLastUpdate) || 
		 	time() - intval($this->settings->messagesAuthLastUpdate) > MESSAGES_AUTH_TIMEOUT)
		 	return $this->updateMessagesAuthCode();
		 else
			 return $this->settings->messagesAuth;
	 }
	
	/**
	 * Updates messages authorization code of this user
	 */
	public function updateMessagesAuthCode()
	{
		$this->settings->messagesAuth = generateMessagesKey($this->id);
		$this->settings->messagesAuthLastUpdate = time();
		if($this->update())
			return $this->settings->messagesAuth;
		else
			return null;
	}

	/**
	 * Destroys messages authorization code of this user
	 */
	public function destroyMessagesAuthCode()
	{
		unset($this->settings->messagesAuth);
		$this->update();
	}
}
?>