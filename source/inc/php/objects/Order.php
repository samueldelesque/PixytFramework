<?php
class Order extends Object{
	public $uid;
	public $title;
	public $recurring=false;
	public $content=array();		//(shopitem,shopitem...)
	public $phone;
	public $email;
	public $shippingAddress=array();
	/*
		(
			'name'=>'',
			'street'=>'',
			'zipcode'=>'',
			'city'=>'',
			'country'=>'',
			'country_code'=>''
		)
	*/
	
	public $amt;				//total cart amount in cents
	public $paymentMethod;			//0:undifined, 1:check, 2:paypal, 3:cash, 4:bank transfer
	public $paymentStatusContent;		//(Paypal transaction data)
	public $status;
		
	//if set to true OVH API will be set to dry run
	public static $demo = false;
	
	public $tmpCart;
	
	protected static function publicFunctions(){
		return array(
			'fullView'=>true,
			'preview'=>true,
			'cancel'=>true,
			'success'=>true,
		);
	}
	
	protected function ownerFunctions(){
		return array(
			'edit'=>true,
			'editPreview'=>true,
		);
	}
	
	public function descriptor(){
		return array (
			'id'=>'int',
			'uid'=>'int',
			'title'=>'string',
			'recurring'=>'bool',
			'content'=>'string',
			'name'=>'string',
			'phone'=>'string',
			'email'=>'string',
			'shippingAddress'=>'string',
			'paymentMethod'=>'int',
			'paymentStatusContent'=>'string',
			'amt'=>'int',
			'amt_artist'=>'int',
			'amt_pixyt'=>'int',
			'items'=>'int',
			'status'=>'int',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		);
	}
	
	public static $statuses = array(
		0=>'Pending',
		1=>'Validated',
		2=>'Canceled',
		3=>'Paid',
		4=>'Under preparation',
		5=>'Sent',
	);
    
	public function getAddress(){
		return $this->name.PHP_EOL.$this->shippingAddress['street'].PHP_EOL.$this->shippingAddress['zipcode'].' '.$this->shippingAddress['city'].PHP_EOL.$this->shippingAddress['country'];
	}
	
	protected function cancel(){
		$r = '';
		T::$page['title'] = translate('Transaction cancelled');
		$this->status = 2;
		$this->update();
		$r .= dv('d600 center');
		$r .= dv('alert-block alert alert-warning').'<h1>'.t('The transaction was cancelled.').'</h1>';
		$r .= t('Your order was cancelled on the payment gateway. If this is a mistake please notify us, or go to your archived orders and retry.');
		$r .= xdv();
		$form = new Form('Message','',true,array('class'=>'full'));
		$form->uid('hidden',1);
		$form->subject('hidden','Order '.$this->id.'/Cancelled');
		$form->content('textarea',translate('Anything we can help you with?'),false);
		$form->{translate('submit')}('submit');
		$r .= '<h3>'.translate('Order ID: ').$this->id.'</h3>'.$form->returnContent();
		$r .= xdv();
		return $r;
	}
	
