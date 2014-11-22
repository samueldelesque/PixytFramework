<?php
class Plesk extends LP{
	private $curl;
	private $host = '94.23.252.196';
	private $login = 'contact@samueldelesque.com';
	private $password = 'KoreWebmaster9120';
	
	function __construct(){
		$this->curlInit($this->host,$this->login,$this->password);
	}
	
	private function domainsInfoRequest(){
		$xmldoc = new DomDocument('1.0', 'UTF-8');
		$xmldoc->formatOutput = true;
		
		// <packet>
		$packet = $xmldoc->createElement('packet');
		$packet->setAttribute('version', '1.6.2.0');
		$xmldoc->appendChild($packet);
		
		// <packet/domain>
		$domain = $xmldoc->createElement('domain');
		$packet->appendChild($domain);
		
		// <packet/domain/get>
		$get = $xmldoc->createElement('get');
		$domain->appendChild($get);
		
		// <packet/domain/get/filter>
		$filter = $xmldoc->createElement('filter');
		$get->appendChild($filter);
		
		// <packet/domain/get/dataset>
		$dataset = $xmldoc->createElement('dataset');
		$get->appendChild($dataset);
		
		// dataset elements
		$dataset->appendChild($xmldoc->createElement('hosting'));
		$dataset->appendChild($xmldoc->createElement('gen_info'));
		
		return $xmldoc;
	}
	
	private function curlInit($host, $login, $password){
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_URL, 'https://'.$host.':8443/enterprise/control/agent.php');
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_POST,           true);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($this->curl, CURLOPT_HTTPHEADER,array('HTTP_AUTH_LOGIN: '.$login,'HTTP_AUTH_PASSWD: '.$password,'Content-Type: text/xml'));
	}
	
	private function sendRequest($packet){
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $packet);
		$result = curl_exec($this->curl);
		if (curl_errno($this->curl)) {
			$errmsg  = curl_error($this->curl);
			$errcode = curl_errno($this->curl);
			curl_close($this->curl);
			$this->error('Curl error '.$errcode.' "'.$errmsg.'"',90);
		}
		curl_close($this->curl);
		return $result;
	}
	
	private function parseResponse($response_string){
		$xml = new SimpleXMLElement($response_string);
		if (!is_a($xml, 'SimpleXMLElement')){$this->error('Cannot parse server response: ['.$response_string.']');}
		return $xml;
	}
	
	private function checkResponse(SimpleXMLElement $response){
		$resultNode = $response->domain->get->result;
		if ('error' == (string)$resultNode->status){$this->error('The Panel API returned an error: '. (string)$resultNode->result->errtext);}
	}
	
	//QUERIES
	private $getsites ='
<?xml version="1.0" encoding="UTF-8" ?>
<packet version="1.6.3.4">
<site>
    <get>
       <filter/>
       <dataset>
            <hosting/>
       </dataset>
    </get>
</site>
</packet>';
	
	//DISPLAY
	
	public function addAlias($name){
		$r = '';
		$response = $this->sendRequest($this->curl, '<?xml version="1.0" encoding="UTF-8" ?>
<packet version="1.6.3.4">
<site-alias>
<create>
   <site-id>5</site-id>
   <name>'.$name.'</name>
</create>
</site-alias>
</packet>
');
		$responseXml = $this->parseResponse($response);
		return print_r($responseXml->xpath('/packet'),true);
		return $r;
	}
	
	public function showSites(){
		$r = '';
		$response = $this->sendRequest($this->curl, $this->getsites);
		$responseXml = $this->parseResponse($response);
		return print_r($responseXml->xpath('/packet'),true);
		return $r;
	}
}
?>