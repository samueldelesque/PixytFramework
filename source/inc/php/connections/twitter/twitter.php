<?php
//include_once ROOT.'inc/php/plugins/oauth/library/OAuthStore.php';
//include_once ROOT.'inc/php/plugins/oauth/library/OAuthRequester.php';
class Twitter extends LP{
	private $host = 'api.twitter.com';
	private $consumerKey = 'YsOetaalQjsJbXzfn7Rh2A';
	private $consumerSecret = 'tGRs2ldgr2fnzbJhez7sppncB3orogcg0ElS8SlXE';
	private $params = array();
	private $store;		//MYSQL STORAGE
	private $oauthSignatureMethod = 'HMAC-SHA1';
	private $oauthVersion = '1.0';
	private $sigBase;
	private $sigKey;
	private $oauthSig;
	private $requestHeader;
	
	public $uid = 1;
	public $token;
	public $tokenSecret;
	
	public $oauthTimestamp;
	public $nonce;
	
	function accessTokenUrl(){return 'https://api.twitter.com/oauth/access_token';}
	function authorizeUrl(){return 'https://api.twitter.com/oauth/authorize';}
	function requestTokenUrl(){return 'https://api.twitter.com/oauth/request_token';}
	function callBackUrl(){return 'https://pixyt.com/connect/twitter';}//?consumer_key='.rawurlencode($this->consumerKey).'&usr_id='.intval($this->uid)
	
	function tempDir(){return ROOT.'local/tmp/';}


	function __construct(){
		$this->oauthTimestamp=time();
		$this->nonce=md5(mt_rand());
		$this->sigBase = 'POST&'.rawurlencode($this->requestTokenUrl()).'&';
		/*
			rawurlencode(
				'oauth_consumer_key='.rawurlencode($this->consumerKey).
				'&oauth_signature_method='.rawurlencode($this->oauthSignatureMethod).
				'&oauth_timestamp='.$this->oauthTimestamp.
				'&oauth_nonce='.rawurlencode($this->nonce).
				'&oauth_callback='.rawurlencode($this->callBackUrl()).
				'&oauth_version='.rawurlencode($this->oauthVersion)
			);
			*/
		$this->sigKey = $this->consumerSecret.'&';
		$this->oauthSig = base64_encode(hash_hmac('sha1',$this->sigBase,$this->sigKey,true));
		//$this->requestHeader = '';
		//die($this->oauthSig);
	}
	
	
	public function request_token_curl(){
		$crl = curl_init();
		$params = array();
		$params['oauth_nonce']=$this->nonce;
		$params['oauth_callback']=$this->callBackUrl();
		$params['oauth_signature_method']=$this->oauthSignatureMethod;
		$params['oauth_consumer_key']=$this->consumerKey;
		
		$encoded_params = array();
		foreach($params as $n=>$v){
			$encoded_params[rawurlencode($n)]=rawurlencode($v);
		}
		ksort($encoded_params);
		
		$request = '';
		$i=0;
		foreach($encoded_params as $n=>$v){
			if($i!=0){$request .= '&';}
			$request .= $n.'='.$v;
			$i++;
		}
		$sigPost = rawurlencode($request);
		$signature = base64_encode(hash_hmac('sha1',$this->sigBase.$sigPost,$this->sigKey,true));
		
		
		$auth = 'oauth_nonce="'.$this->nonce.'",'.
				'oauth_callback="'.$this->callBackUrl().'",'.
				'oauth_signature_method="'.$this->oauthSignatureMethod.'",'.
				'oauth_consumer_key="'.$this->consumerKey.'",'.
				'oauth_signature="'.rawurlencode($signature).'"';
				
		$header = array();
		$header[] = 'Content-length: 0';
		$header[] = 'Content-type: application/json';
		$header[] = 'Authorization: OAuth '.$auth;
		
		curl_setopt($crl, CURLOPT_URL, $this->requestTokenUrl());
		curl_setopt($crl, CURLOPT_HTTPHEADER,$header);
		curl_setopt($crl, CURLOPT_POST,true);
		curl_setopt($crl, CURLOPT_USERAGENT, 'Pixyt/'.SITEVERSION);
		curl_setopt($crl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, false);
		$rest = curl_exec($crl);
		curl_close($crl);
		
		print_r($rest);
	}
	
	public function request_token(){
		$auth = 'OAuth oauth_nonce="'.$this->nonce.'",'.
				'oauth_callback="'.$this->callBackUrl().'",'.
				'oauth_signature_method="'.$this->oauthSignatureMethod.'",'.
				'oauth_consumer_key="'.$this->consumerKey.'",'.
				'oauth_signature="'.$this->oauthSig.'"';	//,!
//						'oauth_timestamp="'.$this->oauthTimestamp.'",'.
//						'oauth_version="'.$this->oauthVersion.'"';
	//	header('Authorization: '.$auth);
		//ini_set('user_agent', "Pixyt\r\nAuthorization: $auth");
		header('Location: '.$this->requestTokenUrl());
		exit();
//		header('Location: https://api.twitter.com/oauth/authorize?oauth_token=Z6eEdO8MOmk394WozF5oKyuAv855l4Mlqo7hhlSLik');
	}
	
