<?php
class Website extends Interfaces{
	public function construct(){
		if(!isset($_SESSION['buy'])){$_SESSION['buy'] = array();}
	}
	
	public function directory(){
		$r = '';
		T::$page['title'] = t('Create a website');
		$r .= dv('page-inner').dv('sale-hero');
		$r .= '<h1>'.t('Pixyt is a great platform for your photos. Its also the best place to have your website.').'</h1>';
		$r .= xdv();
		
		$r .= dv('sale-options').dv('d960 center');
		if(isset($_REQUEST['error'])){
			switch($_REQUEST['error']){
				case 'select-plan-first':
					$r .= dv('alert alert-info alert-block').'<p>'.t('Please select a hosting plan for your account.').'</p>'.xdv();
				break;
				
				case 'not-your-site':
					$r .= dv('alert alert-error alert-block').'<p>'.t('You do not own that website.').'</p>'.xdv();
				break;
			}
		}
		$r .= dv('quote').'<blockquote class="italic big">"Le service site web qu\'offre Pixyt est fait pour les créatifs comme moi qui veulent une interface simple et robuste permettant de personnaliser très facilement le design. J\'aime aussi la disponibilité du service technique !"</blockquote><span class="right">Jérémy Cuenin, '.xtlnk('http://jeremycuenin.com/?utm_source=pixyt&utm_campaign=customer_feedback','jeremycuenin.com',array('target'=>'blank','class'=>'grey')).'</span><br class="clearfloat">'.xdv();
		$r .= dv('quote').'<blockquote class="italic big">"Sobre, efficace et ergonomique, il est simple de poster, organiser, modifier des vidéos, photos et articles. Pas de publicités. Site très astucieux."</blockquote><span class="right">Cyril Masson, '.xtlnk('http://cyril-masson.com/?utm_source=pixyt&utm_campaign=customer_feedback','cyril-masson.com',array('target'=>'blank','class'=>'grey')).'</span><br class="clearfloat">'.xdv();
		foreach(Product::packages() as $name=>$plan){
			if(isset($plan['emphasized'])){$c = 'emphasized pricing-plan';}
			else{$c='pricing-plan';}
			if(isset($plan['notyet'])){$c .= ' faded';}
			$r .= dv('d2 left').'<ul class="'.$c.'">';
			if($plan['billing'] == 'monthly'){$t = '<span class="small">/m</span>';}
			else{$t = '<span class="small">/y</span>';}
			$r .= '<li><h2 class="name">'.$plan['label'].'</h2></li>';
			$r .= '<li><h3 class="price">'.price2str($plan['price']).$t.'</h3></li>';
			$r .= '<li><h4 class="space">'.prettyBytes($plan['space']).'</h4></li>';
			$r .= '<li><ul class="options">';
			foreach($plan['options'] as $option=>$value){
				switch($option){
					case 'domain':
						$r .= '<li><img src="http://pixyt.com/img/sale/domain.png" alt="domain" title="domain" width="24"/> '.t('your own www.domain.com').'</li>';
					break;
					
					case 'traffic':
						$r .= '<li><img src="http://pixyt.com/img/sale/traffic.png" alt="traffic" title="traffic" width="24"/> '.t('unlimited traffic').'</li>';
					break;
					
					case 'drag_drop':
						$r .= '<li><img src="http://pixyt.com/img/sale/drag_drop.png" alt="drag and drop" title="drag and drop" width="24"/> '.t('easy drag and drop interface').'</li>';
					break;
					
					case 'customize':
						$r .= '<li><img src="http://pixyt.com/img/sale/customize.png" alt="customize" title="customize" width="24"/> '.t('customizable - your website your way').'</li>';
					break;
					
					case 'custom_scripts':
						$r .= '<li><img src="http://pixyt.com/img/sale/custom_scripts.png" alt="custom_scripts" title="custom_scripts" width="24"/> '.t('use custom HTML/CSS').'</li>';
					break;
					
					case 'no_branding':
						$r .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.t('no branding').'</li>';
					break;
					
					case 'premium_support':
						$r .= '<li><img src="http://pixyt.com/img/sale/support.png" alt="support" title="support" height="24"/> '.t('24h/7 support').'</li>';
					break;
					
					case 'design_assistance':
						$r .= '<li><img src="http://pixyt.com/img/sale/design.png" alt="design" title="design" height="24"/> '.t('design assistance').'</li>';
					break;
				}
			}
			$r .= '</li></ul>';
			if(isset($_REQUEST['promo']) && isset(Sale::$promoCodes[$_REQUEST['promo']])){
				$_SESSION['buy']['promo'] = Sale::$promoCodes[$_REQUEST['promo']];
				$r .= '<li class="padded centerText">'.t($_SESSION['buy']['promo']['label']).'<span class="discount">'.t('{$1} off!',($_SESSION['buy']['promo']['reduction']*100).'%').'</span></li>';
			}
			if(isset($_REQUEST['renew']) && Object::objectExists('Site',$_REQUEST['renew'])){
				$lnk = lnk(t('select'),'website/validate',array('plan'=>$name,'renew'=>$_REQUEST['renew']),array('class'=>'btn btn-success btn-large'));
			}
			else{
				$lnk = lnk(t('select'),'website/domain',array('plan'=>$name),array('class'=>'btn btn-success btn-large'));
			}
			if(App::$user->id == 1 || !isset($plan['notyet'])){
				$r .= '<li class="centerText"><div class="padded block">'.$lnk.'</div></li>';
			}
			else{
				$r .= '<li><h4>Coming soon!</h4></li>';
			}
			$r .= '</ul>'.xdv();
		}
		$r .= '<br class="clearfloat"/>';
		$r .= xdv();
		$r .= xdv();
		return $r;
	}
	
