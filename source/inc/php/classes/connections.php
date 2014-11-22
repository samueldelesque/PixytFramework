<?php
/*
FACEBOOK

Application ID
    199724263406664
API Key
    90ac143bbbab6a7e27e3a912ff2c1705
App secret
    630e1d9b6d564fff94980695de7b99ca
Contact Email
    contact@lightarchitect.com
*/
require_once(ROOT.'inc/php/connections/facebook/facebook.php');
require_once(ROOT.'inc/php/connections/tumblr/tumblr.php');

class Connection{
	private $facebook;
	private $tumblr;
	private $user=array();
	
	public function connect($platform,$user='',$password='',$api='',$oath=''){
		switch($platform){
			case 'facebook':
			case 'fb':
				$this->facebook = new Facebook('90ac143bbbab6a7e27e3a912ff2c1705','630e1d9b6d564fff94980695de7b99ca');
				$this->facebook->require_frame();
				$this->user['facebook'] = $this->facebook->require_login();
			break;
			
			case 'tumblr':
				$this->tumblr = new Tumblr($user,$password);
			break;
		}
	}
	
	public static function linkFacebookAccountBtn(){
		return xtlnk('www.facebook.com/login.php?api_key=90ac143bbbab6a7e27e3a912ff2c1705&connect_display=popup&v=1.0&fbconnect=true&return_session=true&req_perms=read_stream,publish_stream,offline_access',translate('Link my facebook account'));
	}
}

?>