<?php
class Stat extends Object{
	public $uid;
	public $ip;
	public $session;
	public $sessionstart;
	public $isbot;
	public $isunique;
	public $host;
	public $url;
	public $browser;
	public $platform;
	public $screen;
	public $window;
	public $from;
	public $language;
	public $device;
	public $age;
	public $hour;
	public $day;
	
	public function descriptor(){
		return array (
			'id'=>'int',
			'uid'=>'int',
			'ip'=>'string',
			'session'=>'string',
			'sessionstart'=>'int',
			'isbot'=>'bool',
			'isunique'=>'bool',
			'host'=>'string',
			'url'=>'string',
			'device'=>'string',
			'browser'=>'string',
			'platform'=>'string',
			'screen'=>'string',
			'window'=>'string',
			'from'=>'string',
			'language'=>'string',
			'age'=>'int',
			'hour'=>'int',
			'day'=>'date',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		);
	}
	
	function insert($force=false){
		$this->created = time();
		$stat = new Mysql(DB_STAT_NAME,DB_STAT_USER,DB_STAT_PASSWORD,DB_STAT_SERVER);
		/*
		if(HOST=='localhost'){
			$stat = new Mysql('stats','root','','localhost');
		}
		elseif(HOST=='dev.pixyt.com'){
			$stat = new Mysql('dev_stats','dev_stats','aoey937uf3a','localhost');
		}
		else{
			$stat = new Mysql('stats','pixyt','836yfgia378','localhost');
		}
		*/
		return $stat->insert($this);
	}
}
?>