	public static function domain(){
		$r='';
		//set the plan option
		if(isset($_REQUEST['plan']) && !is_array(Product::packages($_REQUEST['plan']))){
			$_SESSION['buy']['plan'] = $_REQUEST['plan'];
		}
		if(isset($_REQUEST['domain'])){$_REQUEST['domain'] = urldecode($_REQUEST['domain']);}
		
		
		
		T::$page['title'] = translate('Create a website');
		$r .= dv('page-inner');
		$r .= dv('d960 center','createSite');
		$r .= dv('progress-nav i5');
		$r .= lnk(t('plan'),'website',array(),array('class'=>'done'));
		$r .= '<span class="active">'.t('domain').'</span>';
		$r .= '<span class="pending">'.t('billing').'</span>';
		$r .= '<span class="pending">'.t('payment').'</span>';
		$r .= '<span class="pending">'.t('invoice').'</span>';
		$r .= xdv();
		$r .= dv('inner-content');
		$r .= '<h1>'.t('Choose a domain name').'</h1>';
		
		if(isset($_REQUEST['error'])){
			switch($_REQUEST['error']){
				case 'invalid-domain':
				case 'disalow-domain':
				case 'wrong-tld':
					$r .= dv('alert alert-error alert-block').'<h4>'.t('Invalid domain!').'</h4><p>'.t('Make sure you enter a domain containing between 3 and 253 characters followed by the tld of your choise (.com .net .dk etc)').'</p>'.xdv();
				break;
				
				case 'not-available':
					$r .= dv('alert alert-warning alert-block').'<h4>'.t('Not available.').'</h4><p>'.t('Sorry, {$1} is not available. If you own this domain, please contact us at crew@pixyt.com to tranfer the domain, or make it point to our servers.',$_REQUEST['domain']).'</p>'.xdv();
				break;
				
				case 'order-exists':
					$r .= dv('alert alert-warning alert-block').'<h4>'.t('Domain already ordered!').'</h4><p>'.t('The domain {$1} has already been ordered. If you ordered it, go to your orders and confirm it. If you believe there is a mistake, please contact us at crew@pixyt.com',$_REQUEST['domain']).'</p>'.xdv();
				break;
			}
		}
		
		$cart = new Form('','','website/validate',array('class'=>'inline'));
	//	$cart->input('plan',array('type'=>'hidden','value'=>$_SESSION['buy']['plan']));
		if(isset($_REQUEST['domain'])){
			$cart->input('domain',array('type'=>'text','value'=>$_REQUEST['domain']));
		}
		else{
			$cart->input('domain',array('type'=>'text','placeholder'=>t('yourname.com')));
		}
		$cart->button(t('Check availability'),array('class'=>'continue btn btn-success'));
		
		$r .= dv('select-domain').$cart->content().xdv();
		$r .= xdv();
		/*
		$r .= dv('d320 col').dv('order-summary');
		$r .= '<h3>'.t('Summary').'</h3>';
		$r .= dv('padded').dv('progress').dv('bar','','style="width:20%"').xdv().xdv().xdv();
		$r .= '<ul>';
		$r .= '<li>Plan: <strong>'.Product::$packages[$_SESSION['buy']['plan']]['label'].'</strong></li>';
		$r .= '<li>Price: <strong>'.price2str(Product::$packages[$_SESSION['buy']['plan']]['price']).' '.Product::$packages[$_SESSION['buy']['plan']]['billing'].'</strong></li>';
		$r .= '</ul>';
		$r .= xdv();
		$r .= xdv();
		*/
		$r .= xdv();
		$r .= xdv();
		return $r;
	}
	
