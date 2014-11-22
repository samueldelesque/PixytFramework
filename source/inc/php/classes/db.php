<?php
class Mysql extends Pixyt{
	protected $connection;
	protected $dbname;
	public static $queries = array();
	public static $connections = array();
	public static $autoDB;
	
	public function __construct($dbname=NULL,$username=NULL,$password=NULL,$host=NULL){
		parent::__construct();
		if(empty($dbname)){
			$this->dbname = self::$autoDB;
			$this->connection = self::$connections[self::$autoDB];
		}
		else{
			if(ERROR_LEVEL == 0)echo 'Connecting to '.$dbname.'...<br/>';
			try {
				$this->dbname = $dbname;
				$this->connection = new PDO('mysql:host='.$host.';dbname='.$dbname, $username, $password);
				$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				if(!defined('DB_STATUS')){
					define('DB_STATUS',true);
					self::$autoDB = $dbname;
				}
				self::$connections[$this->dbname] = $this->connection;
			}
			catch(PDOException $e) {
				$this->error($e->getMessage());
			}
		}
	}
	
	
	/*
	 * INSERT
	 * $insert should contain an object/array like {key=>value} where key is a valid column
	 * 
	 */
	public function insert($object){
		try {
			if(!is_object($object) || !isset($object->className) || !method_exists($object,'descriptor')){
				$this->error('Wrong object input for DB::insert()');
				return false;
			}
			$fields = array();
			$data = array();
			foreach($object->descriptor() as $name=>$type){
				if($name == 'id'){continue;}
				$fields[] = '`'.$name.'`';
				$values[] = ':'.$name;
				switch($type){
					case 'string':
						$data[':'.$name] = strval($object->$name);
					break;
					
					case 'int':
						$data[':'.$name] = intval($object->$name);
					break;
					
					case 'bool':
						$data[':'.$name] = boolval($object->$name);
					break;
					
					case 'date':
						$data[':'.$name] = date('Y-m-d',strtotime($object->$name));
					break;
					
					case 'object':
						$data[':'.$name] = json_encode($object->$name);
					break;
					
					case 'list':
						if(!is_array($object->$name)){$object->$name=array($object->$name);}
						$data[':'.$name] = implode(',',$object->$name);
					break;
					
					default:
						$this->error('DB::update(): VARTYPE not recognized: '.$type);
						return false;
					break;
				}
			}
			$cols = '('.implode(',',$fields).')';
			$vals = '('.implode(',',$values).')';
			
			$s = microtime(true);
			
			$query = $this->connection->prepare('INSERT INTO `'.$this->dbname.'`.`'.$object->className.'` '.$cols.' VALUES '.$vals);
			$query->execute($data);
			
			self::$queries[] = $query->queryString.' (took '.round((microtime(true) - $s)*1000,2).'ms)';
			

			if($query->rowCount() > 0){
				return $this->insertID();
			}
		}
		catch(PDOException $e) {
			$this->error($e->getMessage().' -- '.$query->queryString.' -- '.implode(', ',$data));
		}
		return false;
	}
	
	public function insertID(){
		self::$queries[] = 'INSERT ID';
		return $this->connection->lastInsertId();
	}
	
	/*
	 * UPDATE
	 * $update like {key=>val}
	 *
	 */
	