	protected function success(){
		$r = '';
		//if order is amt null, auto validate it
		if($this->amt == 0 && $this->status != 3){
			$this->status = 3;
			$this->update();
		}
		//else get payment details and validate
		if($this->status != 3){
			if(!isset($_REQUEST['token'])){
				return $this->error('Missing request token',95);
			}
			else{
				$paypal = new Paypal();
				$shipping = $paypal->GetShippingDetails($_REQUEST['token']);
				$this->paymentStatusContent['shipping']=$shipping;
				$confirmation = $paypal->ConfirmPayment($this->amt,$_REQUEST['token']);
				$this->paymentStatusContent['confirmation']=$confirmation;
				if(strtoupper($confirmation['ACK']) == 'SUCCESS'){
					$this->status = 3;
					if(!$this->update()){
						$this->error('Failed to update Order::'.$this->id);
						return false;
					}
				}
				else{
					$this->update();
					$r .=  dv('d2 center').dv('order','Order_'.$this->id);
					$r .=  '<h3>'.translate('Order ID: ').$this->id.'</h3>';
					$r .=  dv('total').translate('Order total:').' '.price2str($this->amt).xdv();
					$r .=  dv('name').$this->name.xdv();
					$r .=  dv('phone').$this->phone.xdv();
					$r .=  dv('mail').$this->email.xdv();
					
					$r .=  dv('padder');
					$r .=  '<h2 class="red">'.translate('Payment failed!').'</h2>'.dv('grey').$confirmation['L_LONGMESSAGE0'].xdv();
					$r .=  xdv().xdv();
					return $r;
				}
			}
		}
		T::$page['title'] = 'Payment complete!';
		$r .=  dv('d960 center').dv('order','Order_'.$this->id);
		$r .=  '<h3>'.translate('Order ID: ').$this->id.'</h3>';
		$r .=  dv('total').translate('Order total:').' '.price2str($this->amt).xdv();
		$r .=  dv('name').$this->name.xdv();
		$r .=  dv('phone').$this->phone.xdv();
		$r .=  dv('mail').$this->email.xdv();
		
		$r .=  '<h2>'.t('Thank you for ordering with Pixyt.').'</h2>';
		$t = count($this->content);
		foreach($this->content as $item){
			$sale = new Sale($item[0]);
			switch($sale->type){
				case 'P':
					if(!isset($sale->data['settings']['hosting'])){
						$this->error('Could not retrieve hosting plan for order#'.$this->id.'!');
						$r .= dv('alert alert-warning alert-block').t('Failed to set your hosting plan. Please contact us at crew@pixyt.com').xdv();
					}
					App::$user->plan = $sale->data['settings']['hosting'];
					if(isset($sale->data['settings']['hosting_details'])){
						App::$user->data['settings']['hosting'] = $sale->data['settings']['hosting_details'];
					}
					App::$user->update();
				break;
			
				case 'R':
					if(!isset($sale->data['settings']['siteid'])){
						$url = $sale->data['settings']['domain'].'.'.$sale->data['settings']['tld'];
						if(Object::objectExists('Site',$url,'url',$id)){
							$sale->data['settings']['siteid'] = $id;
						}
						else{
							$this->error('Failed to lookup website for renewal!');
							$r .= dv('alert alert-error alert-block').t('Failed to load website to renew. Please contact us at crew@pixyt.com').xdv();
							return $r;
						}
					}
					$site = new Site($sale->data['settings']['siteid']);
					
					//Renew domain on OVH
					try {
						$dryrun = false;
						$soap = new SoapClient('https://www.ovh.com/soapi/soapi-re-1.58.wsdl');
						$session = $soap->login(OVH_NIC,OVH_PWD,'fr',false);
						$response = $soap->resellerDomainRenew($session,$site->url, self::$demo);
						sendMail('crew@pixyt.com', 'Sales', App::$user->email, 'Order '.$this->id,'OVH returned: '.print_r($response,true));
						$result = (array)$response;
						$soap->logout($session);
					}
					catch(SoapFault $fault) {
						$this->error($fault);
						$r .= dv('alert alert-error alert-block').t('Renewal failed. Please contact us at crew@pixyt.com').xdv();
						return $r;
					}
					
					//set expiry date
					if(!isset($sale->data['settings']['duration'])){
						$sale->data['settings']['duration'] = 1;
					}
					$exdate = date("Y-m-d", strtotime("+ ".(365*$sale->data['settings']['duration'])." day"));
					$site->data['expirationDate'] = $exdate;
					if(!$site->update()){
						$this->error('Failed to update website! [ovh:'.print_r($result,true).']');
						return false;
					}
					else{
						//set freedomain option
						if(
							(!isset(App::$user->data['settings']['freedomain']) && empty(App::$user->data['settings']['freedomain'])) ||
							(isset($sale->data['settings']['isfreedomain']) && $sale->data['settings']['isfreedomain'] == true)
						){
							App::$user->data['settings']['freedomain'] = $site->id;
						}
						
						$r .= dv('alert alert-success alert-block').'<h2>'.translate('Your website has been renewed!').'</h2>'.xdv();
						$r .= dv('centerText').xtlnk('http://'.$site->url,$site->url,array('class'=>'btn btn-success btn-large')).xdv();
					}
				break;
			
				case 'D':
					//creating a new domain
					$url = $sale->data['settings']['domain'].'.'.$sale->data['settings']['tld'];
					$id=0;
					if(Object::objectExists('Site',$url,'url',$id)){
						$r .= dv('alert alert-block alert-success').'<h2>'.translate('Your website has been created!').'</h2>'.xtlnk('http://'.$url.'/?tour=true').xdv();
						$r .= dv('rightText').xtlnk('http://'.$url.'/edit?tour=1','live site',array('class'=>'btn')).' '.lnk(t('start adding some content'),'site/'.$id.'/edit',array('tour'=>true),array('class'=>'btn btn-success')).xdv();
					}
					else{
						$site = new Site();
						$site->validateData('url',$url);
						
						if(in_array($sale->data['settings']['tld'],Site::$pixytSites)){
							if(!$site->insert()){
								$this->error('Failed to create website!');
								$r .= dv('alert alert-error alert-block').'Failed to insert website. Please contact us at crew@pixyt.com'.xdv();
								return false;
							}
							else{
								$r .= dv('alert alert-block alert-success').'<h2>'.translate('Your website has been created!').'</h2>'.xtlnk('http://'.$url.'/?tour=true').xdv();
								$r .= dv('rightText').xtlnk('http://'.$url.'/edit?tour=1','live site',array('class'=>'btn')).' '.lnk(t('start adding some content'),'site/'.$id.'/edit',array('tour'=>true),array('class'=>'btn btn-success')).xdv();
							}
						}
						else{
							try{
								$dryrun = self::$demo;
								$soap = new SoapClient('https://www.ovh.com/soapi/soapi-re-1.49.wsdl');
								$session = $soap->login(OVH_NIC,OVH_PWD,'fr',false);
								$result = (array)$soap->resellerDomainCreate($session,$url,'none','gold','agent','yes',OVH_NIC,OVH_NIC,OVH_NIC,OVH_NIC,'ns383426.ovh.net','sdns2.ovh.net','','','','siren','Light Architect',SIREN,'','','','','',self::$demo);
								$soap->logout($session);
							}
							catch(SoapFault $fault) {
								$this->error($fault);
								$r .= dv('alert alert-error alert-block').t('Website creation failed. Please contact us at crew@pixyt.com').xdv();
								return $r;
							}
							$site->data['adminNIC'] = OVH_NIC;
							if(!isset($sale->data['settings']['duration'])){
								$sale->data['settings']['duration'] = 1;
							}
							$exdate = date("Y-m-d", strtotime("+ ".(365*$sale->data['settings']['duration'])." day"));
							$site->data['expirationDate'] = $exdate;
							
							if(!$site->insert()){
								$this->error('Failed to create website! ['.print_r($result,true).']');
								return false;
							}
							else{
								//set freedomain option
								if(
									(!isset(App::$user->data['settings']['freedomain']) && empty(App::$user->data['settings']['freedomain'])) ||
									(isset($sale->data['settings']['isfreedomain']) && $sale->data['settings']['isfreedomain'] == true)
								){
									App::$user->data['settings']['freedomain'] = $site->id;
								}
							
								$r .= dv('alert alert-success alert-block').'<h2>'.t('Your website has been created!').'</h2>'.xdv();
								$info = t('There can go up to 48h before the DNS are set and you can access your site.');
								$r .= dv('alert alert-info alert-block').$info.xdv();
								$button = xtlnk('http://'.$url,$url,array('class'=>'btn btn-success btn-large'));
								$r .= dv('rightText').$button.xdv();
								$this->sendReceipt($info.'<br/>'.$button);
							}
						}
					}
				break;
			}
		}
		$r .=  xdv().'<br/><br/><br/><br/><br/>';
		return $r;
	}
	
