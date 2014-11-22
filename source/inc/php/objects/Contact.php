<?php
//class Contact extends Object{
class _to_be_rebuilt{
	public $idName = 'cid';
	public static $maxVcfContactsPerLoad = 50;
	public static $added = array();
	public $cleanup = false;
	public static $descriptor = array (
		'id'=>Object::HID
		,'uid'=>Object::HINT	//whose contact it is
		,'type'=>Object::HBOOL	//0:person/1:firm
		,'firstname'=>Object::V255
		,'lastname'=>Object::V255
		,'jobtitle'=>Object::V255	//if firm > type of firm (agency, studio, label...)
		,'company'=>Object::V255
		,'emails'=>Object::OBJ
		,'phones'=>Object::OBJ
		,'websites'=>Object::OBJ
		,'address'=>Object::OBJ
		,'birthday'=>Object::VDATE
		,'gender'=>Object::HBOOL
		,'circle'=>Object::V255		
		,'mailList'=>Object::HBOOL		 
		,'cameFrom'=>Object::V255	//where contact was found
		,'notes'=>Object::HTEXT
		,'created'=>Object::HINT
		,'modified'=>Object::HINT
	);
	
	public $ownerFunctions = array(
		'preview'=>true,
	);
	
	public static $defaultValues = 
		array (
		'id'=>0
		,'uid'=>0	//whose contact it is
		,'type'=>0	//0:person/1:firm   === isCompany
		,'firstname'=>NULL
		,'lastname'=>NULL
		,'jobtitle'=>NULL	//if firm > type of firm (agency, studio, label...)
		,'company'=>NULL
		,'emails'=>array()
		,'phones'=>array('home'=>'','mobile'=>'','work'=>'','fax'=>'')
		,'websites'=>array()
		,'address'=>array(
			'home'=>array(
				'addr1' =>'',
 				'addr2' =>'',
				'city' =>'',
				'state' =>'',
				'zipcode'=>'',
				'country'=>''
			),
			'work'=>array(
				'addr1' =>'',
 				'addr2' =>'',
				'city' =>'',
				'state' =>'',
				'zipcode'=>'',
				'country'=>''
			)
			// may add other as: 'LABEL'=>array('addr1'=>...)
		)
		,'birthday'=>'0000-00-00'
		,'gender'=>0 //M:0, F:1
		,'circle'=>array(
			'friends'=>false,
			'family'=>false,
			'work'=>false,
			'network'=>true
		)		
		,'mailList'=>array(
			//NEWSLETTERID=>BOOL
		)
		,'cameFrom'=>NULL	//where contact was found
		,'notes'=>NULL
		,'created'=>0
		,'modified'=>0
	);
	
	public static $requiredFields = array('uid','firstname');
	
	public function construct(){
		if($this->cleanup){
			$w=array();
			foreach($this->websites as $id=>$website){
				if(strlen($website) > 40 || empty($website) || in_array($website,$w)){unset($this->websites[$id]);$this->update();}
				$w[] = $website;
			}
			$p=array();
			foreach($this->phones as $id=>$p){
				if(strlen($p) > 40 || empty($p) || in_array($p,$w)){unset($this->phones[$id]);$this->update();}
				$ps[] = $p;
			}
		}
	}
	
