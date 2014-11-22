<?php
class Checkout extends Interfaces{
	public $store = false;
	
	public function bag(){
		$r = '';
		//Kind of the new cart
		$bag = new Collection('Sale');
		$bag->where('uid',$_SESSION['uid']);
		$bag->where('status',2);
		
		$cancelled = new Collection('Order');
		$cancelled->where('uid',$_SESSION['uid']);
		$cancelled->where('status',2);
		
		$pending = new Collection('Order');
		$pending->where('uid',$_SESSION['uid']);
		$pending->where('status',2);
		
		if($bag->count() == 0 && $cancelled->count() == 0 && $pending->count() == 0){
			return '<h2>'.translate('Your shopping bag is empty.').'</h2>'.dv('grey').translate('If you believe this is a mistake, please make sure your browser accept cookies and executes javasript.').xdv();
		}
		$r .= dv('d3x2 col');
		if($bag->count() > 0){
			$r .= dv('padder').$bag->returnTable(0,999).dv('links padded').lnk('Continue','checkout/finalize',array('method'=>'paypal'),array('class'=>'btn')).xdv().xdv();
		}
		if($pending->total(true) > 0){
			$r .= dv('padder').'<h2>'.translate('Pending orders').'</h2>'.$pending->returnTable(0,999).xdv();
		}
		if($cancelled->total(true) > 0){
			$r .= dv('padder').'<h2>'.translate('Cancelled orders').'</h2>'.$cancelled->returnTable(0,999).xdv();
		}
		$r .= xdv();
		
		return $r;
	}
	
	public function failed(){
		$r = '';
		$r .= dv('d960 center');
		$r .= dv('alert alert-error alert-block').'<h2>'.t('An error occured').'</h2><p>'.t('An error occured. Please contact us at crew@pixyt.com').'</p>'.xdv();
		$r .= xdv();
		return $r;
	}
	
	public function finalize(){
		$bag = new Collection('Sale');
		$bag->status('=',0,true);
		$bag->uid('=',App::$user->id,true);
		$amt = $bag->sum('salesPrice');
		if($bag->total(true)==0){
			Msg::notify(translate('Transaction could not be completed as empty'));
			return $this->bag();
		}
		$bag->load(0,999);
		$content = array();
		foreach($bag->results as $item){
			$owner = new User($item->proprietor);
			//We copy the Data in case the product owner decides to delete it
			$content[] = array($item->id,$item->title,$item->quantity,$item->salesPrice,$item->settings,$owner->fullName());
			//mark the item as validated so it no longer appears in shopping bag
			$item->status = 1;
			$item->update();
		}
		$order = new Order('NEW');
		$order->content = $content;
		$order->amt = $amt;
		$order->email = App::$user->email;
		$order->name = App::$user->fullName('full');
		if(isset($_REQUEST['method'])){
			//for express checkout
			$order->paymentMethod = $_REQUEST['method'];
		}
		if($order->insert()){
			//clear temp cart assistant (if something goes wrong, user will have to start from beginning)
			unset($_SESSION['buy']);
			
			if($order->status == 3){header('Location: '.HOME.'order/'.$order->id.'/success');}
			elseif($order->status == 2){header('Location: '.HOME.'order/'.$order->id.'/cancel');}
			//show the archived version of the order
			elseif($order->status > 2){header('Location: '.HOME.'order/'.$order->id.'');}
			//order status == 0 - we go to payment
			else{return $this->payment($order->id);}
		}
		else{
			$this->error('Failed to insert order!');
			$this->failed();
		}
	}
	
	public function payment($oid=''){
		if(isset($_REQUEST['oid'])){$oid = $_REQUEST['oid'];}
		if(!Object::objectExists('Order',$oid)){
			$this->error('Failed to find matching Order',90);
			return false;
		}
		$r = '';
		$order = new Order($oid);
		if($order->status > 1){
			$this->error('This order has already been paid.',90);
			return false;
		}
		switch($order->paymentMethod){
			case 'paypal':
			default:
				$paypal = new Paypal();
				if($order->recurring){
					$method = 'SetRecuringPayment';
				}
				else{
					$method = 'CallMarkExpressCheckout';
				}
				$ppresponse = $paypal->$method(
					$order->amt, 'EUR',
					'Sale',
					HOME.'order/'.$order->id.'/success',
					HOME.'order/'.$order->id.'/cancel',
					$order->data['name'], 
					$order->data['shippingAddress']['street'], 
					$order->data['shippingAddress']['city'], 
					'',
					$order->data['shippingAddress']['country_code'],
					$order->data['shippingAddress']['zipcode'], 
					'', 
					$order->data['phone']
				);
				
				$order->data['paymentStatusContent']['pre-payment'] = $ppresponse;
				if(!$order->update()){
					$this->error('Could not update the order.');
					return false;
				}
				
				$ack = strtoupper($ppresponse['ACK']);
				if($ack=='SUCCESS'||$ack=='SUCCESSWITHWARNING'){
					$r .= '<h2>'.translate('We are now redirecting you to Paypal...').'</h2>';
					$paypal->RedirectToPayPal($ppresponse['TOKEN']);
				}
				else{
					$r .= '<h2 class="red">'.translate('There was an error processing the payment.').'</h2>'.dv('grey').$ppresponse['L_LONGMESSAGE0'].xdv();
				}
			break;
		}
		return $r;
	}
	
