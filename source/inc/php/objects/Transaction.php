<?php
class Transaction extends Object{
	public $uid;
	public $holder;				//payment holder (me (uid) or my company(companyid))
	public $type;				//0:unknown, 1:credit(from card etc), 2: debit(buy stuff), 3:refund
	public $account;			//fr_accounts index
	public $name;				//name label (ex: Picto or client name)
	public $proofFiles=array();	//(fileId,fileId)
	public $paymentMethod;		//0:undifined, 1:check, 2:paypal, 3:cash, 4:bank transfer 5:credit Card
	public $amt;				//amount total in cents (HT)
	public $vatrate;
	public $vat;				//total VAT paid or perceived
	public $status;				//0:pending, 1:done, 2:refused/cancelled
	public $date;
		
	protected static function publicFunctions(){
		return array(
			'fullView'=>true,
			'preview'=>true,
		);
	}
	
	protected static function ownerFunctions(){
		return array();	
	}
	
	public function descriptor(){
		return array(
			'id'=>'int',
			'uid'=>'int',
			'holder'=>'int',
			'type'=>'int',
			'account'=>'int',
			'name'=>'string',
			'proofFiles'=>'object',
			'paymentMethod'=>'int',
			'amt'=>'int',
			'vatrate'=>'int',
			'vat'=>'int',
			'status'=>'int',
			'date'=>'date',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		);
	}
	
	public static function vatrates(){
		return array(
			0=>array('Not applicable',0),
			1=>array('19,6% (normal rate)','0.196'),
			2=>array('7% (art, books..)','0.07'),
		);
	}
	
	public static function paymentTypes(){
		return array(
			1=>'Sale',
			2=>'Buy',
			3=>'Refund'
		);
	}
	
	public static function paymentMethods(){
		return array(
			4=>'bank transfer',
			2=>'paypal',
			1=>'check',
			3=>'cash',
			5=>'credit Card'
		);
	}
	
	public static function holders(){
		return array(
			28=>'Light Architect',
			1=>'Samuel',
			4=>'Robin',
			2=>'Amaury',
		);
	}
	
	public static function statuses(){
		return array(
			0=>'pending',
			1=>'done',
			2=>'refused/cancelled'
		);
	}
	
	public static function fr_accounts(){
		return array(
			'Capitaux'=>array(
				array('Apports',101000),
			),
			'TVA'=>array(
				array('TVA récupérable',445650),
				array('TVA à reverser sur les recettes',445710),
				array('TVA payée',445510),
				array('Crédit de TVA',445670),
			),
			'Charges'=>array(
				array('Sous traitance',604000),
				array('Achats divers',606100),
				array('Domiciliation',613200),
				array('Locations (serveurs, domaines...)',613500),
				array('Honoraires',622600),
				array('Publicité',623100),
				array('Expédition (poste...)',626100),
				array('Frais bancaires',627000),
				array('Frais de création (greffe...)',635400),
				array('Cotisation Agessa',645800)
			),
			'Recettes'=>array(
				array('Services TVA 19,6%',706100),
				array('Services TVA 7%',706200),
				array('Produits TVA 19,6%',707100),
				array('Produits TVA 7%',707200),
			)
		);
	}
	
	public static function matchAccount($id){
		foreach(self::$fr_accounts as $group){
			foreach($group as $account){
				if($account[1] == $id){
					return $id.': '.$account[0];
				}
			}
		}
		return $id.': unknown';
	}
	
	public static function nommenclatureFrancaise($base){
		$b = str_split((string)$base);
		$e = array();
		$table = new Table('nomeclature');
		foreach(Transaction::$fr_accounts as $id=>$account){
			$l = str_split((string)$id);
			$m = count($l);
			$line = array();
			for($i=1;$i<6;$i++){
				if($i == $m){
					$line[] = '<h'.$i.'>'.$account.'</h'.$i.'>';
				}
				else{
					$line[] = '';
				}
			}
			$table->addLine($line);
		}
		$table->insert($r);
		return $table->returnContent();
	}
	