	public function validateData($n,$v){
		if(!chkutf8($v)){$v = mb_convert_encoding($v,'utf8');}
		
		switch ($n){
			case 'id':
			case 'created':
			case 'modified':
		    		return true;
		    break;
			
			case 'uid':
				if(is_numeric($v)){$this->$n = $v; return true;}
			break;
			
			case 'emails':
			case 'phones':
			case 'websites':
				if($n == 'emails' || $n = 'invitations'){
					if(!isEmail($v)){$this->error('Wrong email input',10);return false;}
				}
				if(!in_array($v,$this->emails[$n])){
					array_push($this->emails[$n],$v);
				}
				return true;
		    break;
			
		   	case 'birthday':
				$this->$n=$v;
			break;
			
			case 'gender':
			case 'type':
			case 'mailList':
			   	$this->$n=(bool)$v;
				return true;
			break;
			
			case 'notes':
			   	$this->$n=(string)$v;
				return true;
			break;
			
			case 'firstname':
			case 'lastname':
			case 'jobtitle':
			case 'company':
			case 'cameFrom':
			case 'circle':
				if(strlen($v) > 255){Msg::addMsg(translate('Name may not exceed 255 chars.'));return false;}
				$this->$n=str_replace(array('0','1','2','3','4','5','6','7','8','9'),'',$v);
				return true;
			break;
			
			
			// SPECIAL CASES: VCF FROMATS ETC
			case 'surname':
				if(strlen($v) > 255){Msg::addMsg(translate('Name may not exceed 255 chars.'));return false;}
				$this->lastname=rmvmrkr($v);
				return true;
			break;
			case 'organization':
				if(strlen($v) > 255){Msg::addMsg(translate('Name may not exceed 255 chars.'));return false;}
				$this->company=rmvmrkr($v);
				return true;
			break;
			default:
				$this->notes .= $n.':'.$v.PHP_EOL;
			break;
		}
	}
	
	protected function preview(){
		$r = '';
		$style='';
		if($this->type){
			$style='style="background:#E6E6E6 url(/img/factory.png) center bottom no-repeat;"';
		}
		$r .= dv('previewBox preview','contact_'.$this->id,$style);
		$r .= dv('tools').lnk('X','#',array('delete[Contact]['.$this->id.']'=>true),array('class'=>'delete btn right','id'=>'delete_contact_'.$this->id.''));
		$r .= lnk('#','#',array('update[Contact]['.$this->id.'][type]'=>!$this->type),array('data-gethtml'=>'false','class'=>'btn right','id'=>'contact_'.$this->id.'_delete')).xdv();
		$r .= '<h2 class="name" class="left"><span class="editable" id="update_Contact_'.$this->id.'_firstname">'.$this->firstname.'</span> <span class="editable" id="update_Contact_'.$this->id.'_lastname">'.$this->lastname.'</span></h2>';
		foreach($this->phones as $number){if(!empty($number)){$r .= '<p class="phone">'.$number.'</p>';}}
		foreach($this->emails as $mail){if(!empty($mail)){$r .= '<p class="mail">'.lnk($mail,'message',array('to'=>$mail)).'</p>';}}
		foreach($this->websites as $site){if(!empty($site)){$r .= '<p class="site">'.xtlnk(makeUrl($site)).'</p>';}}

		//$r .= '<p class="cameFrom">'.$this->cameFrom.'</p>';
		//$r .= '<p class="notes">'.$this->notes.'</p>';
		$r .= xdv();
		return $r;
	}
	
	public function tableHead(){
		return array('Name','email','phone','website','');
	}
	
	public function tableData(){
		$del = lnk('X','#',array('delete[Contact]['.$this->id.']'=>true),array('class'=>'delete big','id'=>'delete_contact_'.$this->id));
		return array($this->fullName(),implode('<br/>',$this->emails),implode('<br/>',$this->phones),implode('<br/>',$this->websites),$del);
	}
	
	public function fullName(){
		if($this->isOwner()){
			return '<span id="update_Contact_'.$this->id.'_firstname">'.ucfirst($this->firstname).'</span> '.'<span id="update_Contact_'.$this->id.'_firstname">'.ucfirst($this->lastname).'</span>';
		}
		return ucfirst($this->firstname).' '.ucfirst($this->lastname);
	}
	