	public function update($object){
		try {
			if(!is_object($object) || !isset($object->id) || !method_exists($object,'descriptor')){
				$this->error('Wrong object input for DB::update()');
				return false;
			}
			$fields = array();
			$data = array();
			foreach($object->descriptor() as $name=>$type){
				$fields[] = '`'.$name.'` = :'.$name;
				switch($type){
					case 'string':
						$data[':'.$name] = strval($object->$name);
					break;
					
					case 'int':
						$data[':'.$name] = intval($object->$name);
					break;
					
					case 'bool':
						$data[':'.$name] = boolval($object->$name);
					break;
					
					case 'date':
						$data[':'.$name] = date('Y-m-d',strtotime($object->$name));
					break;
					
					case 'object':
						$data[':'.$name] = json_encode($object->$name);
					break;
					
					case 'list':
						if(!is_array($object->$name)){$object->$name=array($object->$name);}
						$data[':'.$name] = implode(',',$object->$name);
					break;
					
					default:
						$this->error('DB::update(): VARTYPE not recognized: '.$type);
						return false;
					break;
				}
			}
			$cols = implode(',',$fields);
			$data[':id'] = $object->id;
			
			$s = microtime(true);
			
			$query = $this->connection->prepare('UPDATE `'.$this->dbname.'`.`'.$object->className.'` SET '.$cols.' WHERE `id` = :id');
			$query->execute($data);
			
			self::$queries[] = $query->queryString.' (took '.round((microtime(true) - $s)*1000,2).'ms)';
			
			if($query->rowCount() > 0){
				return true;
			}
		}
		catch(PDOException $e) {
			$this->error($e->getMessage().' -- '.$query->queryString.' -- '.implode(', ',$data));
		}
		return false;
	}
	
	
	/*
	 * DELETE
	 *
	 */
	
	public function delete($object){
		$object->deleted = time();
		return $this->update($object);
		
		/*
		
		try {
			if(!is_object($object) || !isset($object->id)){
				$this->error('Wrong object input for DB::delete()');
				return false;
			}
			$s = microtime(true);
			$query = $this->connection->prepare('DELETE FROM `'.$this->dbname.'`.`'.$object->className.'` WHERE `id` = :id');
			$query->execute(array(':id'=>$object->id));
			
			self::$queries[] = $query->queryString.' (took '.round((microtime(true) - $s)*1000,2).'ms)';
			
			if($query->rowCount() > 0){
				return true;
			}
		}
		catch(PDOException $e) {
			$this->error($e->getMessage().' -- '.$query->queryString.' -- '.implode(', ',$data));
		}
		return false;
		*/
	}
	
	/*
	 * QUERY
	 * $query: custom query string
	 * $data: associated data
	 */
	
	public function query($query,$data=array(),$all=true){
		try {
			$s = microtime(true);
			$query = $this->connection->prepare($query);
			$query->execute($data);
			self::$queries[] = $query->queryString.' (took '.round((microtime(true) - $s)*1000,2).'ms)';
			if($all===true)return $query->fetchAll(PDO::FETCH_OBJ);
			else return $query->fetchObject();
		}
		catch(PDOException $e) {
			$this->error($e->getMessage().' -- '.$query->queryString.' -- '.implode(', ',$data));
		}
		return false;
	}
	
	protected function _from($from){
		if(!empty($from)){
			return ' FROM `'.$this->dbname.'`.`'.$from.'`';
		}
		return '';
	}
	
	protected function _set($set){
		if(!is_array($set) && !is_object($set)){$set = array();}
		$s = '';
		foreach($set as $k=>$v){
			$s .= '`'.$k.'` = "'.$this->connection->quote($v).'"';
		}
		return 'SET '.$s;
	}
	
	protected function _join($join,$on=NULL){
		if(!empty($join)){
			if(!empty($on)){$on = ' ON '.$on;}
			return ' JOIN `'.$this->dbname.'`.`'.$join.'`'.$on;
		}
		return '';
	}
	
	private function setWhereKey($k){
		$k_parts = explode(' ',$k);
		if(count($k_parts)>1 && in_array(end($k_parts),array('<=','<','>','>=','=','!=','LIKE','IN'))){$sign = end($k_parts);}
		else{$sign='=';}
		return array('name'=>reset($k_parts),'sign'=>$sign);
	}
	
	private function setWhereValue($where,$v){
		if(is_array($v)){
			//WHERE IN Array type
			return array('filter'=>$where.' ('.implode(',',$v).')');
		}
		return array('filter'=>$where.' ?','data'=>strval($v));
	}
	