	public static function selectAccount($base=1){
		$b = str_split((string)$base);
		$e = array();
		$form = new Form();
		foreach(Transaction::$fr_accounts as $id=>$account){
			$l = str_split((string)$id);
			$e[count($l)][] = array($id.': '.$account,$id);
		}
		$form->{'getAccount'.count($b)}('select',$e[count($b)],'Select an account','','','loadGetAccount(this.value);');
		T::$js[] = '
var loadGetAccount = function(base){
	$(".getAccount").append("<div class=\"loading contentBox\"><img src=\"/img/ajax-loader.gif\"/></div>");
	$(".getAccount").load("'.HOME.'component/selectAccount/"+base, function() {
		$(".getAccount").find(".loading").fadeOut();
	});
}
';
		return dv('getAccount').$form->returnContent().xdv();
	}
		
	public static function addTransaction(){
		$form = new Form('Transaction',NULL,'compta');
		$form->{translate('Account')}('label');
		$form->account('select',Transaction::$fr_accounts,translate('Account'));
		$form->{translate('Label')}('label');
		$form->name('input',false,translate('Transaction label'));
		$form->type('select',Transaction::$paymentTypes,translate('Payment type'));
		$form->{translate('Payment method')}('label');
		$form->paymentMethod('select',Transaction::$paymentMethods,translate('Payment method'));
		$form->amt('input',0.00,translate('Transaction amount').' (€ cents)');
		$form->vat('input',0.00,translate('VAT paid or perceived').' (€ cents)');
		$form->{translate('Payment holder')}('label');
		$q = 'SELECT id,firstname,lastname FROM User WHERE access < 30';
		if(!LP::$db->customQuery($q,$d)){
			$users = array(0=>'ERROR');
		}
		else{
			$users = array();
			foreach($d as $user){
				$users[$user['id']] = $user['firstname'].' '.$user['lastname'];
			}
		}
		
		$form->holder('select',$users,translate('Payment holder'));
		$form->{translate('Status')}('label');
		$form->status('select',Transaction::$status,translate('Payment status'));
		$form->{translate('Date')}('label');
		$form->{'date'}('date');
		$form->{translate('save')}('submit');
		return dv('contentBox').'<h2>'.translate('Add a transaction').'</h2>'.$form->returnContent().xdv();
	}
	