	public static function checkFile(){
		$content = file_get_contents(ROOT.'local/contacts.txt');
		$s = array('eÃÅ','√©','√¶');
		$r = array('é','é','æ');
		$content = str_replace($s,$r,$content);
		$contacts = explode(PHP_EOL.'---',$content);
		$r = array();
		$max = 100;
		$dummy = new Contact();
		$existing = new Collection('Contact');
		$existing->uid = App::$user->id;
		$myContacts = $existing->content(1);
		$knownEmails = array();
		$knownNames = array();
		$knownPhones = array();
		foreach($myContacts as $contact){
			foreach($contact->data['emails'] as $email){
				$knownEmails[$email] = $contact->id;
			}
			foreach($contact->data['phones'] as $phone){
				$knownEmails[$phone] = $contact->id;
			}
			$knownNames[$contact->id] = $contact->firstname.' '.$contact->lastname; 
		}
		$verify = array();
		$table = new Table();
		$header = array('Id','uid','firstname (or company)','lastname (or type)','emails','phones','websites','cameFrom','notes');
		$table->addHeader($header);
		$total = count($contacts);
		$start = 0;
		$added = 0;
		$limit = $total;
		if(isset($_REQUEST['s'])){$start = ((int)$_REQUEST['s'] * $max)-1;}
		if($total > $max+$start){
			$limit = $max+$start;
			$next = $limit;
		}
		if(isset($_REQUEST['s'])){$i = $_REQUEST['s'];}
		for($i=$start;$i<$limit;$i++){
			$r[$i] = new Contact();
			$lines = explode(PHP_EOL,$contacts[$i]);
			foreach($lines as $line){
				$field = explode(':',$line);
				if(isset($field[1])){
					switch($field[0]){
						case 'Name':
							$n = explode(' ',$field['1']);
							if(isset($n[2])){
								$r[$i]->firstname = ucfirst($n[1]);
								$r[$i]->lastname = ucfirst($n[2]);
							}
							else{
								$r[$i]->firstname = $n[0];
							}
						break;
						
						case 'Email(WORK)':
							$r[$i]->data['emails']['work'] = $field['1'];
						break;
						
						case 'Email(HOME)':
							$r[$i]->data['emails']['home'] = $field['1'];
						break;
						
						case 'Job Title':
							$r[$i]->data['jobtitle'] = $field['1'];
						break;
						
						case 'Phone(CELL)':
							$r[$i]->data['phones']['cell'] = $field['1'];
						break;
						
						case 'Phone(HOME)':
							$r[$i]->data['phones']['home'] = $field['1'];
						break;
						
						case 'Phone(WORK)':
							$r[$i]->data['phones']['work'] = $field['1'];
						break;
						
						case 'Company':
							$r[$i]->data['company'] = $field['1'];
						break;
						
						default:
							$r[$i]->data['notes'] .= $field[0].': '.$field[1];
						break;
					}
				}
			}
			if(empty($r[$i]->data['phones']) && empty($r[$i]->data['emails'])  && empty($r[$i]->data['websites'])){$r[$i]->data = self::$defaultValues;}
			else{
				if(empty($r[$i]->firstname) && !empty($r[$i]->company)){$r[$i]->type = true;}
				$save = true;
				foreach($r[$i]->data['phones'] as $phone){
					if(in_array($phone,$knownPhones)){
						$save = false;
					}
				}
				foreach($r[$i]->data['emails'] as $email){
					if(in_array($email,$knownEmails)){
						$save = false;
					}
				}
				
				if($save){
					$added ++;
				}
				else{
					$verify[] = $i;
				}
				//$table->addLine($r[$i]->line());
			}
		}
		//T::$body[] = $table->returnContent();
	}
	
	public function fromVCard($card){
		if(self::contactExist($card->displayname,self::$added)){
			$this->error('Contact already exist',20);
			return false;
		}
		if(!is_array($vcard) && !is_object($vcard)){
			$this->error('Invalid Vcard table');
			return false;
		}
		foreach($card as $n=>$v){
			if(!$this->validateData($n,$v)){
				$this->error('Could not validate field:'.$n);
				return false;
			}
		}
		self::$added[] = $card->displayname;
		return true;//No error noticed
	}
	
