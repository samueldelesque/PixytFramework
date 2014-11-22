<?php
class Corp extends Interfaces{
	
	public function __construct(){
		T::$template = array('body');
		T::$page['icon'] = 'img/lightarchitect.ico';
		T::$page['description'] = translate('Light Architect is a web orientated photography agency. It provides high quality tools for professionnal photographers who intend to make a living with their art.');
		define('USER_ANALYTICS','UA-40552887-1');
		return true;
	}
	
	public function photo($id=''){
		header('Location: '.HOME,true,301);
	}
	
	public function createsite(){
		$r = '';
		$r .= $this->menu();
		$r .= dv('d1000 center');
		$r .= dv('hero-unit').'<h1>Get a beautifully designed website today.</h1>'.xdv();
		$r .= dv('d3x2 col');
		$r .= dv('padded').'<h2>Get our special offer</h2><p>Order today for 99$, limited offer only!<span class="discount">save 50%</span></p>';
		$r .= '<h3>We build websites that are responsive, easy to use and totally customisable.</h3>';
		$r .= '<p>All websites include a .com domain (or any other domain) and hosting with unlimitted traffic, SEO optimisation, friendly support, integrated CMS and much much more. Send us an email for more details!</p>';
		$r .= xdv();
		
		$form = new Form('Message',NULL,'contact',array('class'=>'full'));
		$form->uid('hidden','1');
		if(App::$user->id==0){
			$form->name('input',translate('name'),false);
			$form->from('input',translate('email'),false);
		}
		else{
			$form->from('hidden',App::$user->id);
		}
		$form->content('textarea','Want to know more?',false);
		$form->send('submit');
		$r .= $form->returnContent();
		$r .= xdv();
		$r .= dv('d3 right').'<ul class="padded">
<li><img src="http://pix.yt/img/site_editor/edit.png" height="30"/> 100% editable</li>
<li><img src="http://pix.yt/img/site_editor/add_photo.png" height="30"/> add your own photos</li>
<li><img src="http://pix.yt/img/site_editor/add_page.png" height="30"/> add pages yourself</li>
<li><img src="http://pix.yt/img/site_editor/color.png" height="30"/> customize everything</li>
<li><img src="http://pix.yt/img/site_editor/stats.png" height="30"/> all your stats</li>
<li><img src="http://pix.yt/img/site_editor/heart.png" height="30"/> ... and much more!</li>
</ul>';
		$r .= xdv().'<br class="clearfloat"/>';
		$r .= xdv();
		$r .= self::footmenu();
		return $r;
	}
	
	function promoted(){header('Location: '.HOME.'blog',true,301);}
	
	public function blog(){
		$r = '';
		$r .= $this->menu();
		T::$page['title'] = translate('Light Architect blog - a currated photography stream');
		if(!IS_AJAX){
			$r .= dv('hero-unit').dv('d1000 center').'<h1>'.translate('Editors pick').'</h1>'.xdv().xdv().dv('d1000 center').dv('append','feeds','data-s="1"');
		}
		$col = new Collection('Feedback');
		$col->type=5;
		if($col->total(true) < $_REQUEST['s']*10){
			if($col->total(true) >= $_REQUEST['s']*10-10)
			$r .= '<h1 class="grey centerText">'.translate('No more feeds to show.').'</h1>';
			res('script','$(".loadMore").slideUp();');
		}
		else{
			$col->load($_REQUEST['s']*10,10);
			foreach($col->results as $f){
				if(!empty($f->objectType)){
					$o = Object::$objectTypes[$f->objectType];
					
					if(!Object::objectExists($o,$f->objectId)){
						self::addError($o.'::'.$f->objectId.' had to be deleted from blog as not existing',10);
						$f->delete(true);
					}
					else{
						$el = new $o($f->objectId);
						$author = new User($f->uid);
						$r .= $el->feed('600Wide');
					}
				}
			}
		}
		if(!IS_AJAX){
			$r .= xdv().'<br class="clearfloat"/>';
			$r .= dv('loadMore').lnk('...','#cur',array(),array('data-containerid'=>'feeds','data-gethtml'=>true)).xdv();
			$r .= xdv();
		}
		return $r;
	}
	