	public function sendReceipt($msg=''){
		$r = '';
		$r .=  '<h3>'.translate('Order ID:').' '.$this->id.'</h3>';
		$r .=  dv('total').translate('Order total:').' '.price2str($this->amt).xdv();
		$r .=  dv('name').$this->name.xdv();
		$r .=  dv('phone').$this->phone.xdv();
		$r .=  dv('mail').$this->email.xdv();
		if(!empty($msg))$r .=  '<br/><br/><p>'.$msg.'</p>';
		
		$message = array(array('<h1>'.lnk('<img src="http://pixyt.com/logo.jpg?type=user&id='.$this->id.'&type=sale" height="40" width="40" style="position:relative;top:10px;" alt="pixyt" title="pixyt"/>','',array('from'=>'email')).' &nbsp; '.t('Thank you for your order on Pixyt!').'</h1>'),array('<div class="message">'.$r.'</div>'),array('<br/><p style="color:#888;margin:4px;border-top:1px dotted #aaa;">email settings '.xtlnk('http://pixyt.com/account/settings','pixyt.com/account/settings').' - '.t('Pssst!: You can answer this email to tell us something').' :) </p>'));
		return sendMail($this->email, 'Pixyt', 'crew@pixyt.com', t('Order on Pixyt'), $message);
	}
	
