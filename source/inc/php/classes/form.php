<?php
class Form extends Pixyt{
	public $head;
	public $foot;
	public $namePrefix;
	public $idPrefix;
	public $objectType;
	public $objectId=0;
	public $actionType;
	public $curId;
	public $id;
	public $o = false;
	private $clearTemp=false;
	private $data;
	private $fieldsetOpen = false;
	
	function __construct($objectType='',$objectId='',$url='',$params=array()){
		if(!is_array($params)){$params=array();}
		if(!isset($params['class'])){$params['class']='std';}
		if(!isset($params['ajax'])){$params['ajax']=false;}
		if(!isset($params['insert'])){$params['insert']=false;}
		if(!isset($params['onsubmit'])){$params['onsubmit']='';}
		if(!isset($params['onreset'])){$params['onreset']='';}
		if(!isset($params['data'])){$params['data']=array();}
		$this->objectType=$objectType;
		if(!empty($this->objectType) && class_exists($this->objectType)){
			if(empty($objectId)){
				$this->namePrefix='insert['.$objectType.']';
			    $this->actionType = 'insert';
				$this->idPrefix='insert_'.$objectType;
				$this->id = 'form_'.$objectType;
			}
			else{
				$this->namePrefix='update['.$objectType.']['.$objectId.']';
				$this->actionType = 'update';
				//force insert class for complex objects like sites
				if($params['insert']){$this->actionType='insert';}
				$this->objectId = $objectId;
				$this->idPrefix='update_'.$objectType.'_'.$objectId;
				$this->id = 'form_'.$objectType.'_'.$objectId;
				$this->o = new $objectType($objectId);
			}
		}
		elseif($this->objectType == 'cartItem'){
			$this->actionType = 'buy';
			$this->namePrefix = 'buy['.$objectId.']';
			$this->idPrefix = 'buy_'.$objectId;
			$this->id = 'form_cartItem_'.$objectId;
		}
		else{
			$this->actionType = 'glob';
			$this->namePrefix = 'glob';
			$this->idPrefix = 'glob';
			$this->id = 'form_general_'.randStr();
		}
		if($params['ajax']===true){
			$action = lnk('',$url,'',array('data-type'=>'form'),true);
			$params['onsubmit'] = 'submitForm(this);return false;';
		}
		else{
			if(empty($url)){$url=true;}
			$action = lnk('',$url,'',array(),true);
		}
		$data=array();
		$params['data']['nonce'] = md5($_SESSION['uid'].SITE_KEY);
		foreach($params['data'] as $n=>$v){$data[]='<input type="hidden" name="'.$n.'" value="'.$v.'"/>';}
		$this->head = '<form action="'.$action.'" data-actiontype="'.$this->actionType.'" class="'.$params['class'].'" onsubmit="'.$params['onsubmit'].'" method="post" onreset="'.$params['onreset'].'" enctype="multipart/form-data" id="'.$this->id.'">'.implode('',$data);
		$this->foot='</form>';
		return $this->head;
	}
	
	function head(){
		return $this->head;
	}
	
	function foot(){
		return $this->foot;
	}
	
	public function content(){
		$form = $this->head();
		foreach($this->data as $field){
			$form .= $field;
		}
		$form .= $this->foot();
		return $form;
	}
	
	private function mkname($name){
		if(!is_array($name)){$name = explode('_',$name);}
		$n='';
		foreach($name as $level){$n .= '['.$level.']';}
		return $this->namePrefix.$n;
	}
	
	/* FORM FIELDS */
	
	function input($name,$params=array()){
		$attributes = '';
		if(!isset($params['auto']) || $params['auto'] != false){
			$params['name'] = $this->mkname($name);
		}
		else{
			$params['name'] = $name;
		}
		if(!isset($params['type'])){$params['type'] = 'text';}
		$allowed = array('name','type','value','onclick','onchange','id','class','checked','placeholder');
		if(isset($_REQUEST[$params['name']])){$params['value'] = $_REQUEST[$params['name']];}
		foreach($allowed as $attr){
			if(isset($params[$attr])){$attributes .= ' '.$attr.'="'.htmlspecialchars($params[$attr]).'"';}
		}
		return $this->data[] = '<input'.$attributes.'/>';
	}
	
	function textarea($name,$params=array()){
		$attributes = '';
		if(!isset($params['auto']) || $params['auto'] != false){
			$params['name'] = $this->mkname($name);
		}
		else{
			$params['name'] = $name;
		}
		$allowed = array('name','onclick','onchange','id','class','placeholder');
		if(isset($_REQUEST[$params['name']])){$params['value'] = $_REQUEST[$params['name']];}
		foreach($allowed as $attr){
			if(isset($params[$attr])){$attributes .= ' '.$attr.'="'.htmlspecialchars($params[$attr]).'"';}
		}
		if(!isset($params['value'])){$params['value'] = '';}
		return $this->data[] = '<textarea'.$attributes.'>'.$params['value'].'</textarea>';
	}
	
	function onOff($name,$params=array()){
		if(!isset($params['auto']) || $params['auto'] != false){
			$params['name'] = $this->mkname($name);
		}
		else{
			$params['name'] = $name;
		}
		
		if(!isset($params['value'])){$params['value']=true;}
		
		if($params['value'] === true){$checked = 'checked';}else{$checked='';}
		$id = randStr();
		return dv('onoffswitch').'<input type="checkbox" name="'.$params['name'].'" class="onoffswitch-checkbox" id="'.$id.'" '.$checked.'><label class="onoffswitch-label" for="'.$id.'">'.dv('onoffswitch-inner').xdv().dv('onoffswitch-switch').xdv().'</label>'.xdv();
	}
	
