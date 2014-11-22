<?php
class Home extends Interfaces{
	public function directory(){
		if(!$_SESSION['uid']){return $this->welcome();}
		
		//set the page title
		if(!isset(App::$site->content->Home)){
			App::$site->content->Home = new stdClass();
			App::$site->content->Home->title = "Pixyt feed";
			App::$site->content->Home->description = "Discover great photographers";
			App::$site->update();
		}

		$items = new Collection('Photo');
		$items->where('uid IN',array_merge(array_keys(App::$user->following()),array(App::$user->id)));
		$items->limit(50)->order_by('id',true);
		$data = $items->load();
		$feed = array();
		foreach($data as $item){
			$author = new User($item->uid);
			$feed[] = array(
				'id'=>$item->id,
				'title'=>$item->title,
				'size'=>'w'.(rand(1,3)),
				'user'=>$author->data(),
			);
		}
		return array('items'=>$feed);
		return array('uid'=>$_SESSION['uid']);
		$r = '';
		
		$c = dv('navigation');
		$c .= dv('tab').dv('denomination').'Feeds'.xdv();
		$c .= dv('el').lnk('My network').xdv();
		$c .= dv('el').lnk('Popular images').xdv();
		$c .= dv('el').lnk('Editors picks').xdv();
		$c .= xdv();
		
		$max = 20;
		$c = new Collection('Photo');
		$where = array();
		$where['access'] = 3;
		$where['channel <'] = 2;
		$c->where($where);
		$c->limit($max,$_REQUEST['s']*$max);
		$collection = $c->load();
		
		if(!IS_AJAX){
			$r .= dv('append','feeds');
			
			/*
			T::js('
var infinite = true;
s='.$_REQUEST['s'].';
function infinity(){
	if(infinite===true){
		if($(window).scrollTop() >= ($(document).height() - $(window).height())-1000){
			s++;
			activateLoadingState();
			query("",{"gethtml":true,"s":s},$("#feeds"));
		}
	}
}
$(".toggleinfinite").click(function(){
	infinite=!infinite;
	var img = $(this).find("img");
	var path = img.attr("src");
	img.attr("src",$(this).attr("data-reverse"));
	$(this).attr("data-reverse",path);
});
setInterval("infinity()",1000);
');
*/
		}
		$columns = array();
		$i=0;
		if(empty($collection)){
			$r .= '<h3>No more photos to show.</h3>';
		}
		else{
			foreach($collection as $item){
				if(!isset($columns[$i])){$columns[$i]='';}
				$author = new User($item->uid);
				$r .= dv('Photo preview home-thumbnail left');
				$r .= '<p>'.lnk($item->img('thumb'),'photo/'.$item->id).'</p>';
				$r .= lnk($item->title().' '.t('by').' '.$author->fullName(),'photo/'.$item->id,array(),array('title'=>$item->title().' '.t('by').' '.$author->fullName()));
				$r .= xdv();
			}
		}
		
		$a='<a href="javascript:;" class="toggleinfinite" data-reverse="/img/infinite-inactive.png"><img src="/img/infinite-active.png" title="'.t('Stop infinite load').'" alt="stop"/></a><a href="javascript:;"><img src="/img/to-top.png" onclick="$(\'body,html\').animate({\'scrollTop\':0},200);" alt="to top" title="'.t('Scroll to top').'"/></a>';
		$r .= '<br class="clearfloat"/>';
		//$r .= dv('fixed pageTool').$a.xdv();
		if(!IS_AJAX){$r .= xdv();}
		App::$user->data['settings']['homenotif'] = time();
		return $r;
	}
	
	public function login(){
		$r = '';
		if(isset(App::$input['email']) && isset(App::$input['password']) && User::login(App::$input['email'],App::$input['password'])){
			return App::$user->data();
		}
		return 401;
	}
	
	protected function welcome(){
		if(App::$user->id!=0){return $this->directory();}
		T::$page['title']= t('Photo network for photographers and other image professionals');
		T::$page['description'] = t('Pixyt is a platform connecting photographers and organizations in the photography industry, enabling them to sell, share and organize creative content.');
		return array('promoted'=>array('anais-zombini.jpg'));
	}
	