	public function construct(){
		
	}
	
	public function adminBox(){
		$r = dv('Order');
		$r .= dv('splitLeft').'<p class="bold">'.nl2br($this->getAddress()).'</p>'.xdv();
		$r .= dv('splitRight rightText').'<p>'.$this->email.'</p>'.'<p>'.$this->phone.'</p><br/>'.'<p>'.translate('Order').' #'.$this->id.', '.date('d/m/Y',$this->created).'</p>'.xdv();
		$r .= '<br class="clearfloat"/><br/><br/>';
		switch($this->status){
			case 1:
				$table = new Table();
				$table->addHeader(array('',translate('filename'),translate('Size'),translate('Print type'),translate('HD file'),translate('Comments')));
				$pending = 0;
				foreach($this->content as $i=>$line){
					switch($line['type']){
						case 'Shopitem':
							$shopitem = new Shopitem($line['shopItemId']);
							$format = new Shopitemformat($line['printFormatId']);
							$btn = '';
							$filename='no name';
							if($story->objectType == 'Photo' && Object::objectExists('Photo',$story->objectId)){
								$photo = new Photo($story->objectId);
								$filename=$photo->filename;
								if(empty($photo->path_hd)){
									$btn = $photo->addHDbutton();
									$pending++;
								}
								else{
									$btn = '<span class="ready">ready</span>';
								}
							}
							$table->addLine(array($story->img('thumb',false),$filename,$format->description,$format->option2str($line['options']['papertype']),$btn,$line['comments']));
						break;
					}
				}
				$confirm = '';
				if($pending == 0){$confirm = dv('contentBox rightText','validateOrder','style="margin-top:20px;border-top:2px dashed #ccc"').lnk(translate('Confirm order and send to the lab'),'admin/orders',array('update[Order]['.$this->id.'][status]'=>3),array('class'=>'btn ready','id'=>'validateOrderBtn')).xdv();}
				else{$confirm = dv('rightText','validateOrder','style="margin-top:20px;border-top:2px dashed #ccc"').translate('Once you have uploaded all the HD files you will be able to send the order to the lab.').lnk(translate('Confirm order and send to the lab'),'admin/orders',array('update[Order]['.$this->id.'][status]'=>3),array('class'=>'btn ready','id'=>'validateOrderBtn','class'=>'hidden')).xdv();}
				$r .= $table->returnContent().$confirm;
				$r.'<script type="text/javascript">pending += '.$pending.';</script>';
			break;
			
			case 3:
				$table = new Table();
				$table->addHeader(array('',translate('filesize'),translate('Size'),translate('Print type'),translate('HD file')));
				$pending = 0;
				foreach($this->content as $i=>$line){
					switch($line['type']){
						case 'Shopitem':
							$shopitem = new Shopitem($line['shopItemId']);
							$format = new Shopitemformat($line['printFormatId']);
							$btn = '';
							$filename='no name';
							if($story->objectType == 'Photo' && Object::objectExists('Photo',$story->objectId)){
								$photo = new Photo($story->objectId);
								$filename=$photo->filename;
								if(empty($photo->path_hd)){
									$btn = '<span class="error">NO HD FILE</span>';
									$pending++;
									$filesize=0;
								}
								else{
									$filesize = round(filesize(ROOT.'local/photos/_hd/'.$photo->path_hd)/1000000,2).'Mo';
									$btn = xtlnk(HOME.'download.php?x=photos/_hd/'.$photo->path_hd,'Download');
								}
							}
							$table->addLine(array($story->img('thumb',false),$filesize,$format->description,$format->option2str($line['options']['papertype']),$btn));
						break;
					}
				}
				$confirm = '';
				if($pending == 0){$confirm = dv('contentBox rightText','validateOrder','style="margin-top:20px;border-top:2px dashed #ccc"').lnk(translate('Confirm order and send to the lab'),'admin/orders',array('update[Order]['.$this->id.'][status]'=>4),array('class'=>'btn ready','id'=>'validateOrderBtn')).xdv();}
				else{$confirm = dv('rightText','validateOrder','style="margin-top:20px;border-top:2px dashed #ccc"').translate('Missing files for order.').lnk(translate('Confirm order and send to the lab'),'admin/orders',array('update[Order]['.$this->id.'][status]'=>4),array('class'=>'btn ready','id'=>'validateOrderBtn','class'=>'hidden')).xdv();}
				$r .= $table->returnContent().$confirm;
				$r.'<script type="text/javascript">pending += '.$pending.';</script>';
			break;
			
			case 4:
				$table = new Table();
				$table->addHeader(array('',translate('filesize'),translate('Size'),translate('Print type'),translate('HD file')));
				foreach($this->content as $i=>$line){
					switch($line['type']){
						case 'Shopitem':
							$shopitem = new Shopitem($line['shopItemId']);
							$format = new Shopitemformat($line['printFormatId']);
							$btn = '';
							$filename='no name';
							if($story->objectType == 'Photo' && Object::objectExists('Photo',$story->objectId)){
								$photo = new Photo($story->objectId);
								$filename=$photo->filename;
								if(empty($photo->path_hd)){
									$btn = '<span class="error">NO HD FILE</span>';
									$filesize=0;
								}
								else{
									$filesize = round(filesize(ROOT.'local/photos/_hd/'.$photo->path_hd)/1000000,2).'Mo';
									$btn = xtlnk(HOME.'download.php?x=photos/_hd/'.$photo->path_hd,'Download');
								}
							}
							$table->addLine(array($story->img('thumb',false),$filesize,$format->description,$format->option2str($line['options']['papertype']),$btn));
						break;
					}
				}
				$confirm = dv('contentBox rightText','validateOrder','style="margin-top:20px;border-top:2px dashed #ccc"').lnk(translate('Validate order'),'orders',array('update[Order]['.$this->id.'][status]'=>5),array('class'=>'btn ready','id'=>'validateOrderBtn')).xdv();
				
				$r .= $table->returnContent().$confirm;
			break;
			
			default:
				$r.= '<p class="big info">This viewing mode is not ready yet.</p>';
			break;
		}
		return $r.xdv();
	}
	