	function free($content){
		return $this->data[] = $content;
	}
	
	function button($content,$params=array()){
		$attributes = '';
		$allowed = array('name','type','onclick','id','class');
		if(!isset($params['type'])){$params['type'] = 'submit';}
		foreach($allowed as $attr){
			if(isset($params[$attr])){$attributes .= ' '.$attr.'="'.htmlspecialchars($params[$attr]).'"';}
		}
		return $this->data[] = '<button'.$attributes.'>'.$content.'</button>';
	}
	
	function label($name,$params=array()){
		$attributes = '';
		$allowed = array('for','id','class');
		foreach($allowed as $attr){
			if(isset($params[$attr])){$attributes .= ' '.$attr.'="'.htmlspecialchars($params[$attr]).'"';}
		}
		return $this->data[] = '<label'.$attributes.'>'.$name.'</label>';
	}
	
	function fieldset($params=array()){
		if($this->fieldsetOpen){
			$this->fieldsetOpen = false;
			return $this->data[] = '</fieldset>';
		}
		else{
			$attributes = '';
			$this->fieldsetOpen = true;
			$allowed = array('id','class');
			foreach($allowed as $attr){
				if(isset($params[$attr])){$attributes .= ' '.$attr.'="'.htmlspecialchars($params[$attr]).'"';}
			}
			return $this->data[] = '<fieldset'.$attributes.'>';
		}
	}
	
	function select($name,$params=array()){
		if(!isset($params['auto']) || $params['auto'] != false){
			$params['name'] = $this->mkname($name);
		}
		else{
			$params['name'] = $name;
		}
		if(!isset($params['options'])){return false;}
		$opt=array();
		$option_attr = array('disabled','label','select','value');
		foreach($params['options'] as $option){
			$attributes = '';
			if(!is_array($option)){$option = array('value'=>$option);}
			foreach($option as $k=>$v){
				$attributes .= ' '.$k.'="'.htmlspecialchars($v).'"';
			}
			if(!isset($option['value'])){$option['value'] = '';}
			if(!isset($option['label'])){$option['label'] = $option['value'];}
			$opt[] = '<option'.$attributes.'>'.$option['label'].'</option>';
		}
		$attributes = '';
		$allowed = array('id','class','name','required','mutiple','onchange');
		foreach($allowed as $attr){
			if(isset($params[$attr])){$attributes .= ' '.$attr.'="'.htmlspecialchars($params[$attr]).'"';}
		}
		return $this->data[] = '<select'.$attributes.'>'.implode('',$opt).'</select>';
	}
	
	function date($name,$params=array()){
		$r = '';
		if(!isset($params['auto']) || $params['auto'] != false){
			$params['name'] = $this->mkname($name);
		}
		else{
			$params['name'] = $name;
		}
		if(!isset($params['format']) || !is_array($params['format'])){$params['format'] = array('d','m','y');}
		if(!isset($params['value'])){
			$params['value'] = array('d'=>date('d'),'m'=>date('m'),'y'=>date('y'));
		}
		if(!isset($params['class'])){$params['class'] = 'date';}
		elseif(!is_array($params['value'])){
			$time = strtotime($params['value']);
			$params['value'] = array('d'=>date('d',$time),'m'=>date('m',$time),'y'=>date('y',$time));
		}
		foreach($params['format'] as $select){
			switch($select){
				case 'd':
				case 'day':
					$options=array();
			    	$options[] = array('label'=>t('Day:'),'value'=>-1,'selected'=>true);
					for($i=1;$i<=31;$i++){
						$o = array('label'=>$i,'value'=>sprintf('%02d',$i));
						if($params['value'][$select] == $i){$o['selected'] = true;}
						$options[] = $o;
						
					}
					$r .= $this->select($params['name'].'[day]',array('options'=>$options,'auto'=>false,'class'=>$params['class'].' day'));
				break;
				
				case 'm':
				case 'month':
					$options=array();
			    	$options[] = array('label'=>t('Month:'),'value'=>-1,'selected'=>true);
			    	$months = array('01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April', '05'=>'May', '06'=>'June', '07'=>'July', '08'=>'August', '09'=>'September', '10'=>'October', '11'=>'November', '12'=>'December');
					foreach($months as $value=>$label){
						$o = array('value'=>$value,'label'=>t($label));
						if($params['value'][$select] == $i){$o['selected'] = true;}
						$options[] = $o;
					}
					$r .= $this->select($params['name'].'[month]',array('options'=>$options,'auto'=>false,'class'=>$params['class'].' month'));
				break;
				
				case 'y':
				case 'year':
					$options=array();
			    	$options[] = array('label'=>t('Year:'),'value'=>-1,'selected'=>true);
			    	$y = (int)date('Y');
					$s = $y-90;
					for($i=$y;$i>$s;$i--){
						$o = array('value'=>$i,'label'=>$i);
						if($params['value'][$select] == $i){$o['selected'] = true;}
						$options[] = $o;
					}
					$r .= $this->select($params['name'].'[year]',array('options'=>$options,'auto'=>false,'class'=>$params['class'].' year'));
				break;
			}
		}
		return $this->data[] = $r;
	}
}
?>