	public function passwordrecovery(){
		T::$page['title'] = t('Password recovery');
		$r = '';
		$r .= dv('d900 center');
		if(isset($_REQUEST['uid']) && isset($_REQUEST['code'])){
			$user = new User($_REQUEST['uid']);
			$r .= dv('passwordRecoveryInfo');
			$r .= '<h2>'.t('Set a new password').'</h2>';
			$r .= '<p class="info">'.t('Just one final step to be able log into your account: set a new password.').'</p>';
			$r .= xdv();
			$r .= dv('padder');
			$r .= $user->setNewPassword($_REQUEST['code']);
			$r .= xdv();
			return $r;
		}
		if(isset($_REQUEST['email'])){//the one posted in the form below
			if(!Object::objectExists('User',$_REQUEST['email'],'email',$id)){
					$r .= dv('splitLeft passwordRecoveryInfo').dv('padder centerText').'<h3 class="pattern">'.t('No accounts where found matching that email.').'</h3>'.xdv().xdv();
			}
			else{
				$user = new User($id);
				if($user->sendPasswordRecoveryCode()){
					$r .= dv('passwordRecoveryInfo');
					$r .= '<h3 class="quote">'.t('An email was sent to you, please click the link in your inbox to reset your password.').'</h3>'.xdv();
					return $r;
				}
				else{
					$r .= dv('passwordRecoveryInfo').'<h3 class="quote error">'.t('Failed to send your recovery code.').'(uid:'.$id.')</h3>'.xdv();
				}
			}
		}
		else{
			$r .= dv('passwordRecoveryInfo').'<h2>'.t('Forgot your password, huh?').'</h2>';
			$r .= '<p class="info">'.t('Don\'t worry, recovery is super easy, just specify the email used to log in.').'</p>';
			$r .= xdv();
		}
		$r .= dv('padder');
		$form = new Form(NULL,NULL,'passwordrecovery');
		$form->email('text');
		$form->{t('recover password')}('submit');
		$r .= $form->returnContent();
		$r .= xdv();
		$r .= xdv();
		return $r;
	}
	
	public function fblogin(){
		$r = '';
		if(isset($_REQUEST['backurl'])){$_SESSION['backurl'] = urldecode($_REQUEST['backurl']);}
		if(!isset($_SESSION['backurl'])){$_SESSION['backurl'] = HOME;}
		if($_SESSION['uid']!=0){header('location:'.$_SESSION['backurl']);}
		
		$facebook = new Facebook(array(
		  'appId'=>'508730959165083',
		  'secret'=>'8399e768bf7b6c14ac349c841a6aadaa',
		));
		$permissions = 'email,user_birthday';
		$user = $facebook->getUser();
		if($user){
			try{
				$q = array(
					'method'=>'fql.query',
					'query'=>'SELECT uid,sex,email,birthday_date,pic_square,first_name,last_name FROM user WHERE uid = me()'
				);
				$me = $facebook->api($q);
			}
			catch (FacebookApiException $e){
				LP::addError($e);
				$user = NULL;
			}
		}
		if($user){
			if(Object::objectExists('User',$me[0]['email'],'email',$id)){
				$_SESSION['uid'] = $id;
				header('location:'.$_SESSION['backurl']);
			}
			else{
				$r .= dv('d750 center');
				$r .= '<h1><img src="'.$me[0]['pic_square'].'"/>Hello '.$me[0]['first_name'].'</h1>';
				$r .= '<h2>'.t('You are just one step away from your account.').'</h2>';
				T::$page['title'] = t('Sign up');
				$form = new Form('User','','',array('class'=>'full'));
				$r .= $form->head;
				$r .= $form->input('input',$me[0]['first_name'],false);
				$r .= $form->lastname('input',$me[0]['last_name'],false);
				$r .= $form->email('input',$me[0]['email'],false);
				$r .= $form->profile_function('select',$f,t('Function'));
				$r .= $form->birthday('date',$me[0]['birthday_date'],t('birthday'));
				$r .= $form->password('password','',t('password'));
				$r .= $form->terms('checkbox',true,t('Accept the').' '.lnk(t('terms'),'termsofuse','',array('title'=>t('Terms of use'),'data-type'=>'popup')).' and '.lnk(t('privacy'),'privacy','',array('title'=>t('Privacy policy'),'data-type'=>'popup')));
				$r .= $form->{t('Sign up')}('submit');
				$r .= $form->foot;
				$r .= xdv();
			}
		}
		else{
			header('location:'.$facebook->getLoginUrl(array('redirect_uri'=>HOME.'fblogin','scope'=>$permissions)));
			return;
		}
		return $r;
	}
	
	public function signedup(){
		
	}
	
