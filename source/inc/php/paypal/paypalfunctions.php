<?php
class Paypal extends Pixyt{
	private $useProxy=false;
	public $PROXY_HOST = '127.0.0.1';
	public $PROXY_PORT = '808';

	public $SandboxFlag = false;
	public $sBNCode = "PP-ECWizard";

	public $API_UserName="mail_api1.lightarchitect.com";
	public $API_Password="3UWS4LNFYMXB5HFC";
	public $API_Signature="Aw.82J1zF2JNIqwD9F7ljKSepIbIAsXOi8vNe2pWL0EIf4TIBafTYk5Z";

	public $API_Endpoint = "https://api-3t.paypal.com/nvp";
	public $PAYPAL_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=";

	public function __construct($sandbox=false){
		if($sandbox==true){
			Msg::notify('You are using the sandbox payment API');
			$this->API_Endpoint='https://api-3t.sandbox.paypal.com/nvp';
			$this->API_UserName='crew_1355430585_biz_api1.pixyt.com';
			$this->API_Password='1355430607';
			$this->API_Signature='AEJLJq7YlUdBD7KI3Qfzxj1U5e25A2d-vTuH8ai6hlaOBGktN6oRi.6S';
			$this->PAYPAL_URL = "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=";
		}
	}
	
	private function formatAmt($amt){
		return number_format($amt/100,2);
	}

	public function CallShortcutExpressCheckout($amt,$currency,$type,$success,$cancel){
		$nvpstr='&Amt='.$this->formatAmt($amt).'&PAYMENTACTION='.$type.'&ReturnUrl='.$success.'&CANCELURL='.$cancel.'&CURRENCYCODE='.$currency;
		
		$_SESSION['pp']['currency'] = $currency;	  
		$_SESSION['pp']['type'] = $type;
 
	    $response=$this->hash_call('SetExpressCheckout', $nvpstr);
		$ack = strtoupper($response['ACK']);
		if($ack=='SUCCESS' || $ack=='SUCCESSWITHWARNING'){
			$token = urldecode($response['TOKEN']);
			$_SESSION['pp']['token']=$token;
		}
	    return $response;
	}
	
	function CallMarkExpressCheckout($amt,$currency,$type,$success,$cancel,$name,$street,$city,$state,$countrycode,$zip,$street2,$phone,$additional=''){
		if($amt < 50 || $amt > 1000000){//in cents
			$this->error('Invalid amount ['.$amt.']');
			return array('L_LONGMESSAGE0'=>'Checkout accepts only amounts between 0.5 and 10 000€','ACK'=>'FAILURE');
		}
		$nvpstr='&Amt='.$this->formatAmt($amt).'&PAYMENTACTION='.$type.'&ReturnUrl='.$success.'&CANCELURL='.$cancel.'&CURRENCYCODE='.$currency.'&ADDROVERRIDE=1';
		$nvpstr .= '&SHIPTONAME='.$name.'&SHIPTOSTREET='.$street.'&SHIPTOSTREET2='.$street2.'&SHIPTOCITY='.$city.'&SHIPTOSTATE='.$state.'&SHIPTOCOUNTRYCODE='.$countrycode.'&SHIPTOZIP='.$zip.'&PHONENUM='.$phone.$additional;
		
		$_SESSION['pp']['currency'] = $currency;	  
		$_SESSION['pp']['type'] = $type;

	    $response=$this->hash_call('SetExpressCheckout', $nvpstr);
		$ack = strtoupper($response['ACK']);
		if($ack=='SUCCESS' || $ack=='SUCCESSWITHWARNING'){
			$token = urldecode($response['TOKEN']);
			$_SESSION['pp']['token']=$token;
		}
		else{
			$response['query']=$nvpstr;
		}
	    return $response;
	}
	
	function SetRecuringPayment($amt,$currency,$type,$success,$cancel,$name,$street,$city,$state,$countrycode,$zip,$street2,$phone){
		$recuringSettings = '&BILLINGTYPE=MerchantInitiatedBilling';
		$this->CallMarkExpressCheckout($amt,$currency,$type,$success,$cancel,$name,$street,$city,$state,$countrycode,$zip,$street2,$phone);
	}
	
