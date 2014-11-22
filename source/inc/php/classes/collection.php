<?php
class Collection extends Query{
	public $type;
	public $objects;
	
	public function __construct($type){
		parent::__construct();
		$dummy = new $type;
		$this->select(array_keys($dummy->descriptor()));
		$this->from($type);
		$this->type = $type;
		return $this;
	}
	
	public function data(){
		$_data = array();
		if(empty($this->objects)){$this->load();}
		foreach($this->objects as $obj){
			$_data[] = $obj->data();
		}
		return $_data;
	}

	public function get($object=null){
		return $this->load();
	}
	
	public function load($class=NULL,$rows=NULL,$where=NULL,$limit=NULL){
		if(!empty($this->objects)){return $this->objects;}
		$this->objects = array();
		$data = parent::get();
		foreach($data as $d){
			$this->objects[] = new $this->type($d);
		}
		return $this->objects;
	}
}
?>