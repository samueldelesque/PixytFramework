<?php
class Product extends Object{
	public $title;
	public $description;
	public $file;
	public $settings=array();
	public $basePrice;
	public $type;
	public $duration;
	
	public static $tlds = array(
		array('tld'=>'com','price'=>990),
		array('tld'=>'me','price'=>1490),
		array('tld'=>'fr','price'=>1090),
		array('tld'=>'de','price'=>990),
		array('tld'=>'dk','price'=>1490),
		array('tld'=>'es','price'=>1090),
		array('tld'=>'co.uk','price'=>1800),
		
		array('tld'=>'eu','price'=>1090),
		array('tld'=>'info','price'=>990),
		array('tld'=>'net','price'=>990),
		array('tld'=>'org','price'=>990),
		
		array('tld'=>'ac','price'=>6000),
		array('tld'=>'af','price'=>6600),
		array('tld'=>'asia','price'=>1500),
		array('tld'=>'at','price'=>1500),
		array('tld'=>'be','price'=>990),
		array('tld'=>'biz','price'=>1500),
		array('tld'=>'bz','price'=>2400),
		array('tld'=>'ca','price'=>1800),
		array('tld'=>'cat','price'=>3000),
		array('tld'=>'cc','price'=>2400),
		array('tld'=>'cn','price'=>1090),
		array('tld'=>'ch','price'=>1200),
		array('tld'=>'co','price'=>3000),
		array('tld'=>'cz','price'=>1800),
		array('tld'=>'fi','price'=>2400),
		array('tld'=>'ie','price'=>6000),
		array('tld'=>'im','price'=>1800),
		array('tld'=>'in','price'=>990),
		array('tld'=>'io','price'=>6000),
		array('tld'=>'la','price'=>2400),
		array('tld'=>'lc','price'=>2400),
		array('tld'=>'li','price'=>1800),
		array('tld'=>'lt','price'=>2400),
		array('tld'=>'me.uk','price'=>1200),
		array('tld'=>'mobi','price'=>1800),
		array('tld'=>'name','price'=>1200),
		array('tld'=>'nu','price'=>3600),
		array('tld'=>'nl','price'=>990),
		array('tld'=>'pl','price'=>990),
		array('tld'=>'tv','price'=>3000),
		array('tld'=>'us','price'=>990),
		array('tld'=>'xxx','price'=>1800),
		array('tld'=>'yt','price'=>1200)
	);
	
	public static $plans = array(
		'pixyt.m.001' => array(
			'name'=>'Personal',
			'space'=>10737418240,
			'price'=>400,
			'billing'=>'monthly',
		),
		'pixyt.m.002' => array(
			'name'=>'Pro',
			'space'=>53687091200,
			'price'=>800,
			'billing'=>'monthly',
		),
		'pixyt.y.003' => array(
			'name'=>'Personal',
			'space'=>10737418240,
			'price'=>4400,
			'billing'=>'yearly',
		),
		'pixyt.m.004' => array(
			'name'=>'Pro',
			'space'=>53687091200,
			'price'=>8800,
			'billing'=>'yearly',
		),
	);
	
	public static $coupons = array(
		'PX13NOV'=>array(
			'percentoff'=>50,
			'amountoff'=>0,
			'duration'=>6,		//months
			'expire'=>'2013-11-30',
		),
	);
	
	public static function tldPrices($tld=NULL){
		$tlds = array(
			'com'=>990,
			'me'=>1490,
			'fr'=>1090,
			'de'=>990,
			'dk'=>1490,
			'es'=>1090,
			'co.uk'=>1800,
			
			'eu'=>1090,
			'info'=>990,
			'net'=>990,
			'org'=>990,
			
			'ac'=>6000,
			'af'=>6600,
			'asia'=>1500,
			'at'=>1500,
			'be'=>990,
			'biz'=>1500,
			'bz'=>2400,
			'ca'=>1800,
			'cat'=>3000,
			'cc'=>2400,
			'cn'=>1090,
			'ch'=>1200,
			'co'=>3000,
			'cz'=>1800,
			'fi'=>2400,
			'ie'=>6000,
			'im'=>1800,
			'in'=>990,
			'io'=>6000,
			'la'=>2400,
			'lc'=>2400,
			'li'=>1800,
			'lt'=>2400,
			'me.uk'=>1200,
			'mobi'=>1800,
			'name'=>1200,
			'nu'=>3600,
			'nl'=>990,
			'pl'=>990,
			'tv'=>3000,
			'us'=>990,
			'xxx'=>1800,
			'yt'=>1200
		);
		if(isset($tlds[$tld])){return $tlds[$tld];}
		elseif($tld!=NULL){return false;}
		else{return $tlds;}
	}
	/*
	public static $webspace = array(
		'Trial'=>	array('space'=>0,'price'=>0),
		'1GB'=>		array('space'=>1073741824,'price'=>1500),
		'2GB'=>		array('space'=>2147483648,'price'=>2340),
		'5GB'=>		array('space'=>5368709120,'price'=>4560),
		'20GB'=>	array('space'=>21474836480,'price'=>10680),
		'100GB'=>	array('space'=>107374182400,'price'=>33480),
		'500GB'=>	array('space'=>536870912000,'price'=>59880),
		'1TB'=>		array('space'=>1099511627776,'price'=>106800),
		'3TB'=>		array('space'=>3298534883328,'price'=>226800),
		'6TB'=>		array('space'=>6597079766656,'price'=>394800),
		'12TB'=>	array('space'=>13194139533312,'price'=>598800),
	);
	*/
	
	public static function packages($package=NULL){
		$packages = array(
			'personal' => array(
				'label'=>'Personal',
				'name'=>'personal',
				'space'=>2147483648,
				'price'=>290,
				'billing'=>'monthly',
				'options'=>array(
					'domain'=>true,
					'traffic'=>true,
					'drag_drop'=>true,
					'support'=>true,
					'customize'=>true,
					'custom_scripts'=>true,
				),
			),
			'professional' => array(
				'label'=>'Pro',
				'emphasized'=>true,
				'name'=>'professional',
				'space'=>5368709120,
				'price'=>590,
				'billing'=>'monthly',
				'options'=>array(
					'domain'=>true,
					'traffic'=>true,
					'drag_drop'=>true,
					'support'=>true,
					'customize'=>true,
					'custom_scripts'=>true,
					'no_branding'=>true,
					'design_assistance'=>true,
					'premium_support'=>true,
				),
			),
		);
		if(isset($packages[$package])){return $packages[$package];}
		elseif($package!=NULL){return false;}
		else{return $packages;}
	}
	
	public function descriptor(){
		return array(
			'id'=>'int',
			'uid'=>'int',
			'title'=>'string',
			'description'=>'string',
			'settings'=>'object',
			'basePrice'=>'int',
			'type'=>'string',
			'duration'=>'int',			//In seconds if applicable, else 0
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		);
	}
	
	protected static function publicFunctions(){
		return array(
			'fullView'=>true,
			'preview'=>true,
		);
	}
	
	protected static function ownerFunctions(){
		return array(
			'edit'=>true,
			'editPreview'=>true,
		);
	}
}
?>