	public function request($type='authorize',$body=''){
		switch($type){
			case 'authorize':
				$url = $this->authorizeUrl();
				$auth = 'OAuth oauth_consumer_key="'.$this->consumerKey.'",'.
						'oauth_nonce="'.$this->nonce.'",'.
						'oauth_signature="'.$this->oauthSig.'",'.
						'oauth_signature_method="'.$this->oauthSignatureMethod.'",'.
						'oauth_timestamp="'.$this->oauthTimestamp.'",'.
						'oauth_token="'.$this->token.'",'.
						'oauth_version="'.$this->oauthVersion.'"';
			break;
			
			case 'request_token':
				$url = $this->requestTokenUrl();
				$auth = 'OAuth oauth_nonce="'.$this->nonce.'",'.
						'oauth_callback="'.$this->callBackUrl().'",'.
						'oauth_signature_method="'.$this->oauthSignatureMethod.'",'.
						'oauth_consumer_key="'.$this->consumerKey.'",'.
						'oauth_signature="'.$this->oauthSig.'"';	//,!
//						'oauth_timestamp="'.$this->oauthTimestamp.'",'.
//						'oauth_version="'.$this->oauthVersion.'"';
			break;
			
			default:
				$this->error('Request type not supported:'.$type,95);
			break;
		}
		$opt = array();
		$opt['timeout']=3;
		$opt['connecttimeout']=3;
		$opt['dns_cache_timeout']=3;
		$opt['url']=$url;
		$opt['referer']='Pixyt';
		$opt['cookies']=array();
		$opt['useragent']='Pixyt';
		
		$opt['headers']=array(
			'Content-Type'=>'application/x-www-form-urlencoded',
			'Authorization'=>$auth,
			'Content-Length'=>strlen($body),
			'Host'=>$this->host
		);
		$opt['compress']=false;
		
		return http_request(HTTP_METH_GET,$url,$body,$opt);
	}
	/*
	public function SendRequest($url,$method='GET',$data='',$headers=array('Content-type: application/x-www-form-urlencoded')){
		$context = stream_context_create(array(
			'http' => array(
				'method' => $method,
				'header' => $headers,
				'content' => http_build_query($data)
			)
		));
	 
		return file_get_contents($url, false, $context);
	}
	*/
	public function requestCredentials(){
		$requestUrl = $this->requestTokenUrl().'?'.
			'oauth_consumer_key='.rawurlencode($this->consumerKey).
			'&oauth_signature_method='.rawurlencode($this->oauthSignatureMethod).
			'&oauth_timestamp='.$this->oauthTimestamp.
			'&oauth_nonce='.rawurlencode($this->nonce).
			'&oauth_callback='.rawurlencode($this->callBackUrl()).
			'&oauth_version='.rawurlencode($this->oauthVersion).
			'&oauth_signature='.$this->oauthSig;
			
		$response = file_get_contents($requestUrl);
		die($response);
	}
	
/*
	function __construct(){
		//OAuthStore::instance("2Leg", $this->params);
		
		$options = array('server' =>SQL_SERVER,'username'=>SQL_USER,'password'=>SQL_PASSWORD,'database'=>SQL_DB);
		$this->store = OAuthStore::instance('MySQL', $options);
		
		$this->params['consumer_key'] = $this->key;
		$this->params['consumer_secret'] = $this->secret;
		$this->params['server_uri'] = 'https://api.twitter.com/';
		$this->params['signature_methods'] = array('HMAC-SHA1','PLAINTEXT');
		$this->params['request_token_uri'] = $this->requestTokenUrl();
		$this->params['authorize_uri'] = $this->authorizeUrl();
		$this->params['access_token_uri'] = $this->accessTokenUrl();
		
		$this->consumerKey = $this->store->getServer($this->key, $this->uid);
	}
	
	//UTILITY FUNCTIONS
	
	public function connect(){
		//REDIRECT TO SERVICE PROVIDER
		$this->token = OAuthRequester::requestRequestToken($this->consumerKey, $this->uid);
		if (!empty($this->token['authorize_uri'])){
			// Redirect to the server, add a callback to our server
			if (strpos($this->token['authorize_uri'], '?')){
				$uri = $this->token['authorize_uri'] . '&'; 
			}
			else{
				$uri = $this->token['authorize_uri'] . '?'; 
			}
			$uri .= 'oauth_token='.rawurlencode($this->token['token']).'&oauth_callback='.rawurlencode($this->callBackUrl());
		}
		else{
		   $uri = $callback_uri . '&oauth_token='.rawurlencode($this->token['token']);
		}
		header('Location: '.$uri);
		exit();
	}
	
	public function getAccessToken(){
		$this->consumerKey = $_REQUEST['consumer_key'];
		$this->token = $_REQUEST['oauth_token'];
		$this->uid = $_REQUEST['usr_id'];
		try{
			OAuthRequester::requestAccessToken($this->consumerKey,$this->token,$this->uid);
		}
		catch (OAuthException $e){
			$this->error('Failed to get access token!');
			return false;
		}
	}
	
	public function signedRequest(){
		$u = 'https://api.twitter.com/';
		$p = array('method'=>'ping');
		$req = new OAuthRequester($u, 'GET', $p);
		return $req->doRequest($this->uid);
	}
	
	
	//PUBLIC FUNCTIONS
	
	public function publicTimeline(){
		$u = 'https://twitter.com/statuses/update.json';
		$request = new OAuthRequester($u, 'GET',$this->params);
		return $request->doRequest();
	}
	*/
}
?>