	protected function _where($where,&$data=array()){
		if(!is_array($where)){
			die('DB::where expects an array.');
		}
		if(!empty($where)){
			foreach($where as $k=>$v){
				$key = $this->setWhereKey($k);
				if(preg_match('/^{LOWER|UPPER}/',$key['name'])){
					$where_value = $key['name'];
				}
				else{
					$where_value = '`'.$key['name'].'` ';
				}
				$w = $this->setWhereValue($where_value.$key['sign'],$v);
				$filters[] = $w['filter'];
				if(isset($w['data']))$data[] = $w['data'];
			}
			$filters = implode(' AND ',$filters);
			return ' WHERE '.$filters;
		}
		return '';
	}
	
	protected function _group_by($group){
		if(!is_array($group)){
			die('DB::_group_by expects an array.');
		}
		if(!empty($group)){
			$group_by = implode(',',$group);
			return ' GROUP BY `'.$group_by.'`';
		}
		return '';
	}
	
	protected function _order_by($group,$desc=false){
		if(!is_array($group)){
			die('DB::_order_by expects an array.');
		}
		if(!empty($group)){
			$group_by = implode('`,`',$group);
			$mode = ($desc)?' DESC':' ASC';
			return ' ORDER BY `'.$group_by.'`'.$mode;
		}
		return '';
	}
	
	protected function _limit($limit,$start=0){
		$parts = explode(',',$limit);
		if(count($parts) == 2){return ' LIMIT '.$limit;}
		if(!empty($limit) && intval($limit) > 0){
			return ' LIMIT '.intval($start).','.intval($limit);
		}
		return '';
	}
	
	/*
	 * GET specific, LOAD array
	 * load a specific object
	 */
	 
	private function formatResult($result,$descriptor){
		if(!is_object($result)){return $result;}
		foreach($descriptor as $k=>$t){
			switch($t){
				case 'int':
					$result->$k = intval($result->$k);
				break;
				
				case 'object':
					$result->$k = (object)json_decode($result->$k);
				break;
				
				case 'list':
					$result->$k = explode(',',$result->$k);
				break;
				
				case 'string':
					$result->$k = strval($result->$k);
				break;
				
				case 'bool':
					$result->$k = boolval($result->$k);
				break;
				
				default:
					$result->$k = $result->$k;
				break;
			}
		}
		return $result;
	}

	public function get($object){
		try {
			if(!is_object($object)){
				$this->error('Wrong object input for DB::get()');
				return false;
			}
			$s = microtime(true);
			
			$rows = '`'.implode('`,`',array_keys($object->descriptor())).'`';
			$query = $this->connection->prepare('SELECT '.$rows.' FROM `'.$this->dbname.'`.`'.$object->className.'` WHERE `id` = :id');
			$query->execute(array('id'=>$object->id));
			self::$queries[] = $query->queryString.' (took '.round((microtime(true) - $s)*1000,2).'ms)';
			$obj = $query->fetchObject();
			if(ERROR_LEVEL == 0){print_r($obj);}
			return $this->formatResult($obj,$object->descriptor());
		}
		catch(PDOException $e){
			$this->error($e->getMessage().' -- '.$query->queryString);
		}
		return false;
	}
	
	private function load($class,$rows,$where=NULL,$limit=NULL){
		try {
			$rows = '`'.implode('`,`',$rows).'`';
			$filters = array();
			$data = array();
			
			$s = microtime(true);
		
			$query = $this->connection->prepare('SELECT '.$rows.$this->_from($class).$this->_where($where,$data).$this->_limit($limit));
			
			$query->execute($data);
			
			self::$queries[] = $query->queryString.' (took '.round((microtime(true) - $s)*1000,2).'ms)';
			
			$r = array();
			$data = $query->fetchAll(PDO::FETCH_OBJ);
			$dummy = new $class();
			$descriptor = $dummy->descriptor();
			foreach($data as $obj){
				$r[] = new $class($this->formatResult($obj,$descriptor));
				if(ERROR_LEVEL == 0){print_r($obj);}
			}
			return $r;
		}
		catch(PDOException $e) {
			$this->error($e->getMessage().' -- '.$query->queryString.' -- '.implode(', ',$data));
		}
		return false;
	}
	