	public function validate(){
		$r = '';
		T::$page['title'] = t('Billing');
		
		//if a plan is set - if coming straight for a renew for instance
		if(isset($_REQUEST['plan']) && isset(Product::$packages[$_REQUEST['plan']])){
			$_SESSION['buy']['plan'] = $_REQUEST['plan'];
		}
		
		//if no plan is selected, redirect (if the user doesnt have one already)
		if(!isset($_SESSION['buy']['plan']) && App::$user->plan == 'basic'){
			header('location: http://pixyt.com/website?error=select-plan-first&utm_medium='.urlencode(HOST).'&utm_campaign=website_validate');
			exit();
		}
		
		//Lets insert the plan Sale
		/*
		if(isset($_SESSION['buy']['plan']) && !isset($_SESSION['buy']['plan_inserted'])){
			$sale = new Sale();
			$sale->validateData('plan',$_SESSION['buy']['plan']);
			if(!$sale->insert()){
				header('location: http://pixyt.com/website?error=plan-insert-failed&utm_medium='.urlencode(HOST).'&utm_campaign=website_validate');
				exit();
			}
			else{
				$_SESSION['buy']['plan_inserted'] = $sale->id;
			}
		}
		*/
		
		//Check the newly selected domain
		if(isset($_REQUEST['domain'])){
			$parts = explode('.',strtolower($_REQUEST['domain']));
			$domain = strtolower($parts[0]);
			unset($parts[0]);
			$tld = implode('.',$parts);
			
			$siteurl = $domain.'.'.$tld;
			
			$curSales = new Collection('Sale');
			$curSales->title = $siteurl;
			$curSales->status('=',0,true);
			
			//the site is already regitered on Pixyt, lets check if the current user owns it..
			if(Object::objectExists('Site',$siteurl,'url',$id)){
				$site = new Site($id);
				if($site->uid == App::$user->id){
					//lets show the renew option instead of create...
					$_SESSION['buy']['renew'] = $site->id;
					$siteurl = $site->url;
				}
				else {
					header('location: http://pixyt.com/website/domain?domain='.urlencode($_REQUEST['domain']).'&error=not-available');
					exit();
				}
			}
			else{
				if(strlen($domain) > 253 || strlen($domain) <= 3){
					header('location: http://pixyt.com/website/domain?domain='.urlencode($_REQUEST['domain']).'&error=invalid-domain');
					exit();
				}
				elseif(in_array($_REQUEST['domain'],Site::$disalowUrls)){
					header('location: http://pixyt.com/website/domain?domain='.urlencode($_REQUEST['domain']).'&error=disalow-domain');
					exit();
				}
				elseif($curSales->total(true) > 0){
					header('location: http://pixyt.com/website/domain?domain='.urlencode($_REQUEST['domain']).'&error=order-exists');
					exit();
				}
				elseif(!isset(Product::$tldPrices[$tld])){
					header('location: http://pixyt.com/website/domain?domain='.urlencode($_REQUEST['domain']).'&error=wrong-tld');
					exit();
				}
				else{
					if(!in_array($tld,Site::$pixytSites)){
						$soap = new SoapClient('https://www.ovh.com/soapi/soapi-re-1.49.wsdl');
						$session = $soap->login(OVH_NIC,OVH_PWD,'fr',false);
						$result = (array)$soap->domainCheck($session,$siteurl);
					}
					if(in_array($tld,Site::$pixytSites) || $result[0]->value == 1){
						//Set the session
						$_SESSION['buy']['domain'] = $siteurl;
					}
					else{
						header('location: http://pixyt.com/website/domain?domain='.urlencode($_REQUEST['domain']).'&error=not-available');
						exit();
					}
				}
			}
		}
		
		//domain was set once, and is set in session
		elseif(isset($_SESSION['buy']['domain'])){
			//redo the domain parts to check for pricing this time
			$siteurl = $_SESSION['buy']['domain'];
		}
		
		//renewing some site
		elseif(isset($_REQUEST['renew'])){
			if(!Object::objectExists('Site',$_REQUEST['renew'])){
				header('location: http://pixyt.com/website/domain?error=site-not-exists');
				exit();
			}
			//set this once and for all
			$_SESSION['buy']['renew'] = $_REQUEST['renew'];
			$site = new Site($_SESSION['buy']['renew']);
			$siteurl = $site->url;
		}
		
		//renewing site mode has been enabled
		elseif(isset($_SESSION['buy']['renew'])){
			$site = new Site($_SESSION['buy']['renew']);
			$siteurl = $site->url;
		}
		
		//sorry, what?
		else{
			header('location: http://pixyt.com/website/domain?error=unknown-error');
			exit();
		}
		
		$parts = explode('.',$siteurl);
		$domain = strtolower($parts[0]);
		unset($parts[0]);
		$tld = implode('.',$parts);
		
		//This will contain all the billing info on the right
		$bill = array();
		
		//if set the number of months to pay on first round
		if(!isset($_REQUEST['duration'])){
			if(isset($_SESSION['buy']['duration'])){
				//no new data is entered, use session
				$_REQUEST['duration'] = $_SESSION['buy']['duration'];
			}
			else{
				//this is the first visit lets pre-select the 2 year plan
				$_REQUEST['duration'] = 2;
			}
		}
		
		$isfreedomain = false;
		$_REQUEST['duration'] = intval($_REQUEST['duration']);
		if($_REQUEST['duration'] >= 10 || $_REQUEST['duration'] <= 0){
			$_REQUEST['duration'] = 2;
		}
		$_SESSION['buy']['duration'] = $_REQUEST['duration'];
		$billed_months = 12*$_REQUEST['duration'];
		
		//If this is a renew, and it is the freedomain, renew account at the same time
		if(
			App::$user->plan != 'basic' && 
			isset($_SESSION['buy']['renew']) && 
			App::$user->data['settings']['freedomain'] == $_SESSION['buy']['renew'] &&
			Object::objectExists('Site',App::$user->data['settings']['freedomain'])
		){
			$plan_price = intval(Product::$packages[App::$user->plan]['price']);
			$amt = ($plan_price*$billed_months);
			$bill[] = array('label'=>t('Hosting plan').' <span class="small grey">('.price2str($plan_price).'x'.$billed_months.')</span>','amt'=>($plan_price*$billed_months),'type'=>'major','id'=>'hosting_plan');
			
			$domain_price = 0;
			$isfreedomain = true;
			if(Product::$tldPrices[$tld] > 1000){$domain_price = Product::$tldPrices[$tld] - 1000;}
			$bill[] = array('label'=>t('Domain price').' <span class="small stroked">('.price2str(Product::$tldPrices[$tld]).')</span>','amt'=>$domain_price,'type'=>'minor','id'=>'domain_price');
		}
		
		//user already has a hosting plan, just add a domain
		elseif(App::$user->plan != 'basic'){
			$domain_price = $amt = intval(Product::$tldPrices[$tld])*$_REQUEST['duration'];
			$bill[] = array('label'=>t('Additional domain'),'amt'=>$domain_price,'type'=>'major','id'=>'domain_price');
		}
		
		//User is renewing his plan - REQUEST[domain] overides the renew ID
		elseif(isset($_SESSION['buy']['renew'])&&!isset($_REQUEST['domain'])){
			$plan_price = intval(Product::$packages[$_SESSION['buy']['plan']]['price']);
			$amt = ($plan_price*$billed_months);
			$bill[] = array('label'=>t('Hosting plan').' <span class="small grey">('.price2str($plan_price).'x'.$billed_months.')</span>','amt'=>($plan_price*$billed_months),'type'=>'major','id'=>'hosting_plan');
			
			$domain_price = 0;
			$isfreedomain = true;
			if(Product::$tldPrices[$tld] > 1000){$domain_price = Product::$tldPrices[$tld] - 1000;}
			$bill[] = array('label'=>$siteurl,'amt'=>$domain_price,'type'=>'minor','id'=>'domain_price');
		}
		
		//create a hosting plan and add domain
		else{
			$plan_price = intval(Product::$packages[$_SESSION['buy']['plan']]['price']);
			
			if(isset($_SESSION['buy']['promo'])){
				$plan_price -= ($plan_price*$_SESSION['buy']['promo']['reduction']);
			}
			$amt = ($plan_price*$billed_months);
			$bill[] = array('label'=>t('Hosting plan').' <span class="small grey">('.price2str($plan_price).'x'.$billed_months.')</span>','amt'=>($plan_price*$billed_months),'type'=>'major','id'=>'hosting_plan');
			
			if(isset($_SESSION['buy']['promo'])){
				$bill[] = array('label'=>t($_SESSION['buy']['promo']['label']),'amt'=>(-$plan_price*$billed_months*$_SESSION['buy']['promo']['reduction']),'type'=>'rabate','id'=>'discount_total');
			}
			
			//1 domain included in hosting
			if(!isset(App::$user->data['settings']['freedomain']) || empty(App::$user->data['settings']['freedomain'])){
				$domain_price = 0;
				$isfreedomain = true;
				if(Product::$tldPrices[$tld] > 1000){$domain_price = Product::$tldPrices[$tld] - 1000;}
				$bill[] = array('label'=>t('Domain price').' <span class="small stroked">('.price2str(Product::$tldPrices[$tld]).')</span>','amt'=>$domain_price,'type'=>'minor','id'=>'domain_price');
			}
			//if user already own 1 domain, he has to pay for additionals
			else{
				$domain_price = intval(Product::$tldPrices[$tld])*$_REQUEST['duration'];
				$amt += $domain_price;
				$bill[] = array('label'=>t('Domain price').' <span class="small grey">('.price2str(Product::$tldPrices[$tld]).'x'.$_REQUEST['duration'].')</span>','amt'=>$domain_price,'type'=>'minor','id'=>'domain_price');
			}
		}
		if($_REQUEST['duration'] > 1){
			$bill[] = array('label'=>t('Discount'),'amt'=>(-$amt*Sale::$durationDiscount[$_REQUEST['duration']]),'type'=>'rabate','id'=>'discount_total');
			$amt -= ($amt*Sale::$durationDiscount[$_REQUEST['duration']]);
		}
		$bill[] = array('label'=>t('Total'),'amt'=>$amt,'type'=>'total','id'=>'amt_total');
		$bill[] = array('label'=>t('including VAT'),'amt'=>($amt*0.196),'type'=>'total lightgrey small','id'=>'amt_total');
		
		$r .= dv('d960 center').dv('whitebgd');
		$r .= dv('progress-nav i5');
		$r .= lnk(t('plan'),'website',array(),array('class'=>'done'));
		$r .= lnk(t('domain'),'website/domain',array(),array('class'=>'done'));
		$r .= '<span class="active">'.t('billing').'</span>';
		$r .= '<span class="pending">'.t('payment').'</span>';
		$r .= '<span class="pending">'.t('invoice').'</span>';
		$r .= xdv();
		
		//init sales
		if(App::$user->id==0){
			//If user is not logged in, come back to here to init Sales
			$type = '';
			$url='website/validate';
		}
		else{
			$type = '';
			$url = 'website/finalize';
		}
		$form = new Form_v2($type,'',$url,array('class'=>'select-billing'));
		
		if(isset($_SESSION['buy']['plan'])){
			$form->input('plan',array('value'=>$_SESSION['buy']['plan'],'type'=>'hidden'));
		}
		elseif($isfreedomain){
			$form->input('plan',array('value'=>App::$user->plan,'type'=>'hidden'));
		}
		if(isset($_SESSION['buy']['renew'])&&!isset($_REQUEST['domain'])){
			$form->input('renew',array('type'=>'hidden','value'=>$_SESSION['buy']['renew']));
			$form->free('<h4>'.t('Renew {$1} for:','<strong>'.$siteurl.'</strong>').'</h4>');
		}
		else{
			//make sure in checkout this is the same as in session
			$form->input('domain',array('value'=>$siteurl,'type'=>'hidden'));
			$form->free('<h4>'.t('Book {$1} for:','<strong>'.$siteurl.'</strong>').'</h4>');
		}
		
		//select plan duration
		$form->fieldset(array('class'=>'radio-group duration'));
		$options = array('type'=>'radio','value'=>'1','id'=>'domain_1');
		if($_REQUEST['duration'] == 1){$options['checked'] = true;}
		$form->input('duration',$options);
		$form->label(t('1 year'),array('for'=>'domain_1'));
		$form->fieldset();
		
		$form->fieldset(array('class'=>'radio-group duration'));
		$options = array('type'=>'radio','value'=>'2','id'=>'domain_2');
		if($_REQUEST['duration'] == 2){$options['checked'] = true;}
		$form->input('duration',$options);
		$form->label(t('{$1} years',2).' <span class="discount">'.t('{$1} off!','5%').'</span>',array('for'=>'domain_2'));
		$form->fieldset();
		
		$form->fieldset(array('class'=>'radio-group duration'));
		$options = array('type'=>'radio','value'=>'3','id'=>'domain_3');
		if($_REQUEST['duration'] == 3){$options['checked'] = true;}
		$form->input('duration',$options);
		$form->label(t('{$1} years',3).' <span class="discount">'.t('{$1} off!','10%').'</span>',array('for'=>'domain_3'));
		$form->fieldset();
		
		$form->free('<p class="sale-newline"></p>');
		
		if(App::$user->id==0){
			$form->free('<h4>'.t('Personal info').'</h4>');
			$form->free('<p>'.t('Already have a Pixyt account?').' '.lnk(t('login here'),'login',array('backurl'=>urlencode('website/validate')),array('class'=>'btn btn-small')).'</p>');
			$form->free('<p>'.t('If not, please {$1}',lnk(t('create an account'),'signup',array('backurl'=>urlencode('website/validate')),array('class'=>'btn btn-small'))).'</p>');
			/*
			$form->input('insert[User][firstname]',array('auto'=>false,'placeholder'=>t('firstname')));
			$form->input('insert[User][lastname]',array('auto'=>false,'placeholder'=>t('lastname')));
			$form->input('insert[User][email]',array('auto'=>false,'placeholder'=>'email@domain.com'));
			$form->date('insert[User][birthday]',array('auto'=>false,'format'=>array('d','m','y')));
			$form->input('insert[User][email]',array('auto'=>false,'placeholder'=>'email@domain.com'));
			*/
			$form->free('<p class="sale-newline"></p>');
		}
				
		/*
		$form->fieldset(array('class'=>'radio-group'));
		$form->input('billing',array('type'=>'radio','value'=>'quaterly','id'=>'billing_quaterly'));
		$form->label(t('Bill me quaterly'),array('for'=>'billing_quaterly'));
		$form->fieldset();
		
		$form->fieldset(array('class'=>'radio-group'));
		$form->input('billing',array('type'=>'radio','value'=>'yearly','checked'=>true,'id'=>'billing_yearly'));
		$form->label(t('Bill me yearly'),array('for'=>'billing_yearly'));
		$form->fieldset();
		
		$form->free('<p class="sale-newline"></p>');
		*/
		
		//buttons
		$form->fieldset(array('class'=>'buttons'));
		$form->free(lnk(t('change domain'),'website/domain','',array('class'=>'btn left')));
		
		//do not allow non logged users to create a website
		if(App::$user->id!=0){
			$form->button('<i class="icon-shopping-cart icon-white"></i> '.t('Go to payment'),array('class'=>'btn btn-success right'));
		}
		else{
			$form->free('<span class="lightgrey italic padded right">'.t('Please login to proceed to payment.').'</span>');
		}
		$form->fieldset();
		
		$r .= dv('d640 col').dv('inner-content').dv('select-billing');
		if(!isset($_SESSION['buy']['renew'])){$r .= dv('alert alert-success alert-block').t('{$1} is available!',$siteurl).xdv();}
		$r .= $form->content();
		$r .= xdv().xdv().xdv();
		
		$r .= dv('d320 col').dv('order-summary');
		$r .= '<h3>'.t('Summary').'</h3>';
		//$r .= dv('padded').dv('progress').dv('bar','','style="width:50%"').xdv().xdv().xdv();
		
		$bill_html = '';
		$id = 'bill_'.randStr();
		$bill_html .= '<ul class="bill" id="'.$id.'">';
		foreach($bill as $line){
			$bill_html .= '<li class="'.$line['type'].'" id="'.$line['id'].'">'.$line['label'].'<strong class="right" id="'.$line['id'].'">'.price2str($line['amt']).'</strong></li>';
		}
		$bill_html .= '</ul>';
		if(IS_AJAX){
			return $bill_html;
		}
		else{
			$r .= $bill_html;
			T::$js[] = '$(".duration input").change(function(){$("#'.$id.'").load(HOME+"website/validate?ajax=1&duration="+$(".duration input:checked").val());});';
		}
		$r .= xdv();
		$r .= xdv();
		$r .= xdv();
		$r .= xdv();
		return $r;
	}
	
