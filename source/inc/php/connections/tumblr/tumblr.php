<?php
require_once(ROOT.'inc/php/connections/tumblr/tumblroauth/tumblroauth.php');
class Tumblr extends LP{
	private $email;
	private $password;
	private $blog;
	
	private $host = 'http://api.tumblr.com/v2/';
	private $apiKey = '3ZA58VQqGK21YW9JnJrmXcJL0xIfQN4airCepo6gMWlFupPpC2';
	private $secretKey = 'G0a84I6QCgGDzt4d8iTrVtm9yvMkyck7UdG0OdhRFP1xBV9VcY';
	private $token = NULL;
	private $ready = false;
	
	public $handler;
	
	function accessTokenURL(){return 'http://www.tumblr.com/oauth/access_token';}
	function authenticateURL(){ return 'http://www.tumblr.com/oauth/authorize';}
	function authorizeURL(){return 'http://www.tumblr.com/oauth/authorize';}
	function requestTokenURL(){return 'http://www.tumblr.com/oauth/request_token';}
	function callBackUrl(){return 'https://pixyt.com/connect/tumblr';}
	
	function __construct($e,$p,$b){
		$this->email = $e;
		$this->password = $p;
		$this->blog = $b;
		$authentificate = new TumblrOAuth($this->apiKey, $this->secretKey);
		$this->token = $authentificate->getXAuthToken($this->email, $this->password);
		if($authentificate->http_code==200){$this->ready=true;}
		if(isset($this->token['oauth_token']) && isset($this->token['oauth_token_secret'])){
			$this->handler = new TumblrOAuth($this->apiKey,$this->secretKey,$this->token['oauth_token'],$this->token['oauth_token_secret']);
		}
	}
	
	// API FUNCTIONS
	public function getUserInfo(){
		return $this->handler->get($host.'user/info');
	}
	
	public function postPhoto($id){
		$photo = new Photo($id);
		$r = http_build_query(
			array(
				'email'     => $this->email,
				'password'  => $this->password,
				'type'      => 'photo',
				'title'     => $photo->title,
				'body'      => $photo->description.' - '.User::$users[$photo->uid]->fullName('link'),
				'date'      => $photo->created,
				'tags'      => implode(',',$photo->tags),
				'caption'	=> $photo->title.' © '.User::$users[$photo->uid]->fullName(),
				'url'		=> 'http://pixyt.com/photo/'.$photo->id,
				'source'	=> 'http://pixyt.com/local/photos/600Wide/'.$photo->id.'.jpg',
			)
		);
		return $this->write($r,$result);
	}
	
	public function getFollowers(){
		$c = curl_init($host.'blog/'.$this->blog.'/followers?api_key='.$this->apiKey);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, $r);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($c);
		$status = curl_getinfo($c, CURLINFO_HTTP_CODE);
		curl_close($c);
		
		if($status == 201){
			return true;
		}
		elseif($status == 403){
			$result = translate('Wrong email or password');
			return false;
		}
		else{
			return false;
		}
	}
	
	protected function write($r,&$result){
		$c = curl_init($host.'blog/lightarchitect/write');  
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, $r);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($c);
		$status = curl_getinfo($c, CURLINFO_HTTP_CODE);
		curl_close($c);
		// Check for success
		if($status == 201){
			return true;
		}
		elseif($status == 403){
			$result = translate('Wrong email or password');
			return false;
		}
		else{
			return false;
		}
	}
}
?>