	private function preview(){
		return dv('line highlight','','data-type="popup" data-url="'.HOME.'component/order/'.$this->id.'"').'<span>'.$this->id.'</span>'.'<span>'.$this->name.'</span>'.'<span>'.$this->phone.'</span>'.'<span>'.ShopItem::price2str($this->amt).'</span>'.'<span>'.$this->items.'</span>'.xdv();
	}
	
	public function tableLine(){
		if($this->status != 4){$price='<td>'.ShopItem::price2str($this->amt).'</td>';}else{$price='';}
		if($this->id%2){$c='even';}else{$c='uneven';}
		return '<tr class="line highlight '.$c.'" data-type="popup" data-url="'.HOME.'component/order/'.$this->id.'"><td>'.$this->id.'</td>'.'<td>'.$this->name.'</td>'.'<td>'.$this->phone.'</td>'.'<td>'.$this->email.'</td>'.'<td>'.$this->items.'</td>'.$price.'<td>'.Order::$statuses[$this->status].'</td></tr>';
	}
	
	public function validateData($n,$v){
		switch ($n){
			case 'status':
				if($v==4){
					if($this->sendOrderToPicto()){
						Msg::addMsg(translate('The order has been submitted to Picto'));
						return true;
					}
					else{
						Msg::addMsg(translate('Failed to transfer order to Picto via FTP'));
					}
				}
				elseif($v == 3){
					$pending=0;
					foreach($this->content as $i=>$line){
						switch($line['type']){
							case 'Shopitem':
								$shopitem = new Shopitem($line['shopItemId']);
								if($story->objectType == 'Photo' && Object::objectExists('Photo',$story->objectId)){
									$photo = new Photo($story->objectId);
									if(empty($photo->path_hd)){
										$pending++;
									}
								}
							break;
							
							case 'shipping':
								//$table->addLine(array(translate('shipping'),'','',ShopItem::price2str($line['price'])));
							break;
						}
					}
					if($pending>0){
						Msg::addMsg(translate('You must upload all HD photos before validating order!'));
						return false;
					}
					else{
						$this->$n=$v;
						Msg::addMsg(translate('Order has been validated and will be sent to Picto.'));
						return true;
					}
				}
				elseif($v==5){
					$this->$n=$v;
					Msg::addMsg(translate('The order has been marked as sent and is archived.'));
					return true;
				}
				Msg::addMsg(translate('You cannot change order status manually.'));
			break;
			
			case 'name':
				if(strlen($v)>1 && strlen($v) <= 255){
					$this->$n = $v;
					return true;
				}
				else{
					Msg::addMsg(translate('Please enter your full name.'));
				}
			break;
			
			case 'email':
				if(isEmail($v)){
					$this->$n = $v;
					return true;
				}
				else{
					Msg::addMsg(translate('Please enter a valid email.'));
				}
			break;
			
			case 'phone':
				if(strlen($v) > 8 && strlen($v) < 18){
					$this->$n = $v;
					return true;
				}
				else{
					Msg::addMsg(translate('Please enter a valid phone number.'));
				}
			break;
			
			case 'shippingAddress':
				if(!is_array($v)){
					$this->error('Shipping adress not an array!',90);
					return false;
				}
				else{
					$validate = true;
					foreach($v as $name=>$value){
						switch($name){
							case 'zipcode':
								if(strlen($v) > 4 && strlen($v) < 7 && is_numeric($v)){
									$this->shippingAddress[$n] = $v;
								}
								else{
									Msg::addMsg(translate('Please enter a valid zip code.'));
									$validate = false;
								}
							break;
							
							case 'street':
							case 'city':
							case 'country_code':
								if(strlen($v)>=1 && strlen($v) <= 255){
									$this->shippingAddress[$n] = $v;
								}
								else{
									Msg::addMsg(translate('Please enter a valid address.'));
									$validate = false;
								}
							break;
						}
					}
					return $validate;
				}
			break;
			
			case 'domain':
				if($v != $_SESSION['buy']['domain']){
					$this->error('SESSION domain not matching POST domain');
					Msg::notify('An error occured, please try again.');
					return false;
				}
				$parts = explode('.',$_SESSION['buy']['domain']);
				$domain = strtolower($parts[0]);
				unset($parts[0]);
				$tld = implode('.',$parts);
				
				$this->title = $_SESSION['buy']['domain'];
				$this->type='W';
				$this->settings['tld']=$tld;
				$this->settings['domain']=$domain;
				$this->salesPrice += Product::$tldPrices[$tld];
				return true;
			break;
			
			case 'paymentMethod':
				if(isset(self::$paymentMethods[$v])){
					$this->paymentMethod = 	$v;
					return true;
				}
				else{
					Msg::addMsg(translate('Wrong payement method selected.'));
					return false;
				}
			break;
			
		}
		return false;
	}
	
