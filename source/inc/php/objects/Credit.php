<?php
class Credit extends Object{
	public $uid;
	public $objectType;
	public $objectId;
	public $who;
	public $name;
	public $role = 'other';
	//0=>no action, 1=> validated, -1=>disaproved
	public $approved;
		
	protected static function publicFunctions($key=NULL){
		return arrayGetKey($key,array(
			'preview'=>true,
		));
	}
	
	protected static function ownerFunctions($key=NULL){
		return arrayGetKey($key,array(
			'edit'=>true,
		));
	}
	
	public function descriptor($key=NULL){
		return arrayGetKey($key,array(
			'id'=>'int',
			'uid'=>'int',
			'objectType'=>'string',
			'objectId'=>'int',
			'who'=>'int',
			'name'=>'string',
			'role'=>'string',
			'approved'=>'int',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		));
	}
	
	public function validateData($n,$v){
		switch ($n){
			case 'id':
			case 'uid':
			case 'created':
			case 'modified':
			case 'deleted':
				return true;
			break;
			
			case 'role':
			case 'name':
			case 'who':
			case 'objectType':
			case 'objectId':
				$this->$n = $v;
				return true;
			break;
			
			case 'approved':
				$this->$n = intval($v);
				return true;
			break;
			
			default:
				Msg::notify($n.' no a valid field');
				return false;
			break;
		}
		return false;
	}
}
?>