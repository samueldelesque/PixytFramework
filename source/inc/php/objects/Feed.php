<?php
class Feed extends Object{
	public $uid;
	public $objectType;
	public $objectId;
	public $seen;
	public $clicked;
	public $publish;
	public $importance;
		
	protected static function publicFunctions($key=NULL){
		return arrayGetKey($key,array(
			'preview'=>true,
			'smallFeed'=>true,
			'largeFeed'=>true,
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
			'seen'=>'int',
			'clicked'=>'string',
			'publish'=>'date',
			'importance'=>'int',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		));
	}
	
	public function validateData($n,$v){
		switch ($n){
			case 'id':
			case 'uid':
			case 'seen':
			case 'clicked':
			case 'created':
			case 'modified':
			case 'deleted':
				return true;
			break;
			
			case 'importance':
			case 'date':
			case 'objectType':
			case 'objectId':
				$this->$n = $v;
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