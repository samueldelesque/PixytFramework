<?php
class Admin extends Interfaces{
	public function construct(){
		if(App::$user->access>40){
			Msg::addMsg(401,0,Msg::CRITICAL);
			return false;
		}
		return true;
	}
	
	public function increase($uid){
		if(App::$user->access > 40){die('You may not do that');}
		if(!Object::objectExists('User',$uid)){
			Msg::addMsg('User was not found!');
			return;
		}
		$u = new User($uid);
		if(isset($_REQUEST['allocation'])){$add = intval($_REQUEST['allocation']);}else{$add=0;}
		$u->allocation += $add;
		$u->update(true);
		Msg::addMsg($u->fullName().' was granted an extra '.prettyBytes($add));
	}
	
	public function accounting(){
		T::title('Accounting');
		$r = dv('head').lnk(translate('Add payment'),'transaction/create',array(),array('class'=>'btn','data-type'=>'popup')).xdv();
		for($i=1;$i<=12;$i++){
			$col = new Collection('Transaction');
			$col->where('MONTH(date) = '.$i);
			$col->where('YEAR(date) = '.date('Y'));
			$col->load(0,100);
			if($col->count() > 0){
				$sample = reset($col->results);
				T::$body[] = dv('contentBox').'<h2>'.date('m.Y',strtotime($sample->date)).'</h2>'.$col->returnTable(0,999,true,'date',false).xdv();
			}
		}
		$r .= dv('splitRight').dv('contentBox').Transaction::grandTotal().xdv().xdv();
		return $r;
	}
	
	public function repairSites(){
		$r = '';
		$col = new Collection('Site');
		$col->load(0,10000);
		foreach($col->results as $site){
			$r .= '<h3>'.$site->url.'</h3>';
			foreach($site->content as $id=>$page){
				if(!isset($page['t']) || strlen($page['t']) <= 1){unset($site->data['content'][$id]);}
				else{
					if(!isset($page['u']) || preg_match('/^(title)?((page-)?([0-9]{1,4})?)?$/',$page['u']) || is_numeric($page['u'])){
						$page['u'] = normalize($page['t']);
						$r .= $page['t'].' has no u<br/>';
					}
					if(!isset($page['d'])){
						$page['d'] = '';
						$r .= $page['t'].' has no u<br/>';
					}
					if(!isset($page['x'])){
						$page['x'] = 2;
						$r .= $page['t'].' has no x<br/>';
					}
				}
				$site->savePage($page['u'],$page);
			}
			$site->update(true);
		}
		return $r;
	}
	
	public function fixphotos(){
		$col = new Collection('Photo');
		$col->load(0,100);
		$table = new Table();
		foreach($col->results as $p){
			
		}
		return $table->returnContent();
	}
	
