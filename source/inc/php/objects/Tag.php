<?php
class Tag extends Object{
	public $uid;
	public $objectType;
	public $objectId;
	public $tag;
	public $action;
		
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
			'tag'=>'string',
			'action'=>'string',
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
			
			case 'tag':
				if(strlen($v) > 255){Msg::notify('Tag too long!');return false;}
				$this->$n = $v;
				return true;
			break;
			
			case 'action':
				if(in_array($v,array('created','liked','commented'))){
					$this->$n=$v;
					return true;
				}
				else{
					$this->error('Unknown action '.$v.'!');
					Msg::notify('An error occured! (invalid action on tag)');
				}
			break;
			
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