	public function increment($class,$id,$field,$value){
		try {
			$s = microtime(true);
			$query = $this->connection->prepare('UPDATE `'.$class.'` SET `'.$class.'`.`'.$field.'` = `'.$class.'`.`'.$field.'` + :increment WHERE `id` = :id');
			$query->execute(array('increment'=>$value,'id'=>$id));
			
			self::$queries[] = $query->queryString.' (took '.round((microtime(true) - $s)*1000,2).'ms)';
			
			return true;
		}
		catch(PDOException $e) {
			$this->error($e->getMessage().' -- '.$query->queryString.' -- '.implode(', ',$data));
		}
		return false;
	}
	
	public function deleteAll($class,$where,$limit=NULL){
		return;
		/*
		try {
			$filters = array();
			$data = array();
			foreach($where as $k=>$v){
				$filters[] = $k.' = :'.$k;
				$data[':'.$k] = $v;
			}
			$filters = implode(' AND ',$filters);
			
			if($limit != NULL){
				$limit = ' LIMIT '.$limit;
			}
			$s = microtime(true);
			$query = $this->connection->prepare('DELETE FROM `'.$this->dbname.'`.`'.$class.'` WHERE '.$filters.$limit);
			$query->execute($data);
			
			self::$queries[] = $query->queryString.' (took '.round((microtime(true) - $s)*1000,2).'ms)';
			
			return true;
		}
		catch(PDOException $e) {
			$this->error($e->getMessage().' -- '.$query->queryString.' -- '.implode(', ',$data));
		}
		return false;
		*/
	}
	
	public function count($class,$where){
		try {
			$count = ' COUNT(*) AS `count`';
			$data = array();
			$query = $this->connection->prepare('SELECT'.$count.$this->_from($class).$this->_where($where,$data));
			if(ERROR_LEVEL==0)echo $query->queryString.'<br/>';
			$query->execute($data);
			
			self::$queries[] = $query->queryString;
			
			$result = $query->fetch(PDO::FETCH_OBJ);
			return $result->count;
		}
		catch(PDOException $e) {
			$this->error($e->getMessage().' -- '.$query->queryString.' -- '.implode(', ',$data));
		}
		return false;
	}
	
	public function sum($field,$class,$where){
		try {
			$sum = ' SUM(`'.$field.'`) AS `sum`';
			$data = array();
			$query = $this->connection->prepare('SELECT'.$sum.$this->_from($class).$this->_where($where,$data));
			if(ERROR_LEVEL==0)echo $query->queryString.'<br/>';
			$query->execute($data);
			
			self::$queries[] = $query->queryString;
			
			$result = $query->fetch(PDO::FETCH_OBJ);
			return $result->sum;
		}
		catch(PDOException $e) {
			$this->error($e->getMessage().' -- '.$query->queryString.' -- '.implode(', ',$data));
		}
		return false;
	}
	
	public function exists($class,$where){
		try {
			$rows = '`id`';
			$data = array();
			$s = microtime(true);
			
			$query = $this->connection->prepare('SELECT '.$rows.$this->_from($class).$this->_where($where,$data));
			$query->execute($data);
			
			self::$queries[] = $query->queryString.' (took '.round((microtime(true) - $s)*1000,2).'ms)';
			
			$result = $query->fetchObject();
			if(is_object($result)){
				return $result->id;
			}
		}
		catch(PDOException $e) {
			$this->error($e->getMessage().' -- '.$query->queryString.' -- '.implode(', ',$data));
		}
		return false;
	}
	
	// Prepares a SELECT SQL statement in PDO way. Used for advanced queries
	public function query_prepare($query)
	{
		return $this->connection->prepare($query);
	}
}
?>