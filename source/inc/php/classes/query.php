<?php
class Query extends Mysql{
	private $querystring;
	private $where = array();
	private $set = array();
	private $from;
	private $join;
	private $limit;
	private $group_by = array();
	private $order_by = array();
	private $desc;
	private $data;
	private $queryType = 'select';
	
	public function __construct($table=NULL,$username=NULL,$password=NULL,$host=NULL){
		parent::__construct();
		if(!defined('DB_STATUS')){
			die('Database not loaded, cannot make a query.');
		}
		return $this;
	}
	
	public function select($selector){
		if(is_object($selector)){
			$rows = '`'.implode('`,`',array_keys($object->descriptor())).'`';
		}
		elseif(is_array($selector)){
			$rows = '`'.implode('`,`',$selector).'`';
		}
		else{
			if($selector != '*'){$rows = '`'.$selector.'`';}
			else{$rows = $selector;}
		}
		$this->querystring = 'SELECT '.$rows;
		return $this;
	}
	
	public function update($table){
		$this->queryType = 'update';
		$this->querystring = 'UPDATE `'.$table.'`';
		return $this;
	}
	
	public function where($filter,$value=NULL){
		if(!is_array($filter)){$filter = array($filter=>$value);}
		$this->where = array_merge($filter,$this->where);
		return $this;
	}
	
	public function set($data,$value=NULL){
		if(!is_array($data)){$data = array($data=>$value);}
		$this->set = array_merge($data,$this->set);
		return $this;
	}
	
	public function from($table){
		$this->from = $this->_from($table);	
		return $this;
	}
	
	public function join($table,$on=NULL){
		$this->join .= $this->_join($table,$on);	
		return $this;
	}
	
	public function limit($limit,$start=NULL){
		if($start == NULL){$start = $_REQUEST['s']*$limit;}
		$this->limit = $this->_limit($limit,$start);
		return $this;
	}
	
	public function group_by($key){
		$this->group_by[] = $key;
		return $this;
	}
	
	public function order_by($key,$desc=false){
		$this->order_by[] = $key;
		$this->desc = $desc;
		return $this;
	}
	
	public function get($object=null){
		if(!$this->querystring){$this->error('Query String incomplete (missing SELECT/UPDATE...)');return;}
		switch($this->queryType){
			case 'select':
				$this->data = array();
				return $this->query($this->querystring.$this->from.$this->join.$this->_where($this->where,$this->data).$this->_group_by($this->group_by).$this->_order_by($this->order_by,$this->desc).$this->limit,$this->data);
			break;
			
			case 'update':
				return $this->query($this->querystring.$this->join.$this->_set($this->set).$this->_where($this->where,$this->data),$this->data);
			break;
		}
	}
	
	public function count($class=NULL,$where=NULL){
		return App::$db->count($this->type,$this->where);
	}
}
//$q = new Query(App::$db)
//$photo = $q->select('*')->from('Photo')->where('id',12)->limit(1)->get();
//$photo->display();

//$q = new Query(App::$db)
//$user = $q->select('*')->from('User')->where('IN',array(2,5,12))->limit(3)->load();
// array({"id":2,"name":"Some user"...})
?>