	public function directory(){
		$r = '';
		$period = 30;
		//T::$jsincludes[] = 'highcharts.js';
		//T::$jsincludes[] = 'admin-charts.js';
		T::$page['js'][] = '/-/js/jquery.sparkline.min.js';
		$r .= dv('d1200 center');
		
		$r .= dv('900 col');
		$r .= '<h2>Network activity</h2>';
		
		$now = time();
		$selector = $now - (86400*$period);
		$photoUploads = array();
		$userCreations = array();
		$siteCreations = array();
		$salesCreations = array();
		$orderTotal = array();
		$income = 0;
		while($selector < $now){
			$total = 0;
			$q = 'SELECT COUNT(id) AS `total` FROM `Photo` WHERE created BETWEEN '.intval($selector-86400).' AND '.intval($selector);
			if($d = App::$db->query($q)){
				$day = reset($d);
				$total = intval($day->total);
			}
			$photoUploads[] = $total;
			
			$total = 0;
			$q = 'SELECT COUNT(id) AS `total` FROM `User` WHERE created BETWEEN '.intval($selector-86400).' AND '.intval($selector);
			if($d = App::$db->query($q)){
				$day = reset($d);
				$total = intval($day->total);
			}
			$userCreations[] = $total;
			
			$total = 0;
			$q = 'SELECT COUNT(id) AS `total` FROM `Site` WHERE created BETWEEN '.intval($selector-86400).' AND '.intval($selector);
			if($d = App::$db->query($q)){
				$day = reset($d);
				$total = intval($day->total);
			}
			$siteCreations[] = $total;
			
			
			$total = 0;
			$q = 'SELECT COUNT(id) AS `total` FROM `Sale` WHERE created BETWEEN '.intval($selector-86400).' AND '.intval($selector);
			if($d = App::$db->query($q)){
				$day = reset($d);
				$total = intval($day->total);
			}
			$salesCreations[] = $total;
			
			$total = 0;
			$q = 'SELECT SUM(amt) AS `total` FROM `Order` WHERE created BETWEEN '.intval($selector-86400).' AND '.intval($selector);
			if($d = App::$db->query($q)){
				$day = reset($d);
				$total = intval($day->total);
			}
			$orderTotal[] = $total/100;
			$income += $total;
			
			$labels[] = date('d/m',$selector);
			$selector += 86400;
		}
		
		
		$r .= dv('chart').'<h3>Visitors</h3>';
		$r .= dv('graph');
		//$r .= graph(array(100,90,120,140,190,170),array('width'=>600,'type'=>'line','height'=>300));
		foreach ($labels as $label) {
			$r .= '<span class="label">'.$label.'</span>';
		}
		$r .= xdv();
		$r .= xdv();
		
		
		$r .= dv('chart').'<h3>Photo uploads</h3>';
		$r .= dv('graph');
		$r .= littlegraph($photoUploads,array('width'=>900,'barWidth'=>20,'height'=>120));
		foreach ($labels as $label) {
			$r .= '<span class="label">'.$label.'</span>';
		}
		$r .= xdv();
		$r .= xdv();
		
		
		$r .= dv('chart').'<h3>User signups</h3>';
		$r .= dv('graph');
		$r .= littlegraph($userCreations,array('width'=>900,'barWidth'=>20,'height'=>120));
		foreach ($labels as $label) {
			$r .= '<span class="label">'.$label.'</span>';
		}
		$r .= xdv();
		$r .= xdv();
		
		
		$r .= dv('chart').'<h3>Site creations</h3>';
		$r .= dv('graph');
		$r .= littlegraph($siteCreations,array('width'=>900,'barWidth'=>20,'height'=>120));
		foreach ($labels as $label) {
			$r .= '<span class="label">'.$label.'</span>';
		}
		$r .= xdv();
		$r .= xdv();
		
		$r .= '<h2>Sales</h2>';
		
		$r .= dv('chart').'<h3>Sales</h3>';
		$r .= dv('graph');
		$r .= littlegraph($salesCreations,array('width'=>900,'barWidth'=>20,'height'=>120));
		foreach ($labels as $label) {
			$r .= '<span class="label">'.$label.'</span>';
		}
		$r .= xdv();
		$r .= xdv();
		
		
		$r .= dv('chart').'<h3>Income (total: '.price2str($income).')</h3>';
		$r .= dv('graph');
		$r .= littlegraph($orderTotal,array('width'=>900,'barWidth'=>20,'height'=>120));
		foreach ($labels as $label) {
			$r .= '<span class="label">'.$label.'</span>';
		}
		$r .= xdv();
		$r .= xdv();
		
		
		$r .= xdv();
		
		$r .= xdv();
		return $r;
	}
	
	public function help(){
		$r = '';
		$r .= dv('d2 center');
		$r .= '<h2>Add a question</h2>';
		$form = new Form('Question');
		$form->question('input');
		$form->answer('textarea');
		$form->{'add'}('submit');
		$r .= $form->returnContent();
		$r .= '<h2>Questions asked by users:</h2>';
		$col = new Collection('Question');
		$col->load(0,999);
		foreach($col->results as $q){
			$r .= dv('padded').$q->display('answerQuestion').xdv();
		}
		$r .= xdv();
		return $r;
	}
	
	
	public function createsite(){
		$form = new Form('Site');
		$form->uid('input');
		$form->title('input');
		$form->url('input');
		$form->create('submit');
		return dv('padder').$form->returnContent().xdv();
	}
	