	public static function contactExist($name,$names,&$index){
		if(!is_array($names)){return false;}
		foreach($names as $id=>$fullname){
			if(self::similarName($name,$fullname)){
				$index = $id;
				return true;
			}
		}
		$index = 0;
		return false;
	}
	
	public static function similarName($name1,$name2,&$probability){
		$probability = 0;
		$name1 = preg_replace('/[^a-zA-Z0-9]/',' ',strtolower($name1));
		$name2 = preg_replace('/[^a-zA-Z0-9]/',' ',strtolower($name2));
		$parts1 = explode(' ',$name1);
		$n1 = count($parts1);
		$parts2 = explode(' ',$name2);
		$n2 = count($parts2);
		if($n1 != $n2){
			$probability-=20;
			
			if($n1 > $n2){
				for($i=0;$i<$n1;$i++){
					if(!isset($parts2[$i])){$parts2[$i]='';}
					$probability+=self::nameComparisonAlgorithm($parts1[$i],$parts2[$i]);
				}
			}
			else{
				for($i=0;$i<$n2;$i++){
					if(!isset($parts1[$i])){$parts1[$i]='';}
					$probability+=self::nameComparisonAlgorithm($parts1[$i],$parts2[$i]);
				}
			}
		}
		else{
			for($i=0;$i<$n1;$i++){
				$probability+=self::nameComparisonAlgorithm($parts1[$i],$parts2[$i]);
			}
		}
		if($probability > 100){$probability = 100;}
		elseif($probability<0){$probability = 0;}
		$probability = round($probability);
		if($probability > 70){return true;}
		else{return false;}
	}
	
	private static function nameComparisonAlgorithm($name1,$name2){
		$map1 = charsMap($name1);
		$l1 = count($map1);
		$map2 = charsMap($name2);
		$l2 = count($map2);
		
		$probability = 0;
		
		$base = ($l1+$l2)/2;
		$samelenght = 25/$base; //for each char diff
		$match = 100/$base;  //for each matching char
		
		
		if($l1 > $l2+3 || $l1 < $l2-3){
			if($l1>$l2+3){$probability-=($l1-$l2)*$diff_len;}
			elseif($l1<$l2-3){$probability-=($l2-$l1)*$diff_len;}
		}
		else{
			$probability+=(($l1+$l2)/2)*$diff_len;
		}
		for($i=0;$i<$l1;$i++){
			if($map1[$i] == $map[$i]){
				$probability+=$match;
			}
			elseif(in_array($map1[$i],$map2)){
				$where = array_search($map1[$i],$map2);
				if($where < $i+3 && $where > $i-3){
					$probability += $match*0.9;//occurence is found less than 3 chars away from match
				}
			}
			else{
				$probability-=$match;
			}
		}
		return $probability;
		
	}
	
	public static function importCVS($file){
		$lines = explode(PHP_EOL,file_get_contents($file));
		$cards = self::parseVcards($lines);
		T::$body[] = print_r($cards,true);
		return;
		
		$people = explode('BEGIN:VCARD',$c);
		foreach($people as $person){
			$lines = explode(PHP_EOL,$person);
			$fields = array_pop($lines);
			foreach($fields as $field){
				if(preg_match('/[N:]*/',$field,$match)){
					$parts = explode(';',$field);
					$r[] = $parts[0];
					$r[] = $parts[1];
				}
				elseif(preg_match('/item[1-9]*/',$field,$match)){
					$parts = explode(';',$field);
					$r[] = $parts[0];
				}
			}
		}
	}
	
	public function directory(){
		$r = '';
		$m=500;
		$c = new Collection('Contact');
		$c->uid('=',App::$user->id,true);
		$r .= '<h1>You have '.$c->total(true).' contacts.</h1>';
		T::$page['title'] = App::$user->fullName('full').'\'s contacts';
		$r .= $c->returnTable($_REQUEST['s']*$m,$m,true,'firstname',false);
		T::$pagecount = ceil($c->total(true)/$m);
		return $r;
	}
}
?>