	function GetShippingDetails($token){
	    $nvpstr='&TOKEN='.$token;
	    $response=$this->hash_call('GetExpressCheckoutDetails', $nvpstr);
		$ack = strtoupper($response['ACK']);
		if($ack=='SUCCESS' || $ack=='SUCCESSWITHWARNING'){
			$token = urldecode($response['TOKEN']);
			$_SESSION['pp']['payer_id']=$response['PAYERID'];
		}
		else{
			$response['query']=$nvpstr;
		}
	    return $response;
	}
	
	function ConfirmPayment($amt,$token){
		$token 		= urlencode($token);
		$type 		= urlencode('sale');
		$currency 	= urlencode($_SESSION['pp']['currency']);
		$payerID 	= urlencode($_SESSION['pp']['payer_id']);

		$serverName = urlencode($_SERVER['SERVER_NAME']);

		$nvpstr  = '&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTACTION='.$type.'&PAYMENTACTIONSPECIFIED=true&AMT='.$this->formatAmt($amt).'&CURRENCYCODE='.$currency.'&IPADDRESS='.$serverName;
		$response=$this->hash_call('DoExpressCheckoutPayment',$nvpstr);
		$ack = strtoupper($response['ACK']);
		if($ack == 'SUCCESS'){
			$_SESSION['pp'][$token]['status'] = 'confirmed';
		}
		else{
			$response['query']=$nvpstr;
		}
		return $response;
	}
	
	function DirectPayment( $paymentType, $paymentAmount, $creditCardType, $creditCardNumber,
							$expDate, $cvv2, $firstName, $lastName, $street, $city, $state, $zip, 
							$countryCode, $currencyCode )
	{
		$this->error('Direct payment not yet supported.');
		
		//Construct the parameter string that describes DoDirectPayment
		$nvpstr = "&AMT=" . $paymentAmount;
		$nvpstr = $nvpstr . "&CURRENCYCODE=" . $currencyCode;
		$nvpstr = $nvpstr . "&PAYMENTACTION=" . $paymentType;
		$nvpstr = $nvpstr . "&CREDITCARDTYPE=" . $creditCardType;
		$nvpstr = $nvpstr . "&ACCT=" . $creditCardNumber;
		$nvpstr = $nvpstr . "&EXPDATE=" . $expDate;
		$nvpstr = $nvpstr . "&CVV2=" . $cvv2;
		$nvpstr = $nvpstr . "&FIRSTNAME=" . $firstName;
		$nvpstr = $nvpstr . "&LASTNAME=" . $lastName;
		$nvpstr = $nvpstr . "&STREET=" . $street;
		$nvpstr = $nvpstr . "&CITY=" . $city;
		$nvpstr = $nvpstr . "&STATE=" . $state;
		$nvpstr = $nvpstr . "&COUNTRYCODE=" . $countryCode;
		$nvpstr = $nvpstr . "&IPADDRESS=" . $_SERVER['REMOTE_ADDR'];

		$resArray=hash_call("DoDirectPayment", $nvpstr);

		return $resArray;
	}

	private function hash_call($methodName,$nvpStr){
		$version = "57.0";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->API_Endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		
		if($this->useProxy){
			curl_setopt ($ch, CURLOPT_PROXY, $this->PROXY_HOST.':'. $this->PROXY_PORT); 
		}
		
		$nvpreq='METHOD='.urlencode($methodName).'&VERSION='.urlencode($version).'&PWD='.urlencode($this->API_Password).'&USER='.urlencode($this->API_UserName).'&SIGNATURE='. urlencode($this->API_Signature).$nvpStr.'&BUTTONSOURCE='.urlencode($this->sBNCode);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

		$response = curl_exec($ch);

		$nvpResArray=$this->deformatNVP($response);
		$nvpReqArray=$this->deformatNVP($nvpreq);
		$_SESSION['pp']['nvpReqArray']=$nvpReqArray;
		if(curl_errno($ch)){
			  $this->error(curl_errno($ch).':'.curl_error($ch));
		} 
		else{
		  	curl_close($ch);
		}

		return $nvpResArray;
	}

	function RedirectToPayPal ($token){
		$payPalURL = $this->PAYPAL_URL.$token;
		echo '<script type="text/javascript">document.location = "'.$payPalURL.'";</script>';
	}
	
	private function deformatNVP($nvpstr){
		$intial=0;
	 	$nvpArray = array();

		while(strlen($nvpstr)){
			$keypos= strpos($nvpstr,'=');
			$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);

			$keyval=substr($nvpstr,$intial,$keypos);
			$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
			$nvpArray[urldecode($keyval)] =urldecode( $valval);
			$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
	    }
		return $nvpArray;
	}
}
?>