	public static function create(){
		T::$page['title'] = 'Create transaction';
		$table = new Table();
		$form = new Form('Transaction',NULL,'compta',array());
		$jsrates = array();
		foreach(self::$vatrates as $id=>$rate){
			$jsrates[] = $rate[1];
		}
		$table->insert('<script type="text/javascript">
			var vatrates = Array('.implode(',',$jsrates).');
			var Transaction_calcVAT = function(){
			var ttc = str2price($("#insert_Transaction_value").val());
			var rate = vatrates[$("#insert_Transaction_vatrate").val()];
			var ht = Math.round(ttc/(1+rate));
			var vat = Math.round(ttc-ht);
			$("#insert_Transaction_amt").val(ttc);
			$("#insert_Transaction_vat").val(vat);
			$("#Transaction_HT").html(price2str(ht));
			$("#Transaction_TTC").html(price2str(ttc));
			}</script>');
		$table->insert($form->head());
		
		$accounts = array();
		foreach(Transaction::$fr_accounts as $cat=>$options){
			$accounts[$cat] = array('type'=>'optgroup','label'=>$cat,'options'=>array());
			foreach($options as $opt){
				$accounts[$cat]['options'][] = array($opt[0],$opt[1]);
			}
		}
		$form->account('select',$accounts,translate('Account'));
		$table->addLine(array(translate('Type of transaction'),$form->account));
		
		$form->name('input','',false);
		$table->addLine(array(translate('Label'),$form->name));
		
		$form->paymentMethod('select',Transaction::$paymentMethods,translate('Payment method'));
		$table->addLine(array(translate('Payment method'),$form->paymentMethod));
		
		$form->value('input',price2str(0),false,'','','Transaction_calcVAT()','','','','Transaction_calcVAT()');
		$form->amt('hidden',0);
		$table->addLine(array(translate('Amount (incl. VAT)'),$form->value.$form->amt));
		
		$rates = array();
		foreach(self::$vatrates as $n=>$v){
			$rates[$n]=$v[0];
		}
		$form->vat('hidden',0);
		$form->vatrate('select',$rates,translate('VAT rate'),'','','Transaction_calcVAT()');
		$table->addLine(array(translate('VAT rate'),$form->vatrate.$form->vat));

		
		$form->holder('select',self::$holders,false);
		$table->addLine(array(translate('Payment holder'),$form->holder));
		
		$opt = array();
		foreach(Transaction::$status as $n=>$v){
			$opt[$n] = $v;
		}
		$form->status('select',$opt,false);
		$table->addLine(array(translate('Payment status'),$form->status));
		
		$form->{translate('Date')}('label');
		$form->{'date'}('date');
		$table->addLine(array(translate('Date'),$form->{'date'}));
		
		$form->{translate('save')}('submit');
		$table->addLine(array('Total: <span id="Transaction_HT">'.price2str(0).'</span>HT (<span id="Transaction_TTC">'.price2str(0).'</span>TTC)',$form->{translate('save')}),'total');
		
		return '<h1>Add payment</h1>'.$table->returnContent();
	}
	
	protected function edit(){
		if(isset($_REQUEST['do'])){
			switch($_REQUEST['do']){
				case 'delete':
					if($this->delete(true)){
						res('script','$("tr[data-url=\'http://pixyt.com/transaction/'.$this->id.'/edit\']").fadeOut().remove();');
						return '<h2>Transaction was deleted.</h2>';
					}
					else{
						return '<h2>Failed to delete transaction.</h2>';
					}
				break;
			}
		}
		
		T::$page['title'] = $this->name;
		$table = new Table();
		$form = new Form('Transaction',$this->id,'compta');
		$jsrates = array();
		foreach(self::$vatrates as $id=>$rate){
			$jsrates[] = $rate[1];
		}
		$table->insert('<script type="text/javascript">
			var vatrates = Array('.implode(',',$jsrates).');
			var Transaction_'.$this->id.'_calcVAT = function(){
			var ttc = str2price($("#update_Transaction_'.$this->id.'_value").val());
			var rate = vatrates[$("#update_Transaction_'.$this->id.'_vatrate").val()];
			var ht = Math.round(ttc/(1+rate));
			var vat = Math.round(ttc-ht);
			$("#update_Transaction_'.$this->id.'_amt").val(ttc);
			$("#update_Transaction_'.$this->id.'_vat").val(vat);
			$("#Transaction_'.$this->id.'_HT").html(price2str(ht));
			$("#Transaction_'.$this->id.'_TTC").html(price2str(ttc));
			}</script>');
		$table->insert($form->head());
		$accounts = array();
		foreach(Transaction::$fr_accounts as $cat=>$options){
			$accounts[$cat] = array('type'=>'optgroup','label'=>$cat,'options'=>array());
			foreach($options as $opt){
				$accounts[$cat]['options'][] = array($opt[0],$opt[1]);
			}
		}
		$form->account('select',$accounts,translate('Account'));
		$table->addLine(array(translate('Type of transaction'),$form->account));
		
		$form->name('input',$this->name,false);
		$table->addLine(array(translate('Label'),$form->name));
		
		$form->paymentMethod('select',Transaction::$paymentMethods,translate('Payment method'));
		$table->addLine(array(translate('Payment method'),$form->paymentMethod));
		
		$form->value('input',price2str($this->amt),false,'','','Transaction_'.$this->id.'_calcVAT()','','','','Transaction_'.$this->id.'_calcVAT()');
		$form->amt('hidden',$this->amt);
		$table->addLine(array(translate('Amount (incl. VAT)'),$form->value.$form->amt));
		
		$rates = array();
		$rates[$this->vatrate]=self::$vatrates[$this->vatrate][0];
		foreach(self::$vatrates as $n=>$v){
			if($n!=$this->vatrate){
				$rates[$n]=$v[0];
			}
		}
		$form->vat('hidden',$this->vat);
		$form->vatrate('select',$rates,translate('VAT rate'),'','','Transaction_'.$this->id.'_calcVAT()');
		$table->addLine(array(translate('VAT rate'),$form->vatrate.$form->vat));

		
		$form->holder('select',self::$holders,false);
		$table->addLine(array(translate('Payment holder'),$form->holder));
		
		$opt = array();
		$opt = array($this->status,Transaction::$status[$this->status],'selected');
		foreach(Transaction::$status as $n=>$v){
			if($n!=$this->status){$opt[$n] = $v;}
		}
		$form->status('select',$opt,false);
		$table->addLine(array(translate('Payment status'),$form->status));
		
		$form->{translate('Date')}('label');
		$form->{'date'}('date',$this->date);
		$table->addLine(array(translate('Date'),$form->{'date'}));
		
		$form->{translate('save')}('submit');
		$table->addLine(array('Total: <span id="Transaction_'.$this->id.'_HT">'.price2str($this->amt/(1+$this->vatRate())).'</span>HT (<span id="Transaction_'.$this->id.'_TTC">'.price2str($this->amt).'</span>TTC)',$form->{translate('save')}),'total');
		$table->insert($form->foot());
		
		//$del = dv('padded').lnk('Delete','transaction/'.$this->id.'/edit',array('do'=>'delete'),array('data-type'=>'popup','class'=>'btn')).xdv();
		$r = '<h1>'.$this->name.'</h1>'.$table->returnContent();
		if(count($this->proofFiles) == 0){$empty=' empty';}else{		
			foreach($this->proofFiles as $fileid){
				$file = new File($fileid);
				$r .= dv('file').'<img style="width:120px;height:auto;" src="'.$file->url().'" alt="'.$file->filename.'"/>'.xdv();
			}
			$empty='';
		}
		$r .= dv('proofFiles').xdv().dv('uploadBox'.$empty).File::uploadBtn(5,$this->id,0,'proofFiles').xdv();//.$del;
		return $r;
	}
	
	public function validateData($n,$v){
		switch($n){
			case 'id':
			case 'uid':
			case 'created':
			case 'modified':
			case 'day':
			case 'month':
			case 'year':
			case 'value':
				return true;
			break;
			
			case 'holder':
				if(Object::objectExists('User',$v)){
					$this->$n = $v;
					return true;
				}
				else{
					Msg::addMsg(translate('Holder not found'));
				}
			break;
			
			case 'account':
				$this->$n = $v;
				return true;
			break;
			
			case 'date':
				$this->$n = $v;
				return true;
			break;
			
			case 'name':
				if(strlen($v) < 255 && strlen($v) > 1){
					$this->$n = $v;
					return true;
				}
				else{
					Msg::addMsg(translate('Invalid name input'));
				}
			break;
			
			case 'proofFiles':
				if(Object::objectExists('File',$v)){
					array_push($this->$n,$v);
					return true;
				}
				else{
					Msg::addMsg(translate('Proof file not found'));
				}
			break;
			
			case 'paymentMethod':
				if(isset(Transaction::$paymentMethods[$v])){
					$this->$n = $v;
					return true;
				}
				else{
					Msg::addMsg(translate('Wrong payment method'));
				}
			break;
			
			case 'type':
				if(isset(Transaction::$paymentTypes[$v])){
					$this->$n = $v;
					return true;
				}
				else{
					Msg::addMsg(translate('Wrong type'));
				}
			break;
			
			case 'status':
				if(isset(Transaction::$status[$v])){
					$this->$n = $v;
					return true;
				}
				else{
					Msg::addMsg(translate('Wrong status'));
				}
			break;
			
			case 'vatrate':
				if(isset(Transaction::$vatrates[$v])){
					$this->$n = $v;
					return true;
				}
				else{
					Msg::addMsg(translate('Wrong VAT rate'));
				}
			break;
			
			case 'amt':
				if(is_numeric($v)){
					$this->$n = intval($v);
					return true;
				}
				else{
					Msg::addMsg(translate('Non numeric amt input'));
				}
			break;
			
			case 'vat':
				if(is_numeric($v)){
					$this->$n = intval($v);
					return true;
				}
				else{
					Msg::addMsg(translate('Non numeric amt input'));
				}
			break;
			
			default:
				Msg::addMsg($n.' not set.');
			break;
		}
	}
	
	public function vatRate(){
		if(isset(self::$vatrates[$this->vatrate][1])){
			return self::$vatrates[$this->vatrate][1];
		}
		else{
			return self::$vatrates[0][1];
		}
	}
	
	public function tableData(&$class,&$xtra){
		$xtra = 'data-type="popup" data-url="'.HOME.'transaction/'.$this->id.'/edit"';
		$class = 'highlight line';
		$data = array();
		$holder = new User($this->holder);
	//	$data[] = $this->id;
	//	$data[] = $holder->fullName();
		$data[] = Transaction::matchAccount($this->account);
		$data[] = $this->name;
		$data[] = count($this->proofFiles).' '.translate('files');
	//	$data[] = Transaction::$paymentMethods[$this->paymentMethod];
		if($this->amt*(1+($this->vatrate/100)) < 0){$c = 'red';}else{$c='green';}
		$data[] = '<span class="'.$c.'">'.price2str($this->amt).'</span>';
		if($this->vat < 0){$c = 'red';}else{$c='green';}
		$data[] = '<span class="'.$c.'">'.price2str($this->vat).'</span>';
		if($this->amt < 0){$c = 'red';}else{$c='green';}
		$data[] = '<span class="'.$c.'">'.price2str($this->amt/(1+($this->vatRate()))).'</span>';
		$data[] = $this->{'date'};
		return $data;
	}
	
	public static function balance($condition=''){
		if(!is_array($condition)){$condition = array($condition);}
		return App::$db->sum('amt','Transaction',$condition);
	}
	
	public static function vatBalance($condition=''){
		if(!is_array($condition)){$condition = array($condition);}
		return App::$db->sum('vat','Transaction',$condition);
	}
	
	public function tableFoot($class,$xtra,$condition){
		$data[] = 'total:';
		$data[] = '';
//		$data[] = '';
//		$data[] = '';
		$data[] = '';
		$total = Transaction::balance($condition);
		if($total < 0){$c = 'red';}else{$c='green';}
		$data[] = '<span class="'.$c.'">'.price2str($total).'</span>';
		$vatTotal = Transaction::vatBalance($condition);
		if($vatTotal < 0){$c = 'red';}else{$c='green';}
		$data[] = '<span class="'.$c.'">'.price2str($vatTotal).'</span>';
		$htTotal = $total-$vatTotal;
		if($htTotal < 0){$c = 'red';}else{$c='green';}
		$data[] = '<span class="'.$c.'">'.price2str($htTotal).'</span>';
		$data[] = '';
		return $data;
	}
	
	public static function grandTotal(){
		$r = '';
		$total = Transaction::balance();
		$vatTotal = Transaction::vatBalance();
		$htTotal = $total-$vatTotal;
		$r .= dv('grandTotal rightText');
		$r .= '<h2>HT: '.price2str($htTotal).'</h2>';
		$r .= '<h2>VAT: '.price2str($vatTotal).'</h2>';
		$r .= '<h2>TTC: '.price2str($total).'</h2>';
		return $r;
	}
	
	public function tableHead(){
		$data = array();
//		$data[] = 'ID';
	//	$data[] = translate('Holder');
		$data[] = translate('Account');
		$data[] = translate('Label');
		$data[] = translate('Proof files');
//		$data[] = translate('Payment method');
		$data[] = 'TTC';
		$data[] = translate('VAT');
		$data[] = 'HT';
		$data[] = translate('Date');
		return $data;
	}
	
	protected function postInsert(){
		res('script','$(".xbox").remove();xbox("'.HOME.'transaction/'.$this->id.'/edit",true);');
		return true;
	}
}
?>