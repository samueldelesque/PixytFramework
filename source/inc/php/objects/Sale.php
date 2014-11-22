<?php
class Sale extends Object{
	/*
	
	Sales describe a pre-order state. A sale occurs whenever a customer clicks a "buy" button. It replace the SESSION saved carts.
	
	*/
	public $uid;
	public $proprietor;			//usually us
	public $bid;
	public $title;
	public $quantity=1;
	public $type;				//D: domain, R: renew, P: plan, T: print, M: Magazine W:P+D (obsolete)
	public $session;
	public $settings=array();
	public $salesPrice;
	public $discount;
	public $status;				//0: In cart, 1: Order created
	
	public $isfreedomain = false;
	
	public static function durationDiscount($discount=NULL){
		$discounts = array(
			0=>0,
			1=>0,
			2=>0.05,
			3=>0.1,
		);
		if(isset($discounts[$discount])){return $discounts[$discount];}
		elseif($discount!=NULL){return false;}
		else{return $discount;}
	}
	
	public static function promoCodes(){
		return array(
			'lancement2013'=>array('label'=>'Website launch 2013','reduction'=>0.20,'plan'=>'*'),
		);
	}
	
	public function descriptor(){
		return array (
			'id'=>'int',
			'uid'=>'int',
			'proprietor'=>'int',
			'bid'=>'int',
			'title'=>'string',
			'quantity'=>'int',
			'type'=>'string',
			'session'=>'string',
			'settings'=>'object',
			'salesPrice'=>'int',
			//always set price per year for hosting/domain etc
			'discount'=>'int',
			'status'=>'int',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		);
	}
	
	protected function publicFunctions(){
		return array(
			'fullView'=>true,
			'preview'=>true,
		);
	}
	
	protected function ownerFunctions(){
		return array(
			'edit'=>true,
			'editPreview'=>true,
		);
	}
	
	public function validateData($n,$v){
		switch($n){
			case 'domain':
				$v = strtolower($v);
				$parts = explode('.',$v);
				$domain = strtolower($parts[0]);
				unset($parts[0]);
				$tld = implode('.',$parts);
				
				$this->title = $v;
				$this->type='D';
				$this->settings['tld']=$tld;
				$this->settings['domain']=$domain;
				if($this->isfreedomain){
					$this->settings['isfreedomain']=true;
				}
				else{
					$this->salesPrice += Product::tldPrices($tld);
				}
				return true;
			break;
			
			case 'plan':
				$plan = Product::packages($v);
				if(!is_array($plan)){
					Msg::notify(t('Sorry this hosting plan was not found'));
					return false;
				}
				$this->title = t($plan['label'].' plan');
				$this->type='P';
				$this->settings['hosting']=$v;
				$sale->data['settings']['hosting_details'] = $plan;
				$this->salesPrice += intval($plan['price'])*12;
				if(isset($_SESSION['buy']['promo'])){
					$this->salesPrice -= ($this->salesPrice*12*$_SESSION['buy']['promo']['reduction']);
				}
				return true;
			break;
			
			case 'renew':
				if(!Object::objectExists('Site',$v)){
					$this->error('Wrong site entry for renewal');
					Msg::notify(t('Could not find the site to renew!'));
					return false;
				}
				$site = new Site($v);
				$parts = explode('.',$site->url);
				$domain = strtolower($parts[0]);
				unset($parts[0]);
				$tld = implode('.',$parts);
				
				$this->title = t('Renewal of {$1}',$site->url);
				$this->type='R';
				$this->settings['siteid'] = $site->id;
				$this->settings['tld']=$tld;
				$this->settings['domain']=$domain;
				if($this->isfreedomain){
					$this->settings['isfreedomain']=true;
				}
				else{
					$this->salesPrice += Product::tldPrices($tld);
				}
				return true;
			break;
			
			case 'duration':
				$v = intval($v);
				if($v <=0 || $v > 10){
					$this->error('Wrong duration input: '.$v);
					return false;
				}
				
				$discount = Sale::durationDiscount($v);
				//also update the plan duration if changin domain duration
				if(isset($_SESSION['buy']['plan_inserted'])){
					$plan = new Sale($_SESSION['buy']['plan_inserted']);
					$plan->data['settings']['duration']=$v;
					$plan->data['salesPrice'] = $plan->data['salesPrice']*$v;
					$plan->data['discount'] = $discount*$plan->data['salesPrice'];
					$plan->update();
				}
				$this->settings['duration']=$v;
				$this->salesPrice = $this->salesPrice*$v;
				$this->discount = $discount*$this->salesPrice;
				return true;
			break;
			
			case 'hosting':
				$this->error('Cannot buy hosting at this point...');
				return false;
				if(!isset(Product::$webspace[$v])){
					Msg::notify(translate('Sorry this hosting option was not found').' ('.prettyBytes($v).')');
					return false;
				}
				$this->type='W';
				$this->settings['hosting']=$v;
				$this->salesPrice += Product::$webspace[$v]['price'];
				return true;
			break;
			
			case 'title':
				if(strlen($v) <= 255){
					$this->$n=$v;
					return true;
				}
				$this->error('Sale title too long');
				return false;
			break;
			
			case 'product':
				$v = intval($v);
				if(Object::exists('Product',$v)){
					$product = new Product($v);
					$this->uid = App::$user->id;
					$this->proprietor = $product->uid;	//the seller (0 == pixyt)
					$this->bid = $product->id;
					/*
					switch($product->type){
						case 'd':
							//domain
							if(!isset($this->settings['tld'])){
								$this->error('TLD not defined on Sale.');
								return false;
							}
							if(!isset(Product::$tldPrices[$this->settings['tld']])){
								$this->error('Failed to find price for TLD.');
								return false;
							}
							$time = ceil(Product::$tldduration/$product->duration);
							$price = (Product::$tldPrices[$this->settings['tld']]/$time)+intval($product->basePrice);
							$price = ceil($price);
							if($price<50||$price>1000000){
								$this->error('Invalid price appeared in Sale [price='.$price.']');
								Msg::notify(translate('The saleprice seems to exceed our price range (0,5 to 10 000). Please contact an admin.'));
								return false;
							}
							$this->salesPrice = $price;
						break;
						
						default:
							$this->error('Sorry this item type is not yet sold.');
							return false;
						break;
					}*/
					$this->salesPrice = $product->basePrice;
					return true;
				}
				else{
					$this->error('Sorry that product doesnt exists!');
					return false;
				}
			break;
			
			default:
				$this->error('Invalid sale property ('.$n.')');
			break;
		}
	}
	
	public function tableHead(){
		return array('Product','Quantity','Price','VAT','Total','');
	}
	
	public function tableData(){
		$vat = round($this->salesPrice*VATRATE);
		return array($this->title,$this->quantity,price2str($this->salesPrice-$vat),price2str($vat),price2str($this->salesPrice),lnk('<img src="/img/delete-grey.png" width="18px" style="margin-bottom:-2px;" alt="delete"/>','#cur',array('delete[Sale]['.$this->id.']'=>true)));
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
	
	public function postInsert(){
		$this->session = session_id();
		$this->update();
		return true;
	}
	
	public function postDelete($force = false){
		res('script','$(".Sale_'.$this->id.'_tableLine").fadeOut();');
		return true;
	}
}
?>