	public function express(){
		$q = 'SELECT COUNT(*) FROM `Sale` WHERE `uid` = "'.App::$user->id.'" AND `status` = 0';
		$cart = '0';
		if(self::$db->customQuery($q,$r)){$cart = $r[0]['COUNT(*)'];}
		if($cart != 1){//This method is reserved to single item such as domain.
			return $this->bag();
		}
		//dont review order and go straight to payment
		return $this->finalize();
	}
	
	public function postpayment(){
		if(isset($_REQUEST['oid']) && isset($_SESSION['orderId']) && isset($_REQUEST['token'])){
			$paypal = new Paypal();
			if($_REQUEST['oid'] != $_SESSION['orderId']){
				T::$body[] = '<h2>'.translate('Error: incorrect order id').'</h2>';
				return false;
			}
			
			$order = new Order($_SESSION['orderId']);
			if($order->isDummy){
				T::$body[] = '<h2>'.translate('Error: order does not exist').'</h2>';
				return false;
			}
			
			$resArray = $paypal->GetShippingDetails($_REQUEST['token']);
			$ack = strtoupper($resArray["ACK"]);
			
			$order->data['paymentStatusContent']['checkout_details'] = $resArray;
			
			if($ack != "SUCCESS"){
				checkouterror(translate('cannot get paypal shipping details'));
				return false;
			}
			
			$amt = number_format($order->data['amt']/100,2);
			$resArray = $paypal->ConfirmPayment($amt);
			$ack = strtoupper($resArray["ACK"]);
			$order->data['paymentStatusContent']['post-payment'] = $resArray;
			$order->data['paymentStatusContent']['post-payment']['PayerID'] = $_REQUEST['PayerID'];
			if($ack == "SUCCESS"){
				$order->data['status'] = 1;
			}else{
				$order->data['status'] = 0;
			}
			
			if(!$order->update()){
				checkouterror(translate('cannot update order'));
				return false;
			}
			
			if($ack != "SUCCESS"){
				T::$body[] = '<h2>'.translate('cannot get paypal shipping details').'</h2>';
				return false;
			}else{
				//ADD Transaction
				$t = new Transaction();
				$t->name = 'Order '.$order->id.' - '.$order->name;
				$t->holder = 28;
				$t->type = 1;
				$t->account = 701;
				$t->paymentMethod = 2;
				$t->amt = $order->amt;
				$t->vat = -1*round(VATRATE*($order->amt));
				$t->status = 1;
				$t->date = date('Y-m-d');
				if(!$t->insert()){
					T::$body[] = '<h2>'.translate('Failed to insert transaction details. Please contact admin.').'</h2>';
					return false;
				}
				
				T::$page['title'] = 'Payment validated';
				T::$body[] = dv('splitLeft').dv('table');
				T::$body[] = dv('line').'<h3>'.translate('Order ID: ').$order->id.'</h3>'.xdv();
				T::$body[] = dv('line').translate('Order total:').' '.($order->amt/100).'€'.xdv();
				T::$body[] = dv('line').$order->name.xdv();
				T::$body[] = dv('line').$order->phone.xdv();
				T::$body[] = dv('line').$order->email.xdv();
				T::$body[] = dv('line').$order->shippingAddress['street'].xdv();
				T::$body[] = dv('line').$order->shippingAddress['zipcode'].xdv();
				T::$body[] = dv('line').$order->shippingAddress['city'].xdv();
				T::$body[] = dv('line').$order->shippingAddress['country'].xdv();
				T::$body[] = xdv().xdv();
				
				T::$body[] = dv('splitRight');
				T::$body[] = '<h3>'.translate('Thank you for your order, you will be delivered shortly (up to 10 days).').'</h3>';
				T::$body[] = xdv().dv('clearfloat').xdv();
			}
		}
		else{
			T::$page['title'] = 'Error: incorrect paypal parameters received';
		}
	}

}
?>