	public function users(){
		T::$page['title'] = translate('Users');
		$col = new Collection('User');
		if(isset($_REQUEST['orderby'])){$orderBy = $_REQUEST['orderby'];}else{$orderBy='modified';}
		return $col->returnTable(0,10000,true,$orderBy);
	}
	
	public function online(){
		T::$page['title'] = translate('Online users');
		$col = new Collection('User');
		$col->modified('>',time()-900);
		return '<h1>'.$col->total(true).' users online:</h1>'.$col->content('preview',0,99,true);
	}
	
	public function monitor(){
		T::$page['title'] = translate('Pixyt summary');
		$r = '';
		if(!IS_AJAX){
			$r .= dv('page-inner');
			$r .= dv('d1000 center');
		}
		foreach(Object::$objects as $idname=>$classname){
			$o = ucfirst($classname);
			$col = new Collection($o);
			$object = new $o();
			$number = $col->total();
			$r .= dv('monitor centerText',$o);
			$r .= '<h4 id="'.$o.'_counter">'.$number.'</h4>';
			$r .= '<p>'.$object->classname(true).'</p>';
			$r .= xdv();
			res('script','$("#'.$o.'_counter").html("'.$number.'");');
		}
		if(!IS_AJAX){
			$r .= xdv();
			$r .= xdv();
			T::$jsfoot[] = 'setInterval("keepConnection(\'admin/monitor\')",3000);';
			return $r;
		}
	}
	
	function sample(){
		$r = '';
		//display random samples of photos exif
		//for($i=0;$i<50;$i++){
			//$id = rand(0,8000);
			//while(!Object::objectExists('Photo',$id)){$id=rand(0,9000);}
			$id = 90;
			$p = new Photo($id);
			$p->saveExif();
			$p->saveColors();
			print_r($p->data);
//			$r .= dv('padded').$p->exif().xdv();
		//}
		die();
		return $r;
	}
	
	function stats(){
		T::$page['title'] = translate('Statistics');
		T::$jsincludes[] = 'highcharts.js';
		$r = '';
		$data = Process::$stats->evolution('pixyt.com',14);
		T::$jsfoot[] = '
(function($){
	$(function () {
	var chart1;
	$(document).ready(function() {
	  chart1 = new Highcharts.Chart({
		 chart: {
			renderTo: "stat_chart",
			zoomType: "xy"
		 },
		 title: {
			text: "'.translate('Visitors and average page views').'"
		 },
		 xAxis: {
			labels: {
			   enabled: true
			},
			categories: '.json_encode($data['days']).'
		 },
		 yAxis: [{
			labels: {
			   enabled: false,
			},
			title: {
			   text: ""
			},
			gridLineWidth: 0
		 },{
			labels: {
			   enabled: false,
			},
			title:{
				text: "",
			},
			opposite: true,
			gridLineWidth: 0
		}],
		tooltip: {
			formatter: function() {
				return ""+this.series.name +": "+ this.y +"";
			}
		},
        credits: {
            enabled: false
        },
		plotOptions: {
			series: {
				stacking:"normal"
			}
		},
		 series: [{
			name: "Visitors",
			yAxis: 1,
			type: "areaspline",
			color: "#008CCD",
			data: '.json_encode($data['visitors']).'
		 },{
			name: "Page per visits",
			yAxis: 0,
			type: "spline",
			color: "#FFC",
			data: '.json_encode($data['pagespervisit']).'
		 }]
	  });
	});
});
})(jQuery);
var color = {
	colors: ["#F88", "#FFC", "#008CCD"]
};
var bw = {
	colors: ["#EEE", "#DDD", "#BBB"],
};
var highchartsOptions = Highcharts.setOptions(bw);


';
		$r .= dv('twothird center').dv('','stat_chart','style="width:100%;height:600px;"').xdv().xdv();
		/*
		$r .= dv('padder');
		$visitors = Stat::sum('visitors');
		$visits = Stat::sum('pageloads');
		$bots = Stat::sum('bots');
		
		$r .= '<ul class="big grey squares">';
		$r .= '<li>'.$bots['total'].' '.translate('bot visits').'</li>';
		$r .= '<li>'.$visits['total'].' '.translate('pages loaded').' ('.$visits['avg'].'/day average)</li>';
		$r .= '<li>'.$visitors['total'].' '.translate('unique visitors').' ('.$visitors['avg'].'/day average)</li>';
		$r .= xdv();
		$r .= dv('padder').'<h1>Popularité du site</h1>'.Process::$stats->evolutionBox('pixyt.com').xdv();*/
		return $r;
	}
	
