<?php
class Sites extends Interfaces{
	public function directory(){
		return $this->all();
	}
	
	public function all(){
		$sites = new Collection('Site');
		if(isset($_REQUEST['uid'])){
			if($_REQUEST['uid'] == 'me' && $_SESSION['uid'] != 0){$_REQUEST['uid'] = $_SESSION['uid'];}
			$sites->where('uid',$_REQUEST['uid']);
		}
		
		$sites->load();
		return $sites->data();
	}
	
	public function tlds(){
		return Product::$tlds;
	}
	
	public function domain(){
		if(isset($_REQUEST['domain'])){
			$parts = explode('.',strtolower($_REQUEST['domain']));
			$domain = strtolower($parts[0]);
			unset($parts[0]);
			$tld = implode('.',$parts);
			
			$siteurl = $domain.'.'.$tld;
			
			//the site is already regitered on Pixyt, lets check if the current user owns it..
			if(Object::exists('Site',$siteurl,'url',$id)){
				$site = new Site($id);
				if($site->uid == App::$user->id){
					return array('renew'=>$site);
				}
				else {
					return array('error'=>'Domain not available...');
				}
			}
			else{
				if(strlen($domain) > 253 || strlen($domain) <= 3){
					return array('error'=>'A domain can only contain between 3 and 253 characters.');
				}
				elseif(in_array($_REQUEST['domain'],Site::disalowUrls())){
					return array('error'=>'Sorry, that URL is forbidden.');
				}
				else{
					$options = array();
					$tlds = Product::tldPrices();
					if(!empty($tld) && isset($tld,$tlds)){
						$price = Product::tldPrices($tld);
						$options[] = array('domain'=>$siteurl,'price'=>$price);
					}
					$i=0;
					foreach($tlds as $extension=>$price){
						if($i>5){break;}
						$options[] = array('domain'=>$domain.'.'.$extension,'price'=>$price);
						$i++;
					}
					if(HOST == 'localhost'){
						foreach($options as $k=>$option){$options[$k]['available'] = (bool)rand(0,1);}
						return array('options'=>$options);
					}
					$soap = new SoapClient('https://www.ovh.com/soapi/soapi-re-1.49.wsdl');
					$session = $soap->login(OVH_NIC,OVH_PWD,'fr',false);
					foreach($options as $k=>$option){
						$result = (array)$soap->domainCheck($session,$option['domain']);
						$options[$k]['available'] = $result[0]->value;
					}
					return array('options'=>$options);
				}
			}
		}
		else{
			return array('error'=>'No domain sent.');
		}
	}
	
	public function isavailable(){
		if(!isset(App::$input['domain'])){return array('error'=>'No domain submitted!');}
		if(HOST == 'localhost'){
			return array('available'=>(bool)rand(0,1));
		}
		$soap = new SoapClient('https://www.ovh.com/soapi/soapi-re-1.49.wsdl');
		$session = $soap->login(OVH_NIC,OVH_PWD,'fr',false);
		$result = (array)$soap->domainCheck($session,App::$input['domain']);
		return array('available'=>$result[0]->value);
	}
	
	public function trial(){
		$site = new Site();
		$site->url = strtolower(App::$user->firstname.App::$user->lastname.'.pixyt.com');
		while(Object::exists('Site',$site->url,'url')){
			$site->url = strtolower(App::$user->firstname.App::$user->lastname.'-'.randStr().'.pixyt.com');
		}
		$site->title = App::$user->firstname.' '.App::$user->lastname;
		$site->settings = array('m'=>'topmenu');
		if($site->insert()){
			return $site->data();
		}
		return false;
	}
}
?>