	function sendOrderToPicto(){
		$order_dir_name = 'Pixyt_'.date('dmy').'_'.$this->id;
		$order_dir_path = PICTO_FTP_DIRPREFIX.'/'.$order_dir_name.'/';
		$local_order_dir_path = LOCAL_ORDER_PATH.'/'.$order_dir_name.'/';
				
		if(empty($this->shippingAddress['street']) || empty($this->shippingAddress['zipcode']) || empty($this->shippingAddress['country']) || empty($this->shippingAddress['city'])){
			$this->error('Invalid address', 95);
			return false;
		}
		
		$conn_id = ftp_connect(PICTO_FTP_SERVER);
		if($conn_id === false){
			$this->error('Connexion to Picto FTP server failed', 95);
			return false;
		}
		
		$login_result = ftp_login($conn_id, PICTO_FTP_USER, PICTO_FTP_PASS);
		if($login_result == false){
			$this->error('Authentification to Picto FTP failed', 95);
			ftp_close($conn_id);
			return false;
		}
		
		$pasv_result = ftp_pasv($conn_id, true);
		if($pasv_result == false){
			ftp_close($conn_id);
			$this->error('FTP passive mode failed', 95);
			return false;
		}
		
		$dir_content = ftp_nlist($conn_id, PICTO_FTP_DIRPREFIX);
		
		foreach($dir_content as $dir){
			if(basename($dir) == $order_dir_name){
				ftp_close($conn_id);
				$this->error('Directory '.$order_dir_path.' already exists', 95);
				return false;
			}
		}
		if(file_exists($local_order_dir_path)){
			ftp_close($conn_id);
			$this->error('Order already exists (locally)', 95);
			return false;
		}
		elseif(!mkdir($local_order_dir_path)){
			ftp_close($conn_id);
			$this->error('Local mkdir failed :'.$local_order_dir_path, 95);
			return false;
		}

		if(ftp_mkdir($conn_id, $order_dir_path) === false){
			ftp_close($conn_id);
			$this->error('FTP mkdir failed :'.$order_dir_path, 95);
			return false;
		}
		
		foreach ($this->content as $item){
			if($item['type'] == 'Shopitem'){
				$shopitem = new Shopitem(intval($item['shopItemId']));
				if($shopitem->isDummy){
					ftp_close($conn_id);
					$this->error('Shopitem id '.$item['shopItemId'].' does no longer exists', 95);
					return false;
				}
				
				$format = new ShopitemFormat($item['printFormatId']);
				
				if($format->type == 'paper'){
					if($story->isDummy){
						ftp_close($conn_id);
						$this->error('Story id '.$shopitem->sid.' does no longer exists', 95);
						return false;
					}
					
					$photo = new Photo($story->objectId);
					if($photo->isDummy){
						$this->error('Photo id '.$story->objectId.' does no longer exists', 95);
						ftp_close($conn_id);
						return false;
					}
					
					$photo_hd_path = ROOT.'local/photos/_hd/'.$photo->data['path_hd'];
					if(!file_exists($photo_hd_path)){
						ftp_close($conn_id);
						$this->error('HD photo does not exists : '.$photo_hd_path, 95);
						return false;
					}
					
					$remote_photo_dirname = $format->getPictoName($item['options']);
					if($remote_photo_dirname === false){
						ftp_close($conn_id);
						$this->error('Item id'.$item['shopItemId'].' has invalid options', 95);
						return false;
					}
					$remote_photo_dirpath = $order_dir_path.$remote_photo_dirname;
					$local_photo_dirpath = $local_order_dir_path.$remote_photo_dirname;
					
					if(!file_exists($local_photo_dirpath)){
						if(!mkdir($local_photo_dirpath)){
							ftp_close($conn_id);
							$this->error('Local mkdir failed :'.$local_photo_dirpath, 95);
							return false;
						}
					}
					
					$dir_content = ftp_nlist($conn_id, $order_dir_path);
					$dir_exists = false;
					foreach($dir_content as $dir){
						if(basename($dir) == $remote_photo_dirname){
							$dir_exists = true;
						}
					}
					if(!$dir_exists){
						if(ftp_mkdir($conn_id, $remote_photo_dirpath) === false){
							ftp_close($conn_id);
							$this->error('FTP mkdir failed :'.$remote_photo_dirpath, 95);
							return false;
						}
					}
					
					if(!isset($item['quantity'])){
						ftp_close($conn_id);
						$this->error('Item id'.$item['shopItemId'].' has no quantity', 95);
						return false;
					}
					
					$remote_photo_filename = $item['quantity'].'ex_'.basename($photo->data['path_hd']);
					$remote_photo_filepath = $remote_photo_dirpath.'/'.$remote_photo_filename;
					$local_photo_filepath = $local_photo_dirpath.'/'.$remote_photo_filename;
					
					if(!copy($photo_hd_path, $local_photo_filepath)){
						ftp_close($conn_id);
						$this->error('Local copy to'.$local_photo_dirpath.' has failed', 95);
						return false;
					}
					
					if (ftp_put($conn_id, $remote_photo_filepath, $photo_hd_path, FTP_BINARY) === false) {
						ftp_close($conn_id);
						$this->error('FTP put for'.$remote_photo_filepath.' has failed', 95);
						return false;
					}
				}
			}
		}
		
		// adresse.txt
		$local_address_path = $local_order_dir_path.'/adresse.txt';
		$remote_address_path = $order_dir_path.'/adresse.txt';
		
		$address_contents = $this->name;
		$address_contents .= "\r\n".$this->shippingAddress['street'];
		$address_contents .= "\r\n".$this->shippingAddress['zipcode'] . "  " . $this->shippingAddress['city'];
		$address_contents .= "\r\n".$this->shippingAddress['country'];
		
		if(file_put_contents($local_address_path, $address_contents) === false){
			ftp_close($conn_id);
			$this->error('FTP copy'.$remote_photo_filepath.' has failed', 95);
			return false;
		}
		
		if (ftp_put($conn_id, $remote_address_path, $local_address_path, FTP_BINARY) === false) {
			ftp_close($conn_id);
			$this->error('FTP put for'.$local_address_path.' has failed', 95);
			return false;
		}
		
		ftp_close($conn_id);
		
		return true;
	}