	public function review(){
		$m = 84;
		$s = T::$start*$m;
		T::$page['title'] = translate('Photos to review');
		$c = new Collection('Photo');
		T::$pagecount = ceil($c->total()/$m)-1;
		return $c->content('thumb',$s,$m,false,'id',true,'smallsquare');
	}
	
	function invite(){
		T::$page['title'] = translate('Invitation cards');
		if(isset($_REQUEST['generate'])){
			if(isset(Invite::$types[$_REQUEST['type']])){
				$id = Invite::generate('',$_REQUEST['type'],$_REQUEST['email']);
				$inv = new Invite($id);
				return $inv->display('preview');
			}
		}
		elseif(isset($_REQUEST['create'])){
			T::$actionBar[] = lnk(translate('afficher'),true,array('show'=>true),array('class'=>'btn'));
			$r = '';
			$r .= dv('padder');
			$r .= '<h2>'.translate('Generate invitations').'</h2>';
			$form = new Form();
			$form->type('select',Invite::$types,translate('What type of account is it?'));
			$form->email('input',false,translate('Type the recipient email'));
			$form->generate('hidden',true);
			$form->{'generate!'}('submit');
			$r .= $form->returnContent();
			return $r;
		}
		else{
			T::$actionBar[] = lnk(translate('create'),true,array('create'=>true),array('class'=>'btn'));
			$cards = new Collection('Invite');
			if(isset($_REQUEST['type'])){$cards->type=$_REQUEST['type'];}
			$r = '';
			if(!IS_AJAX){
				$r .= dv('tabs');
				$r .= lnk(translate('all'),'#cur',array('type'=>'any'),array('class'=>'tab','data-containerid'=>'invites'));
				foreach(Invite::$types as $v=>$t){
					$r .= lnk($t,'#cur',array('type'=>$v),array('class'=>'tab','id'=>'select_'.$v,'data-containerid'=>'invites'));
				}
				$r .= xdv();
				$r .= dv('mainContent','invites');
			}
			//$cards->uid = App::$user->id;
			$r .= $cards->content('preview',0,999);
			if(!IS_AJAX){$r .= xdv();}
			return $r;
		}
	}
	
	function load(){
		T::$page['title'] = translate('Load stats for '.SITENAME);
		T::$body[] = dv('splitLeft').dv('padder');
		foreach(Object::$objects as $idname=>$classname){
			$t = 0;
			$o = new Collection($classname);
			$number = $o->total();
			T::$body[] = 'Loaded '.$classname.' ('.$number.' objects found)<br/>';
		}
		T::$body[] = xdv().xdv();
		T::$body[] = dv('splitRight').dv('padder');
		T::$body[] = Process::showLoadTimes();
		T::$body[] = xdv().xdv();
	}
	
	function phpinfo(){
		phpinfo();
		die();
	}
	
	function orders(){
		$orders = new Collection('Order');
		$orders->status = 3;
		T::$page['title'] = translate('Orders');
		T::$body[] = '<h2>'.translate('There are').' '.$orders->total().' '.translate('orders waiting to be processed').'</h2>';
		$table = new Table();
		$table->addHeader(array('ID',translate('Name'),translate('Phone'),translate('Email'),translate('Items'),translate('Amount')));
		$table->insert($orders->content('tableLine',0,100,true,'created',false));
		T::$body[] = $table->returnContent();
	}
	
	function get_browser(){
		error_reporting(E_ALL);
		echo $_SERVER['HTTP_USER_AGENT'] . "\n\n<br />";

		$browser = get_browser(null, true);
		//$browser = get_browser("Googlebot/2.1 (+http://www.google.com/bot.html)", true);
		print_r($browser);
		die();
	}
}
?>