	public function signup(){
		$r = '';
		if(isset($_REQUEST['User_insert'])&&$_REQUEST['User_insert']){
			header('location:'.HOME.'?intro=1');
			return;
		}
		if(App::$user->id != 0){
			T::$page['title'] = t('Welcome!');
		    $r .= '<h1>Welcome aboard!</h1>';
			$r .= dv('lightgrey big bold').t('Welcome to your new account. Start browsing the website using the top menu.').xdv();
			return $r;
		}
		else{
			T::$page['title'] = t('Sign up');
			$form = new Form('User','','',array('class'=>'full signup'));
			$r .= $form->head;
			$f=array();
			$r .= $form->input('firstname',array('type'=>'text','placeholder'=>t('Firstname')));
			$r .= $form->input('lastname',array('type'=>'text','placeholder'=>t('Lastname')));
			$r .= $form->input('email',array('type'=>'email','placeholder'=>t('Your email')));
			$r .= $form->date('birthday',array('type'=>'date','format'=>array('y','m','d')));
			$r .= $form->input('password',array('type'=>'password'));
			$r .= '<p>'.t('By creating an account you accept our {$1}.',lnk(t('Terms of Service'),'termsofuse','',array('title'=>t('Terms of service'),'data-type'=>'popup'))).'</p>';
			$r .= $form->free('<br/><button type="submit" class="btn btn-green">'.t('Sign up').'</button><a class="btn fblogin btn-grey right" href="'.HOME.'fblogin"><img src="/img/share/facebook-white-100x100.png" height="12" alt="'.t('connect with facebook').'"/> '.t('connect with facebook').'</a>');
			$r .= $form->foot;
			//$r .= $form->content();
		}
		$this->sidebar('<h2>'.t('Have an account?').'</h2>'.lnk('Sign in','login','',array('class'=>'btn btn-grey')));
		return $r;
	}
	
	public function picklang(){
		return $this->language();
	}
	
	public function language(){
		$r = '';
		if(isset($_REQUEST['back'])){$url = urldecode($_REQUEST['back']);}else{$url='';}
		$r .= dv('d600 center');
		$r .= lnk('English (UK)',$url,array('lang'=>'en_GB'),array('data-type'=>'plain','class'=>'btn'));
		$r .= lnk('Français (France)',$url,array('lang'=>'fr_FR'),array('data-type'=>'plain','class'=>'btn'));
		return $r;
	}

	public function test($what='launch-email'){
		$data = array('title'=>'Thank you for your interest in joining the Pixyt network. You will receive an invitation as soon as we launch.','host'=>HOST);
		die(templateEmail('contact@samueldelesque.com','Sam','crew@pixyt.com','A present for you, from Pixyt.','assets/themes/pixyt/tpl/email/launch_signup.html',$data,true));
	}
	
	public function subscribe(){
		T::$page['title'] = t('Follow us');
		$r = dv('inner-page');
		if(isset($_REQUEST['Subscriber']) && $_REQUEST['Subscriber'] != 0){
			T::$body[] = dv('info').'<h1>'.t('Thank you. You will now receive occasional updates from us.').'</h1>'.xdv();
		}
		else{
			$form = new Form('Subscriber',NULL,'subscribe',array('ajax'=>true));
			$form->input('email',array('placeholder'=>t('Your email')));
			$form->button(t('Subscribe'),array('class'=>'btn-success'));
			$r .= $form->content();
		}
		$r .= xdv();
		return $r;
	}
	
	public function about(){
		$r = '';
		T::$page['title'] = t('Contact');
		$r .= dv('','contactus');
		$r .= '<h1 class="lightgrey quote">“'.t('How can we help you?').'”</h1>';
		$form = new Form('Message',NULL,'contact',array('class'=>'full'));
		$form->uid('hidden','1');
		if(App::$user->id==0){
			$form->name('input',t('name'),false);
			$form->role('select',array('photographer','agent or studio','communication agency','corporate','other'));
			$form->from('input',t('email'),false);
		}
		else{
			$form->from('hidden',App::$user->id);
		}
		$form->content('textarea',false,false);
		$form->send('submit');
		$r .= dv('padded').$form->returnContent().xdv();
		$r .= dv('rightText').'<span class="white">Ask us live</span><a href="//facebook.com/pixytdotcom" rel="nofollow" target="_blank"><img src="/img/share/facebook-white-100x100.png" height="25" alt="Pixyt on facebook" title="Our facebook page"></a>';
		$r .= '<a href="//twitter.com/pixytdotcom" rel="nofollow" target="_blank"><img src="/img/share/twitter-white-100x100.png" height="25" alt="Pixyt on Twitter" title="Our Twitter page"></a>'.xdv();
		
		$r .= '<br class="clearfloat"/>'.xdv();

		return $r;
	}
	
	public function maps($loc=''){
		T::$page['title'] = $loc;
		$loc = urlencode($loc);
		return dv('map').'<iframe width="100%" height="100%" id="map_canvas" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.fr/maps?f=q&amp;source=s_q&amp;hl=fr&amp;geocode=&amp;q='.$loc.'&amp;aq=0&amp;oq='.$loc.'&amp;ie=UTF8&amp;hq='.$loc.'&amp;t=m&amp;output=embed"></iframe>'.xdv();
	}
}
?>