	public static function finalize(){
		if(isset($_REQUEST['plan'])){
			$sale = new Sale();
			$sale->validateData('plan',$_REQUEST['plan']);
			if(!$sale->insert()){
				header('location: http://pixyt.com/validate?error=plan-insert-failed&utm_medium='.urlencode(HOST).'&utm_campaign=website_finalize');
				exit();
			}
		}
		if(isset($_REQUEST['renew'])){
			$site = new Site($_REQUEST['renew']);
			$sale = new Sale();
			$sale->isfreedomain = isset($_REQUEST['plan']);
			$sale->validateData('renew',$site->id);
			if(!$sale->insert()){
				header('location: http://pixyt.com/validate?error=renew-insert-failed&utm_medium='.urlencode(HOST).'&utm_campaign=website_finalize');
				exit();
			}
		}
		if(isset($_REQUEST['domain'])){
			$sale = new Sale();
			$sale->isfreedomain = isset($_REQUEST['plan']);
			$sale->validateData('domain',$_REQUEST['domain']);
			if(!$sale->insert()){
				header('location: http://pixyt.com/validate?error=domain-insert-failed&utm_medium='.urlencode(HOST).'&utm_campaign=website_finalize');
				exit();
			}
		}
		header('location: '.HOME.'checkout/finalize');
		exit();
	}
	
	public static function designed(){
		$r = '';
		$r .= dv('d1000 center');
		$r .= dv('hero-unit').'<h1>'.t('Get a beautifully designed website today.').'</h1>'.xdv();
		$r .= dv('d3x2 col');
		$r .= dv('padded').'<h2>'.('Get our special offer').'</h2><p>Order today for 99$, limited offer only!<span class="discount">save 50%</span></p>';
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
		return $r;
	}
}
?>