	public function tableHead(){
		return array('Time','items','Price','VAT','Total','');
	}
	
	public function tableData(){
		$vat = round($this->amt*VATRATE);
		$c = '';
		foreach($this->content as $item){
			$c .= $item[1].' ('.$item[3].')<br/>';
		}
		return array(prettyTime($this->created),$c,price2str($this->amt-$vat),price2str($vat),price2str($this->amt),lnk('<img src="/img/delete-grey.png" width="18px" style="margin-bottom:-2px;" alt="delete"/>','#cur',array('delete[Order]['.$this->id.']'=>true)));
	}
	
	public function tableFoot($class,$extra,$condition){
		$q = 'SELECT SUM(salesPrice),SUM(quantity) FROM `Sale`';
		if(!empty($condition)){
			$q .= ' '.$condition;
		}
		
		if(LP::$db->customQuery($q,$d)){
			$total = (int)$d[0]['SUM(salesPrice)'];
			$quantity = (int)$d[0]['SUM(quantity)'];
		}
		else{
			$total = 0;
			$quantity=0;
		}
		$vat = round($total*VATRATE);
		return array('TOTAL',$quantity,price2str($total-$vat),price2str($vat),price2str($total),'');
	}
	function contents(){
		return nl2br($this->content);
	}
	
	protected function postDelete($force = false){
		res('script','$(".Order_'.$this->id.'_tableLine").fadeOut();');
		return true;
	}
    
	protected function postInsert(){
		$_SESSION['buy'] = array();
		return true;
	}
}
?>