	public function magazine(){
		$r = '';
		$r .= $this->menu();
		$r .= dv('hero-unit').dv('d1000 center').'<h1>LAM magazine</h1><h2>number One issue</h2><h3>Get the <a href="http://lightarchitect.com/pdf/lam-1.pdf">PDF</a></h3>'.xdv().xdv();
		$r .= dv('d1200 center centerText magazine');
		for($i=1;$i<=16;$i++){
			if($i==1){$w=1190/2;$class='half right';}
			elseif($i==16){$w=1190/2;$class='half left';}
			else{$w=1190;$class='';}
			$r .= dv('').'<img src="/img/lam-1/lam'.$i.'.jpg" width="'.$w.'" alt="LAM Magazine page '.$i.'" class="nocopy mag-page '.$class.'"/>'.xdv();
		}
		$r .= xdv().'<br class="clearfloat"/>';
		$r .= dv('d1000 center').dv('hero-unit').'<p class="padded"><h2>Photographers:</h2> '.frmt('
Anna Téa 
Samuel Delesque samueldelesque.com 
Flavio Montrone flaviomontrone.com 
Synchrodogs synchrodogs.com 
Kimdary Yin kimdaryyin.com 
Shajjad Hossain Shajib shshajib.com 
Louise Desnos louisedesnos.pixyt.com 
Dia Takácsová  
Andi Schmied hi-imandi.com 
Mina Delic pixyt.com/user/764 
Téo Jaffre teojaffre.com 
Glorija Lizde glojiralizde.wix.com 
Ophélie Longuépée ophelielonguepee.com 
Davy Rigault davyrigault.com 
Mary Jeanne mariejeannesson.pixyt.com 
').'</p>'.xdv().xdv().'<br/>';
		$r .= self::footmenu();
		return $r;
		/*
		if(!isset($_REQUEST['issuu']))return '<object data="http://lightarchitect.com/pdf/lam-1.pdf" type="application/pdf" width="100%" height="600px"><p>It appears you don\'t have a PDF plugin for this browser. No biggie... you can <a href="http://lightarchitect.com/pdf/lam-1.pdf">click here to download the PDF file.</a></p>
</object>';
		return dv('magazine').'<div data-configid="8015628/2240518" style="width: 100%; height: 700px;" class="issuuembed"></div><script type="text/javascript" src="//e.issuu.com/embed.js" async="true"></script>'.xdv();
		*/
	}
	
	private function menu($active=''){
		$r = '';
		$r .= dv('','corpMenu').dv('inner-content');
		$r .= dv('d2 left').lnk('<img src="'.HOME.'img/logo/la/logo-lightarchitect.png" id="lightarchitectLogo" alt="Light Architect (agency logo)" title="'.translate('Light Architect: Photography agency and network.').'" height="120"/>');
		$r .= xdv();
		$r .= dv('d2 right rightText');
		$c = '';if($active == 'agency'){$c='active';}
		$r .= lnk(translate('Agency'),'agency',array(),array('title'=>translate('Light Architect: Photography agency and network.'),'class'=>$c));
		$c = '';if($active == 'magazine'){$c='active';}
		$r .= lnk(translate('Magazine'),'magazine',array(),array('title'=>translate('LAM magazine'),'class'=>$c));
		//$c = '';if($active == 'about'){$c='active';}
		//$r .= lnk(translate('About'),'about',array(),array('title'=>translate('About the agency'),'class'=>$c));
		//$r .= lnk(translate('photographers'),'photographers',array(),array('title'=>translate('Photographers in the agency')));
		//$r .= lnk(translate('Contest'),'contest',array(),array('title'=>translate('Photography contest!'),'class'=>'blue'));
		$c = '';if($active == 'promoted'){$c='active';}
		$r .= lnk('blog','promoted',array(),array('class'=>$c));
		$c = '';if($active == 'contact'){$c='active';}
		$r .= lnk('contact','contact',array(),array('title'=>translate('Contact information, quotes or other such.'),'class'=>$c));
	//	$r .= lnk(Translate::$languages[$_SESSION['lang']],'picklang',array('back'=>urlencode($_REQUEST['url'])),array('data-type'=>'popup','class'=>'right'));
		//$r .= lnk('subscribe','subscribe',array(),array('title'=>translate('Subscribe to our newsletter.'),'data-type'=>'popup','class'=>'right','style'=>'margin-right:10px')).xdv();
		$r .= xdv().'<br class="clearfloat"/>'.xdv().xdv();
		return $r;
	}
	
	private function footmenu($active=''){
		$r = '';
		$r .= dv('','corpFootMenu').dv('d1000 center');
		$r .= '<ul class="d5 left">';
		$r .= '<li><h3>'.translate('Photography').'</h3></li>';
		$r .= '<li>portrait</li>';
		$r .= '<li>events</li>';
		$r .= '<li>interior design</li>';
		$r .= '<li>corporate</li>';
		$r .= '<li>fashion</li>';
		$r .= '</ul>';
		
		$r .= '<ul class="d5 left">';
		$r .= '<li><h3>'.translate('Digital').'</h3></li>';
		$r .= '<li>'.lnk('Pixyt','about').'</li>';
		$r .= '<li>'.lnk('Webdesign','createsite').'</li>';
		$r .= '<li>retouching</li>';
		$r .= '</ul>';
		
		$r .= '<ul class="d5 left">';
		$r .= '<li><h3>'.translate('Magazine').'</h3></li>';
		$r .= '<li>'.lnk('May 2013','magazine/lam-1').'</li>';
		$r .= '</ul>';
		$r .= xdv().'<br class="clearfloat"/>'.xdv();
		return $r;
	}

	public function about(){
		$r = '';
		$r .= $this->menu();
		$v = $_SESSION['lang'];
		if(!in_array($v,array('fr_FR','en_GB'))){
			$v='en_GB';
		}
		T::$page['title'] = translate('Light Architect - photography agency and network');
		$r .= dv('centerText center','','style="width:900px"').'<h2 style="margin-top:50px;font-size:55px;color:#555;">'.translate('List of services').'</h2>';
		$r .= '<img src="/img/schemes/services-'.$v.'.png" alt="organigramme-services.jpg" class="contentBox" width="100%"/>';
		$r .= dv('').'<br/><img src="/img/bubbletop.png" style="margin-left:120px"/>'.dv('bubble').'<p>'.translate('The concept is to propose a large panel of services, mostly for free, adding a small fee on sales.').'</p><p>'.translate('Creatives can also create their own website and buy domain names directly from their account.').'</p>'.xdv().xdv();
		$r .= dv('twothird center','','style="width:900px"').'<h2 style="margin-top:50px;font-size:55px;color:#555;">Site Map</h2><img src="/img/schemes/site_structure.png" id="site_structure_map" usemap="#m_site_structure_map" alt="" /><map name="m_site_structure_map" id="m_site_structure_map">
<area shape="rect" coords="268,554,440,622" href="http://lightarchitect.com/photographers" target="_self" title="photographers" alt="photographers" />
<area shape="rect" coords="378,193,556,255" href="http://pixyt.com" target="_blank" title="pixyt network" alt="pixyt network" />
<area shape="rect" coords="387,113,557,164" href="http://pixyt.com" target="_blank" title="pixyt network" alt="pixyt network" />
<area shape="rect" coords="387,34,548,87" href="http://lightarchitect.com" target="_self" title="Light Architect" alt="Light Architect" />
</map>'.xdv();
/*
		$r .= dv('twothird center').'<h2 style="margin-top:50px;font-size:55px;color:#555;">'.translate('Who are we?').'</h2>';
		$r .= dv('contact').'<h3>Sam</h3><p class="grey">Coder+photographer</p>'.xdv();
		$r .= dv('contact').'<h3>Robin</h3><p class="grey">Computer Genius</p>'.xdv();
		$r .= dv('contact').'<h3>Amaury</h3><p class="grey">Very human Salesman</p>'.xdv();
		$r .= dv('contact').'<h3>Harper</h3><p class="grey">Content editor</p>'.xdv().xdv();
		*/
		return $r;
	}
	
	public function directory(){
		return $this->agency();
	}
	
	function photographyagency(){
		header('Location: '.HOME.'agency',true,301);
	}
	
	public function agency(){
		$r = '';
		$r .= $this->menu();
		T::$page['title'] = translate('Light Architect - photography agency and network');
		$r .= dv('d1000 center');
		$r .= dv('d400 left');
		$r .= '<h1>Light Architect is a digital and photography agency</h1><br/>';
		$r .= '<p>We are photographers, designers and coders. We can shape your visual identity, build your website and shoot your images.</p>';
		$r .= xdv();
		$r .= dv('d600 right').dv('relative d600 h400');
		$r .= dv('slider','','data-delay="4500" data-effect="fade"');
		$r .= dv('slide').'<img src="/img/agency-images/agency-header-01.png" alt="'.translate('We build designs that stand out').'" width="600"/>'.xdv();
		$r .= dv('slide').'<img src="/img/agency-images/agency-header-02.jpg" alt="'.translate('We produce images').'" width="600"/>'.xdv();
		$r .= dv('slide').'<img src="/img/agency-images/agency-header-03.png" alt="'.translate('We hold photo contests').'" width="600"/>'.xdv();
		$r .= dv('slide').'<img src="/img/agency-images/agency-header-04.png" alt="'.translate('We are a network of hundreds of creatives').'" width="600"/>'.xdv();
		$r .= xdv();
		$r .= xdv();
		$r .= xdv();
		$r .= '<br class="clearfloat"/>';
		$r .= xdv();
		$r .= $this->footmenu();
		return $r;
	}
	
	public function network(){
		$r = '';
		$r .= $this->menu();
		T::$page['title'] = translate('Light Architect - photography agency and network');
		$r .= '<img src="/img/schemes/site_structure.png" id="site_structure_map" usemap="#m_site_structure_map" alt="" /><map name="m_site_structure_map" id="m_site_structure_map">
<area shape="rect" coords="268,554,440,622" href="http://lightarchitect.com/photographers" target="_self" title="photographers" alt="photographers" />
<area shape="rect" coords="378,193,556,255" href="http://pixyt.com" target="_blank" title="pixyt network" alt="pixyt network" />
<area shape="rect" coords="387,113,557,164" href="http://pixyt.com" target="_blank" title="pixyt network" alt="pixyt network" />
<area shape="rect" coords="387,34,548,87" href="http://lightarchitect.com" target="_self" title="Light Architect" alt="Light Architect" />
</map>';
		$r .= $this->footmenu();
		return $r;
	}
	
	public function photographers(){
		$r = '';
		$r .= $this->menu();
		$col = new Collection('Site');
		$col->represented=1;
		$r .= '<h1 class="marger">'.translate('Featured photographers using Pixyt').'</h1>';
		$r .= $col->content('preview',0,30,true,'modified');
		$r .= dv('clearfloat').xdv().xdv();
		$r .= $this->footmenu();
		return $r;
	}
}
?>