<?php
class Photo extends Object{
	public $id;
	public $uid;
	public $kid;
	public $prid;
	public $fileid;
	public $access;
	public $channel;
	public $title;
	public $genre;
	public $description;
	public $width;
	public $height;
	public $color;
	public $exif=array();
	public $tags=array();
	public $views;
	public $customOrder;
	public $dateShot;
	public $filename;
	public $filepath;
	public $make;
	public $model;
	public $aperture;
	public $sensitivity;
	public $exposure;
	public $focal;
	public $software;
	public $subjectDistance;
	public $R;
	public $G;
	public $B;
//	public $popularity;
//	public $rating;
	public $z;
		
	private $aspect;
	
	public static $selectName = false;		//like update[Site][123][page][21][c]
	
	public static function mime_types(){
		return array(
			'image/gif' => 'gif',
			'image/jpeg' => 'jpg',
			'image/pjpeg' => 'jpg',
			'image/jpg' => 'jpg',
			'image/png' => 'png',
			'application/pdf' => 'pdf',
			'image/psd' => 'psd',
			'image/bmp' => 'bmp',
			'image/tiff' => 'tiff',
			'image/tiff' => 'tiff',
			'image/jp2' => 'jp2',
			'image/iff' => 'iff',
			'image/vnd.wap.wbmp' => 'bmp',
			'image/xbm' => 'xbm',
			'image/vnd.microsoft.icon' => 'ico'
		);
	}
	
	public static function sizes($s=false){
		return arrayGetKey($s,array(
			'text'	=> array(32,32,true),
			'stack'	=> array(80,80,true),
			'small'	=> array(120,120),
			'thumb'	=> array(220,160,true),
			'cascade'=>array(260,0),
			'medium'=> array(600,0),
			'horizont'=>array(0,600),
			'large'	=> array(0,720),
			'full'	=> array(0,960),
		));
	}
	
	protected static function publicFunctions(){
		return array(
			'fullView'=>true,
			'preview'=>true,
			'feed'=>true,
			'lightbox'=>true,
			'instant'=>true,
			'embedInstant'=>true,
		);
	}
	
	protected static function ownerFunctions(){
		return array(
			'edit'=>true,
			'editPreview'=>true,
			'publish'=>true,
			'sell'=>true,
			'addtags'=>true,
			'select'=>true,
			'selectable'=>true,
		);
	}
	
	
	public function descriptor(){
		return array (
			'id'=>'int',
			'uid'=>'int',
			'kid'=>'int',
			'prid'=>'int',
			'fileid'=>'int',
			'access'=>'int',		//0=> not set, 1=> private, 2=> not listed, 3=> public
			'channel'=>'int',
	//		'popularity'=>'int',	//Popularity (= hearts count/Time factor - soon) - child affects parent
	//		'rating'=>'int',		//sum Votes/voters
			'z'=>'int',				//Curated index
			'title'=>'string',
			'genre'=>'string',
			'description'=>'string',
			'width'=>'int',
			'height'=>'int',
			'color'=>'string',
			'exif'=>'object',
			'tags'=>'list',
			'views'=>'int',
			'customOrder'=>'int',
			'dateShot'=>'int',
			'filename'=>'string',
			'filepath'=>'string',
			'make'=>'string',
			'model'=>'string',
			'aperture'=>'int',
			'sensitivity'=>'int',
			'exposure'=>'int',
			'focal'=>'int',
			'software'=>'string',
			'subjectDistance'=>'int',
			'R'=>'int',
			'G'=>'int',
			'B'=>'int',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		);
	}
	
	public static function channels(){
		return array(
			0=>'serie',		//a=serieId
			1=>'serie',		//a=pid
			2=>'site',		//a=SiteId b=pageId
			3=>'shop',		//a=storeId
			4=>'project',
			5=>'prooffile',	//a=uid (which wall)
			6=>'profile',
			7=>'sitelogo',	//a=site
			8=>'file',
		);
	}
	
	
	public function validateData($n,$v){
		switch ($n){
			case 'id':
			case 'uid':
			case 'width':
			case 'height':
			case 'path':
			case 'filename':
			case 'exif':
			case 'created':
			case 'modified':
				return true;
			break;

			case 'filedata':
				// save ut8 input string as file
				if(!is_dir(ROOT.'local/files/'.App::$user->id.'/')){
					mkdir(ROOT.'local/files/'.App::$user->id.'/');
				}
				file_put_contents(ROOT.'local/files/'.App::$user->id.'/'.sha1($v),$v);
				die(ROOT.'local/files/'.App::$user->id.'/'.sha1($v).' ????');
			break;

			case 'filepath':
				//let's not allow this for now.
				return false;
			break;

			case 'filename':
				$this->$n = $v;
				return false;
			break;
			
			case 'removeHD':
				if($this->removeHD()){
					return true;
				}
				else{
					Msg::addMsg(translate('Failed to remove file.'));
				}
			break;
			
			case 'title':
				if(empty($v)){$this->$n=$v;return true;}
				elseif(strlen($v) <= 255){
					if(!$this->isUniqueTitle($v)){
						Msg::notify('A photo with that name already exists!');
						return false;
					}
					$this->$n = stripslashes($v);
					return true;
				}
				else{
					Msg::addMsg($n.' '.translate('too long (255 chars max)'));
					return false;
				}
			break;
			
			case 'prid':
				if(is_numeric($v)){$this->$n=$v;return true;}
				Msg::addMsg($n.' '.translate('can only be numeric!'));
			break;
			
			case 'kid':
				if(is_numeric($v)){$this->$n=$v;return true;}
				Msg::addMsg($n.' '.translate('can only be numeric!'));
			break;
			
			case 'fileid':
				if(is_numeric($v)){$this->$n=$v;return true;}
				Msg::addMsg($n.' '.translate('can only be numeric!'));
			break;
			
			case 'channel':
				if(is_numeric($v)){$this->$n=$v;return true;}
				Msg::addMsg($n.' '.translate('can only be numeric!'));
			break;
			
			case 'access':
				if(is_numeric($v)){
					if($n==3 && App::$user->validated > 100){$n++;}
					$this->$n=$v;
					return true;
				}
				Msg::addMsg($n.' '.translate('can only be numeric!'));
			break;
			
			case 'genre':
				if(strlen($v)>0 && strlen($v) < 255){
					$this->$n = $v;
					return true;
				}
			break;
			
			case 'description':
				if(strlen($v) <= 900){
					$this->$n = stripslashes(urldecode($v));
					return true;
				}
				else{
					Msg::addMsg($n.' '.translate('too long (900 chars max)'));
					return false;
				}
			break;
			
			case 'removetag':
				if(!isset($this->tags[$v])){Msg::addMsg(translate('tag not found!'));return false;}
				else{
					unset($this->tags[$v]);
					if(IS_AJAX){
						res('script','$("#tag_'.$v.'").fadeOut();activate($("#tags"));');
						if(empty($this->tags)){res('script','$("#tags").html="'.translate('No tags yet.').'"');}
					}
				}
				return true;
			break;
			
			case 'tags':
				if(!is_array($v)){
					$v = preg_split('/[,;]/',$v);
				}
				$append = '';
				foreach($v as $tag){
					$tag = sanitize($tag);
					$id = md5($tag);
					if(count($this->tags) > 7){
						Msg::notify(translate('Max 7 tags are allowed'));
						break;
					}
					if(!isset($this->tags[$id])){
						if(strlen($tag) > 40){
							Msg::notify(translate('Tags may not exceed 40 chars'));
						}
						else{
							$this->tags[$id] = $tag;
						}
					}
					else{
						Msg::addMsg($tag.' '.translate('already exists.'));
					}
				}
				return true;
			break;
		}
		return false;
	}
	
	public function construct(){
		if($this->isDummy || $this->id == 0){return;}
	}

	public function selectable($size='small',$action=false,$data=array(),$tools=''){
		if($action===false){$img = $this->img('stack');}
		else{$img = lnk($this->img('stack'),$action,$data,array('data-gethtml'=>'0','ajax'=>true));}
		if(!empty($tools)){$img.=dv('tools').$tools.xdv();}
		return dv('preview selectable '.$size,'Photo_'.$this->id.'_selectable','data-className="Photo" data-id="'.$this->id.'" data-size="'.$size.'"').$img.xdv();
	}

	public function getName($short = false,$maxLen = 20){
	   	if(!empty($this->title)){
			if($short === true && strlen($this->title) > $maxLen){return substr($this->title,0,$maxLen-3).'...';}
			return $this->title;
		}
		return '';
	}
	
	public function getImageLine($range=8){//for below and 4 above current
		$r = '';
		foreach($this->surroundingImages($range) as $img){
			if(is_object($img) && $img->className == 'Photo'){
				$r .= lnk($img->img('small'),'photo/'.$img->id.'/edit',array(),array('class'=>'fadeImgLnk'));
			}
		}
		return $r;
	}
	
	public function viewsGraph(){
		T::$jsincludes[] = 'highcharts.js';
	//	$data = Process::$stats->evolution('pixyt.com/photo/'.$this->id,20);
		$data = array('pageloads'=>array(),'days'=>array());
	//	if(App::$user->id!=1){return false;}
		$now = time();
		$q = 'SELECT pageloads,date FROM Stat WHERE url = "pixyt.com/photo/'.$this->id.'" ORDER BY id';
		if(LP::$db->customQuery($q,$d)){
			foreach($d as $e){
				$d = explode('-',$e['date']);
				$d = mktime(0,0,0,$d[1],$d[2],$d[0]);
				$data['days'][] = date('d M',$d);
				$data['pageloads'][] = (int)$e['pageloads'];
			}
		}
		
		T::$jsfoot[] = '
(function($){
	$(function () {
	var chart1;
	var days = '.json_encode($data['days']).';
	$(document).ready(function() {
	  chart1 = new Highcharts.Chart({
		 chart: {
			renderTo: "activity_chart",
			type: "column",
			zoomType: "xy"
		 },
		 title: {
			text: ""
		 },
		 xAxis: {
			labels: {
			   enabled: false
			},
			categories: days
		 },
		 yAxis: {
			labels: {
			   enabled: false,
			},
			title: {
			   text: ""
			}
		 },
		tooltip: {
			formatter: function() {
				return days[this.y]+": "+this.y+" "+this.series.name;
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
			name: "visits",
			type:"area",
			data: '.json_encode($data['pageloads']).'
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
		return dv('','activity_chart','style="width:100%;height:200px;"').xdv();
	}
	
	public function nextImage(){
		$photos = new Collection('Photo');
		$photos->where(array('uid'=>$_SESSION['uid'],'kid'=>$this->kid));
		$images = $photos->get();
		$photos = array();
		foreach($images as $im){$photos[] = $im->id;}
		$pos = array_search($this->id,$photos);
		if(isset($photos[$pos+1])){
			return $photos[$pos+1];
		}
		return reset($photos);
	}
	
	public function previousImage(){
		$photos = new Collection('Photo');
		$photos->where(array('uid'=>$_SESSION['uid'],'kid'=>$this->kid));
		$images = $photos->get();
		$photos = array();
		foreach($images as $im){$photos[] = $im->id;}
		$pos = array_search($this->id,$photos);
		if(isset($photos[$pos-1])){
			return $photos[$pos-1];
		}
		return end($photos);
	}
	
	public function surroundingImages($range=4){
		if($this->isDummy){return false;}
		if($this->kid == 0){
			$photos = array_values(self::$unsorted[$this->uid]);
		}
		else{
			$photos = array_values($stack->children());
			foreach($photos as $i=>$p){if($p->className != 'Photo'){unset($photos[$i]);}}
		}
		$total = count($photos);
		$o = intval($this->customOrder);
		if(!empty($photos)){
			$r = array();
			for($i = ($o - $range); $i <= ($o + $range); $i++){
				if(isset($photos[$i])){$r[] = $photos[$i];}
			}
			return $r;
		}
		return array();
	}
	
	public function share($medium='facebook'){
		$author = new User($this->uid);
		$buf = $this->img('medium',$img);
		$txt = $this->title.' '.translate('by').' '.$author->fullName('full').' - on Pixyt';
		return $medium(HOME.'photo/'.$this->id,$txt,HOME.$img);
	}
	
	//DISPLAY MODES
	protected function curves(){
		$r = dv('curves');
		$buf = $this->img('small',$path);//Just to make sure the size exists
		if(!file_exists(ROOT.$path)){return false;}
		
		return false;
		
		if(empty($this->color)||$this->color=='000000'){$this->color = $image->getAverageColor();$this->update(true);}
		$recreate = true;
		$this->color = $image->getAverageColor();
		$red = $image->getLuminanceArray('R');
		$green = $image->getLuminanceArray('G');
		$blue = $image->getLuminanceArray('B');
		//$r .= '<h3>'.translate('RGB levels').'</h3>';
		$r .= dv('curve');
		for($i=0;$i<=255;$i++){
			$v = ceil(($red[$i]+$green[$i]+$blue[$i])/3*2);
			if($v>140){$v = 140;}
			$r .= '<p style="height:'.$v.'px;left:'.str_replace(',','.',round(($i/255*100),2)).'%;"></p>';
		}
		$r .= xdv().xdv();
		return $r;
	}

	protected function portfolioSelectThumb(){
		if($this->isDummy){return false;}
		if(!isset($_REQUEST['fid'])){$this->error('No portfolio selected',90);}
		return lnk($this->img('small'),'#',array('update[Portfolio]['.$_REQUEST['fid'].'][photo]'=>$this->id,'type'=>'photo','folder'=>$_REQUEST['folder']),array('data-gethtml'=>'false','id'=>$this->id));
	}
	
	protected function fullView(){
		$r = '';
		$author = new User($this->uid);
		$stack = new Stack($this->kid);
		if($this->width>$this->height){
			$r .= dv('Photo');
		}
		else{
			$r .= dv('Photo');
		}
		$r .= dv('social-actions');
		$r .= $this->heart();
		$r .= $this->promote();
		if($this->isOwner){
			$r .= lnk('<img src="'.HOME.'img/share/edit.png"/>','photo/'.$this->id.'/edit',array(),array('title'=>translate('Edit'),'id'=>'edit','class'=>'socialBtn edit'));
		}
		$r .= $this->share('facebook');
		$r .= $this->share('tweet');
		$r .= $this->share('pin');
		$r .= xdv();
		$this->header('<h2>'.lnk($author->picture(),'user/'.$this->uid).' '.$author->fullName('link').'</h2>');
		T::$page['title'] = $this->getName().' '.translate('by').' '.$author->fullName();
		
		if($this->kid != 0 && !$stack->access()){
			Msg::addMsg(401,0,Msg::CRITICAL);
			return false;
		}
		if(!empty($this->title)){
			T::$page['title'] = $this->title;
			$this->header('<h1>'.$this->title.'</h1>');
		}
		else{
			T::$page['title'] = translate('Photo by').' '.$author->fullName('full');
		}
		if(!empty($this->description) && $this->description != 'photo description'){
			$this->header('<h3>'.frmt($this->description).'</h3>');
			T::$page['description'] = $this->description;
		}
		else{
			$desc='';
			T::$page['description'] = translate('A photo by').' '.$author->fullName('full');
		}
		$r .= $this->showImage('fullView');
		
		if($this->isDummy){return dv('','Photo_'.$this->id).'<h1>'.translate('Not available').'</h1>'.xdv();}
		$r .= '<br class="clearfloat"/>';
		$r .= dv('meta');
		$r .= $this->tags();
		if(!empty($this->software)||!empty($this->exposure)||!empty($this->sensitivity)||!empty($this->model)){
			$r .= dv('exif');
			if(!empty($this->software)){$r .= '<span class="tag"><span class="software">'.lnk($this->software,'search/'.urlencode($this->software)).'</span></span>';}
			if(!empty($this->exposure)){$r .= '<span class="tag"><span class="exposure">'.lnk('1/'.$this->exposure.'s','search/'.urlencode($this->exposure)).'</span></span>';}
			if(!empty($this->sensitivity)){$r .= '<span class="tag"><span class="sensitivity">'.lnk($this->sensitivity.'iso','search/'.urlencode($this->sensitivity)).'</span></span>';}
			if(!empty($this->model)){$r .= '<span class="tag"><span class="model">'.lnk($this->model,'search/'.urlencode($this->model)).'</span></span>';}
			$r .= '<br class="clearfloat"/>';
			$r .= xdv();
		}
		//$r .= '<span class="time" title="'.date('r',$this->created).'">'.lnk(prettyTime($this->created),'search/'.urlencode(date('d F Y',$this->created))).'</span>'.xdv();
		$r .= $this->comments();
		$r .= xdv();
		return $r;
	}
	
	protected function editPreview($s='mediumthumb'){
		$r='';
		$r .= dv('file preview','photo_'.$this->id,'data-customOrder="'.$this->customOrder.'"');
		$r .= lnk($this->img('small'),'photo/'.$this->id.'/edit',array());
		$r .= dv('tools').lnk('<img src="/img/tool-edit.png" height="25px" alt="edit"/>','photo/'.$this->id.'/edit').lnk('<img src="/img/tool-delete.png" height="25" alt="delete"/>','#cur',array('delete[Photo]['.$this->id.']'=>true),array('data-type'=>'confirm','data-matter'=>translate('Are you sure you want to delete this photo?'))).xdv();
		$r .= xdv();
		return $r;
	}
	
	protected function addtags(){
		$r ='';	
		$form = new Form($this->className,$this->id,true,array('ajax'=>true));
		$form->tags('textarea',translate('add tags'),translate('up to 7 tags'),'this.value=\'\'');
		$form->write('<br/>');
		$form->{translate('add')}('submit');
		$r .= $form->returnContent();
		return $r;
	}
	
	protected function publish(){
		if(!$this->isOwner){return false;}
		$r = '';
		$r .= dv('padded');
		$r .= '<p>'.translate('Share it on your website:').'</p>';
		if(count(App::$user->sites()) == 0){
			$r .= '<p>'.translate('You have no websites yet!').' '.lnk('create one','site/create','',array('class'=>'btn')).'</p>';
		}
		else{
			foreach(App::$user->sites() as $site){
				if(!empty($site->alias)){break;}
				$form = new Form('Site',$site->id,'ajax',array('ajax'=>true,'class'=>'full','data'=>array('method'=>'photo_publish','content'=>'Photo_'.$this->id)));
				$form->write('<p>'.translate('Publish to').' '.$site->url.'</p>');
				$o=array();
				foreach($site->content as $id=>$page){
					if($page['x']==2&&is_array($page['c'])){
						$o[$page['u']]=$page['t'];
					}
					elseif($page['x']==1){
						if(is_array($page['c'])){
							foreach($page['c'] as $sub_page){
								if($sub_page['x'] == 2)$o[$sub_page['u']]='--'.$sub_page['t'];
							}
						}
					}
				}
				$form->{'publish'}('select',$o,false);
				$form->{translate('Publish')}('submit');
				$r .= dv('entry');
				$r .= $form->returnContent();
				$r.= xdv();
			}
		}
		$r .= xdv();
		return $r;
	}
	
	protected function sell(){
		$r = '';
		$f = new Form('','',true,array('ajax'=>true));
		$col = new Collection('Product');
		$col->type = 'p';
		$col->load(0,100);
		$f->write('<h3>'.translate('Private prints').'</h3>');
		$w=array();
		foreach($col->results as $p){
			$f->{'products['.$p->id.']'}('checkbox',false,$p->title);
			$w[] = $p->title;
		}
		
		$f->write('<br/><h3>'.translate('Licences').'</h3>');
		$col = new Collection('Product');
		$col->type = 'c';
		$col->load(0,100,true,'id',false);
		foreach($col->results as $p){
			$f->{'products['.$p->id.']'}('checkbox',false,$p->title);
			$f->write(dv('lightgrey small').$p->description.xdv());
		}
		
		$f->write('<br/><h3>'.translate('Limited edition').'</h3>');
		$f->{'format'}('select',$w,'Format');
		$q=array();
		for($i=0;$i<=30;$i++){
			$q[] = array($i,$i);
		}
		$f->{'edition'}('select',$q,'Edition');
		
		$r .= dv('splitRight').'<h2>'.translate('Select available formats').'</h2>'.$f->returnContent().xdv();
		$r .= dv('splitLeft').'<img src="/img/schemes/sell-info-en_GB.png" alt="Sale workflow" title="Sale workflow"/>'.xdv();
		return $r;
	}
	
	protected function lightbox(){
		return $this->img('full');
	}
	
	public function showImage($viewMode='fullView'){
		$r = '';
		//$r .= fullNavigation($this->img('large'));
		//function fullNavigation($content){
		//	$r = '';
		$n = '<img src="'.HOME.'img/next-grey.png" width="85" height="85" alt="'.translate('Next photo').'" title="'.translate('Next photo').'"/>';
		$nt = translate('Next photo');
		$p = '<img src="'.HOME.'img/prev-grey.png" width="85" height="85" alt="'.translate('Previous photo').'" title="'.translate('Previous photo').'"/>';
		$pt = translate('Previous photo');
		$vurl = '';
		if($viewMode!='fullView'&&$viewMode!=''){$vurl='/'.$viewMode;}
		$nextItem = $this->next();
		$nextLink = strtolower($nextItem[0]).'/'.$nextItem[1].$vurl;
		$previousItem = $this->previous();
		$previousLink = strtolower($previousItem[0]).'/'.$previousItem[1].$vurl;
		$next = lnk($n,$nextLink,array(),array('title'=>$nt,'id'=>'next'));
		$prev = lnk($p,$previousLink,array(),array('title'=>$pt,'id'=>'prev'));
		T::js('
$("body").keydown(function(e) {
    var editing = $(document.activeElement).is("textarea,input");
	if(e.keyCode == 37 && !editing) {
		document.location.href="'.HOME.$nextLink.'";
	}
	else if(e.keyCode == 39 && !editing){
		document.location.href="'.HOME.$previousLink.'";
	}
});
$("#next").fadeTo("fast",0);
$("#prev").fadeTo("fast",0);
var img = $("img[id=Photo_'.$this->id.'_large]");
img.load(function(){
	var h = img.outerHeight(true)+"px";
	$(".next").css({height:h}).mouseover(function(){
		$("#next").stop(true,true).fadeTo("fast",0.7);
	}).click(function(){
		window.location = $("#next").attr("href");
	}).mouseout(function(){
		$("#next").stop(true,true).fadeTo("fast",0);
	});
	$(".prev").css({height:h}).mouseover(function(){
		$("#prev").stop(true,true).fadeTo("fast",0.7);
	}).mouseout(function(){
		$("#prev").stop(true,true).fadeTo("fast",0);
	}).click(function(){
		window.location = $("#prev").attr("href");
	});
});');
		$c='';
		$r .= dv('','mainFile');
		$r .= dv('prev').$prev.xdv();
		$r .= dv('next').$next.xdv();
		$r .= $this->img('large');
		//$r .= $content;
		$r .= xdv();
	//	return $r;
	//	}
		return $r;
	}
	
	protected function edit(){
		$r='';
		$stack = new Stack($this->kid);
		$r .= dv('inner-page');
		if(!empty($this->kid)){Stack::preload($this->kid);}
		T::$page['title'] = translate('Edit photo');
		if($this->kid != '0'){
			T::$header[] = lnk(translate('Make cover'),true,array('update[Stack]['.$this->aid.'][cover]'=>$this->id),array('title'=>translate('Make this the serie cover'),'class'=>'btn'));
		}
		if($this->isDummy || !$this->isOwner){return false;}
		if(isset($_REQUEST['do'])){
			switch($_REQUEST['do']){
				case 'delete':
					if(!$this->delete()){
						return dv('padder info').'<h2>'.translate('Failed to delete photo!').'</h2>'.xdv();
					}
					if(!empty($this->kid)){$url = HOME.'stack/'.$this->kid.'/edit';}else{$url = HOME.'account/organize';}
					if(!IS_AJAX){header('Location: '.$url);}
					else{res('script','$("#photo_'.$this->id.'").fadeOut();');}
					T::$page['title'] = translate('Photo deleted');
					return dv('padder info').'<h2>'.translate('The photo was deleted!').'</h2>'.xdv();
				break;
				
				case 'rotateright':
					Msg::notify('Sorry rorate function out of order.');break;
					$file = new File($this->fileid);
					$image = new Imagick($file->path());
					$image->rotateImage('#ffffff',90);
					$h = $this->height;
					$w = $this->width;
					$this->width = $h;
					$this->height = $w;
					$this->update();
					$xy = Image::xy('full',$this->width,$this->height);
					res('script','$("#pid_'.$this->id.'").fadeOut().attr("src","'.HOME.'local/photos/full/'.$this->id.'.jpg?'.time().'").delay(200).fadeIn();$(".next,.prev,#mainFile").css("height","'.$xy[1].'px");');
				break;
				
				case 'rotateleft':
					Msg::notify('Sorry rorate function out of order.');break;
					foreach(Image::$defaultsizes[Photo::$channels[$this->channel]] as $s){
						$image = new Image(ROOT.'local/photos/'.$s.'/'.$this->id.'.jpg',true,$this->id.'.jpg','image/jpeg');
						$image->rotate(ROOT.'local/photos/'.$s.'/'.$this->id.'.jpg',90);
					}
					$h = $this->height;
					$w = $this->width;
					$this->width = $h;
					$this->height = $w;
					$this->update();
					$xy = Image::xy('full',$this->width,$this->height);
					res('script','$("#pid_'.$this->id.'").fadeOut().attr("src","'.HOME.'local/photos/full/'.$this->id.'.jpg?'.time().'").delay(200).fadeIn();$(".next,.prev,#mainFile").css("height","'.$xy[1].'px");');
				break;
				
				default:
					Msg::addMsg(translate('Action not recognized!'));
				break;
			}
		}
		$file = new File($this->fileid);
		$r .= dv('navigation');
		$r .= dv('tab'); 
		$r .= dv('denomination').translate('photos').'<span class="right open"></span>'.xdv();
		$r .= dv('el softlink','','data-url="account/organize"').'<span class="bold">'.translate('all').'</span>'.xdv();
		if(!empty($this->kid)&&is_object($stack)){
			$r .= dv('el softlink','','data-url="stack/'.$this->kid.'/edit"').'<span class="bold">'.$stack->title.'</span>'.xdv();
			$goback = 'function goback(){window.location.href=HOME+"stack/'.$this->kid.'/edit";}';
		}
		else{
			$goback = 'function goback(){window.location.href=HOME+"account/organize";}';
		}
			T::$js[] = $goback.'
$("body").unbind("keydown").bind("keydown",function(e) {
	if(e.keyCode == 27){
		setTimeout("goback()",100);
		$(".organize").slideUp(200);
	}
});';
		$r .= xdv();
		$r .= dv('tab');
		$r .= dv('denomination').translate('histogram').xdv();
		$r .= dv('el').$this->curves().xdv();
		$r .= xdv();
		$r .= dv('tab');
		$r .= dv('denomination').translate('Exif data').xdv();
		$r .= dv('el').$this->exif().xdv();
		$r .= xdv();
		$r .= dv('tab');
		$r .= dv('denomination').translate('tags').xdv();
		$r .= dv('el').$this->tags().xdv();
		$r .= dv('el ajax','','data-url="'.HOME.'photo/'.$this->id.'/addtags" data-type="popup" data-gethtml="true"').'<span class="bold">+'.translate('add').'</span>'.'<span class="right"><img src="/img/addtags40.png" height="20" alt="'.translate('Add tags').'" title="'.translate('Add tags').'"/></span>'.xdv();
		$r .= xdv();
		$r .= dv('tab');
		$r .= dv('denomination').translate('info').xdv();
		$r .= dv('el').'<ol class="squares"><li>'.lnk('<img src="'.HOME.'/img/site_editor/view.png" height="30" alt="'.translate('Photo views').'" title="'.translate('Photo views').'"/> '.prettyNumber($this->views,1),'photo/'.$this->id,array(),array('class'=>'grey')).'</li>';
		$r .= '<li>'.lnk('<img src="'.HOME.'/img/site_editor/heart.png" height="30" alt="'.translate('Photo hearts').'" title="'.translate('Photo hearts').'"/> '.prettyNumber($this->heartsCounter(),1),'photo/'.$this->id,array(),array('class'=>'grey')).'</li>';
		$r .= '<li>'.lnk('<img src="'.HOME.'/img/site_editor/comment.png" height="30" alt="'.translate('Photo comments').'" title="'.translate('Photo comments').'"/> '.prettyNumber($this->commentsCounter(),1),'photo/'.$this->id,array(),array('class'=>'grey')).'</li>';
		$r .= '<li><img src="'.HOME.'/img/site_editor/color.png" height="30" alt="'.translate('Photo color').'" title="'.translate('Photo color').'" class="alignTop"/> '.$this->colorProfile().'</li>';
		$r .= '<li>'.prettyBytes($file->size).'</li></ol>'.xdv();
		$r .= xdv();
		$r .= xdv();
		$r .= dv('organize','pagelet');
		if(isset($_REQUEST['help']) || !isset(App::$user->data['settings']['tip']['edit-photo-top']) || App::$user->data['settings']['tip']['edit-photo-top']!=1){
			$close = lnk('close','#cur',array('update[User]['.App::$user->id.'][dismissTip]'=>'edit-photo-top'),array('class'=>'btn right'));
			$r .= dv('help','tip_edit-photo-top').$close.'<h2>'.translate('This page is where you can edit your photo.').'</h2>'.'<h3>'.translate('Drag a file to the image to replace it.').'</h3>'.'<br class="clearfloat"/>'.xdv();
		}
		$r .= dv('head');
		if(!empty($this->kid)){
			$r .= dv('toolbox').lnk('<img src="/img/stack40.png" alt="Stack" title="'.translate('back to stack').'">','stack/'.$this->kid.'/edit',array('s'=>$_REQUEST['s']),array('class'=>'back')).xdv();
		}
		else{
			$r .= dv('toolbox').lnk('<img src="/img/organize-grey.png" alt="'.translate('Organize').'" title="'.translate('Organize').'">','account/organize',array('s'=>$_REQUEST['s']),array('class'=>'back')).xdv();
		}
		$r .= dv('toolbox').'<h3><span id="'.$this->idPrefix.'_title" class="editable title">'.$this->title.'</span></h3>'.xdv();
		$r .= dv('toolbox right').lnk('<img src="/img/trashbin40.png" alt="delete" title="Delete"/>','photo/'.$this->id.'/edit',array('do'=>'delete'),array('title'=>translate('Delete photo'),'data-type'=>'confirm','data-matter'=>translate('Are you sure you wish to delete this photo?'))).xdv();
		$r .= dv('toolbox right').lnk('<img src="/img/share40.png" alt="'.translate('Share').'" title="'.translate('Share').'"/>','photo/'.$this->id.'/publish',array(),array('data-type'=>'popup','title'=>translate('Publish photo'))).xdv();
		$r .= dv('toolbox right').lnk('<img src="/img/addtags40.png" alt="'.translate('Add tags').'" title="'.translate('Add tags').'"/>',strtolower($this->className).'/'.$this->id.'/addtags',array(),array('data-type'=>'popup')).xdv();
	
		$r .= dv('toolbox right').lnk('<i class="icon-black icon-download-alt"></i> '.translate('download'),'download.php',array('fileid'=>$file->id,'accessCode'=>$file->accessCode),array('class'=>'btn')).xdv();;
		
		if(App::$user->id==1){
			$r .= dv('toolbox right').lnk('<img src="/img/sell-image40.png" alt="'.translate('Sell').'" title="'.translate('Sell photo').'"/>','photo/'.$this->id.'/sell',array(),array('data-type'=>'popup','title'=>translate('Sell this photo'))).xdv();
		}	
		$counterclock = '<img src="'.HOME.'img/rotate-left40.png" title="'.translate('Rotate counter clockwise').'" alt="'.translate('Rotate photo').'" title="'.translate('Rotate photo').'"//>';
		$r .= dv('toolbox right').lnk($counterclock,'#cur',array('do'=>'rotateleft'),array('title'=>translate('Rotate counterclockwise'),'class'=>'rotate')).xdv();
		$clock = '<img src="'.HOME.'img/rotate-right40.png" title="'.translate('Rotate clockwise').'" alt="'.translate('Rotate photo').'" title="'.translate('Rotate photo').'"//>';
		$r .= dv('toolbox right').lnk($clock,'#cur',array('do'=>'rotateright'),array('title'=>translate('Rotate clockwise'),'class'=>'rotate')).xdv();
		$r .= xdv().'<br class="clearfloat"/>';
		
		$r .= dv('','photoContainer');
		$r .= $this->showImage('edit');
		//$r .= dv('replaceFile').Photo::uploadBtn(1,$this->kid,$this->fileid,'photoContainer').xdv();
		$r .= $this->getImageLine(8);
		$r .= xdv();
		$r .= '<p class="help-link rightText">'.lnk('FAQ','help','',array('class'=>'grey')).'</p>';
		$r .= xdv();
		$r .= '<br class="clearfloat"/>';
		$r .= xdv();
		return $r;
	}
	
	public function preview($size='mediumthumb',$islink=true,$async=true){
		if($this->id == 0){return 'Dummy story.';}
		$r = dv('Photo preview').$this->heart();
		$r .= lnk($this->img('thumb'),'photo/'.$this->id);
		//$r .= dv('head').lnk($this->getName().' '.translate('by').' '.$author->fullName(),'photo/'.$this->id,array(),array('title'=>$this->getName().' '.translate('by').' '.$author->fullName())).xdv();
		$r .= xdv();
		return $r;
	}
	
	protected function statView(){
		if($this->id == 0){return 'Dummy story.';}
		$r= dv('statBox');
		$r .= dv('splitLeft').$this->img('small',true).xdv();
		$r .= dv('splitRight').'<p class="views">'.sprintf(ngettext('%d view','%d views',$this->views),$this->views).'</p>';
		$r .= '<p class="hearts">'.$this->heartsCounter().' hearts</p>';
		$r .= '<p class="comments">'.$this->commentsCounter().' comments</p>'.xdv();
		$r .= '<br class="clearfloat"/><h3>'.lnk($this->getName(),'photo/'.$this->id,array(),array('title'=>$this->getName())).'</h3>';
		$r .= xdv();
		return $r;
	}
	
	protected function colorProfile(){
		return '<span class="colorProfile" style="background-color:#'.$this->color.';"></span>';
	}
	
	protected function fileinfo(){
		$r = '';
		$r .= dv('fileinfo small').'<h3>'.translate('File info').'</h3><p>'.translate('Original filename').': '.$this->filename.'</p>';
		$r .= '<p title="'.translate('File uploaded').'">'.translate('uploaded').' '.prettyTime($this->created).' ('.date('d/m/y H:i',$this->created).')'.'</p>';
		$r .= '<p title="'.translate('Original uploaded photo definition').'.">'.round(($this->width*$this->height)/1000000,2).'Mpx ('.$this->width.' x '.$this->height.')</p>';
		if(!empty($this->path_hd)){$r .= '<p>'.translate('This photo has a HD version.').'</p>';}
		$r .= xdv();
		return $r;
	}
	
	public function saveColors(){
		$image = new Image(ROOT.'local/photos/smallthumb/'.$this->id.'.jpg',true);
		$rgb = $image->getAverageColor('rgb');
		$this->R = $rgb[0];
		$this->G = $rgb[1];
		$this->B = $rgb[2];
	}
	
	public function processExif($update=false){
		$file = new File($this->fileid);
		if($file->type != 'image/jpg' && $file->type != 'image/jpeg'){return true;}
		if(class_exists("Imagick")){
			$img = new Imagick($file->path());
			$this->height = $img->getImageHeight();
			$this->width = $img->getImageWidth();
		}
		$exif = exif_read_data($file->path());
		if(!empty($exif['Model'])){$this->model = $exif['Model'];}
		if(!empty($exif['COMPUTED']['ApertureFNumber'])){$this->aperture = $exif['COMPUTED']['ApertureFNumber'];}
		elseif(!empty($exif['FNumber'])){
			$values=explode('/',$exif['FocalLength']);
			$this->aperture = ((int)$values[0]/(int)$values[1]);
		}
		if(!empty($exif['ISOSpeedRatings'])){$this->sensitivity = $exif['ISOSpeedRatings'];}
		if(!empty($exif['ExposureTime'])){
			$values=explode('/',$exif['ExposureTime']);
			$gcd=gcd($values[0],$values[1]);
			$this->exposure = round(intval($values[0])/intval($values[1])*1000);
		}
		if(!empty($exif['FocalLength'])){
			$values=explode('/',$exif['FocalLength']);
			$this->focal = ((int)$values[0]/(int)$values[1]);
		}
		if(!empty($exif['DateTimeOriginal'])){$this->dateShot = strtotime($exif['DateTimeOriginal']);}
		if(!empty($exif['Software'])){$this->software = $exif['Software'];}
		if(!empty($exif['subjectDistance'])){
			$values=explode('/',$exif['SubjectDistance']);
			$this->subjectDistance = (intval($values[0])/intval($values[1]));
		}
		return true;
	}
	
	public function exif($specificval=false){
		if(!empty($this->exif)){$exif = $this->exif;}
		else{
			$file = new File($this->fileid);
			if($file->type == 'image/jpg' || $this->type == 'image/jpeg'){$exif = $this->exif = exif_read_data($this->path());$this->update(true);}
			else{$this->exif = array('empty'=>true);$this->update(true);}
		}
		$table = new Table('exifdata tiny stdTable');
		if(isset($this->exif['empty']) && $this->exif['empty']==true){
			$table->addLine(array(translate('Exif data not available')));
		}
		else{
			if(!empty($exif['Model'])){$table->addLine(array(translate('Camera model'),$exif['Model']));}
			if(!empty($exif['COMPUTED']['ApertureFNumber'])){$table->addLine(array(translate('Aperture'),$exif['COMPUTED']['ApertureFNumber']));}
			elseif(!empty($exif['FNumber'])){$values=explode('/',$exif['FocalLength']);$table->addLine(array(translate('Aperture'),'F'.((int)$values[0]/(int)$values[1])));}
			if(!empty($exif['ISOSpeedRatings'])){$table->addLine(array(translate('Sensitivity'),'ISO '.$exif['ISOSpeedRatings']));}
			if(!empty($exif['ExposureTime'])){
				$values=explode('/',$exif['ExposureTime']);
				$gcd=gcd($values[0],$values[1]);
				$table->addLine(array(translate('Exposure'),(int)$values[0]/$gcd.'/'.(int)$values[1]/$gcd.' '.translate('seconds')));
			}
			if(!empty($exif['FocalLength'])){$values=explode('/',$exif['FocalLength']);$table->addLine(array(translate('Focal lenght'),((int)$values[0]/(int)$values[1]).'mm'));}
			if(!empty($exif['DateTimeOriginal'])){$table->addLine(array(translate('Date'),$exif['DateTimeOriginal']));}
			//if(isset($specificval)){if(empty($exif[$specificval])){return '';}return $exif[$specificval];}
		}
		return $table->returnContent();
	}
	
	//PUBLIC FUNCTIONS
	public function __call($n,$v){
		if(is_numeric($n)){
			$s = new Photo($n);
			T::$page['title'] = $s->title();
			T::$body[] = $s->fullView();
		}
	}
	
	private function mkinstant($s='s'){
		$file = new File($this->fileid);
		if(!file_exists($file->path())){
			return false;
		}
		switch($s){
			case 's':
			default:
				$s='s';
				$ih = $iw = 500;
			break;
		}
		$colors= array(
			0=>'#996666',
			1=>'#997777',
			2=>'#885555'
		);
		$colorize=array($colors[rand(0,2)],0.1);
		$image = new Imagick();
		$image->readImage($file->path());
		$image->cropThumbnailImage($iw,$ih);
		$image->setimagebackgroundcolor($colors[rand(0,2)]);
		$image->colorizeImage($colorize[0],$colorize[1]);
		$image->modulateImage(110,10,100);
		$image->gammaImage('0.'.rand(75,95),Imagick::CHANNEL_YELLOW);
		$image->setImageCompression(Imagick::COMPRESSION_JPEG);
		$image->setImageCompressionQuality(80);
		$image->stripImage();
		$image->cropThumbnailImage($iw,$ih);
		$image->borderImage('#fffffe',25,25);
		$insta = new Imagick(ROOT.'-/img/instants/'.$s.'/'.rand(1,12).'.png');
		$image->compositeImage($insta,Imagick::COMPOSITE_DEFAULT,0,0);
		$image->setImageFormat('jpg');
		$path = 'cdn/instant/'.$s.'/';
		checkPath($path);
		$image->writeImage(ROOT.$path.$this->id.'.jpg');
		return true;
	}
	
	public function mkhd($w=1100,$h=720){
		$file = new File($this->fileid);
		if(!file_exists($file->path())){
			Msg::addMsg('Failed to resize image [file does not exits='.$file->path().']!',0,Msg::CRITICAL);
			return false;
		}
		$image = new Imagick($file->path());
		$image->setimagebackgroundcolor('#ffffff');
		if($image->getImageWidth() > $image->getImageHeight()){
			$image->resizeImage($w,0,Imagick::FILTER_LANCZOS,0.8);
		}
		else{
			$image->resizeImage(0,$h,Imagick::FILTER_LANCZOS,0.8);
		}
		if($image->getImageColorspace() != Imagick::COLORSPACE_SRGB){
			$image->setImageColorspace(Imagick::COLORSPACE_SRGB);
		}
		$image->setImageCompression(Imagick::COMPRESSION_JPEG);
		$image->setImageCompressionQuality(92);
		$profiles = $image->getImageProfiles('*', false);
		if(array_search('icc', $profiles) === false){
			$image->profileImage('icc',ROOT.'inc/icc/rgb/AdobeRGB1998.icc');
		}
		$image->setImagePage(0,0,0,0);
		$image->setImageFormat('jpg');
		$path = 'cdn/'.strval($w).'x'.strval($h).'/h/';
		checkPath($path);
		$image->writeImage(ROOT.$path.$this->id.'.jpg');
		if(!file_exists(ROOT.$path.$this->id.'.jpg')){
			die('FAILED WRITING IMAGE TO `'.ROOT.$path.$this->id.'.jpg`');
		}
		return true;
	}
	
	private function mkld($w,$h){
		$file = new File($this->fileid);
		if(!file_exists($file->path())){
			return false;
		}
		$ih = $h;
		$iw = $w;
		$image = new Imagick($file->path());
		if(empty($this->height)||empty($this->width)){
			$this->height = $image->imageHeight();
			$this->width = $image->imageWidth();
		}
		if($ih==0&&$iw==0){
			$ih = $iw = 25;
		}
		elseif($iw==0){
			$iw = ($this->width/$this->height)*$ih;
		}
		elseif($ih==0){
			$ih = ($this->height/$this->width)*$iw;
		}
		$image->cropThumbnailImage($iw,$ih);
		$image->setImageCompression(Imagick::COMPRESSION_JPEG);
		$image->setImageCompressionQuality(88);
		$image->stripImage();
		$image->setimagebackgroundcolor('#ffffff');
		$image->setImagePage(0, 0, 0, 0);
		$image->setImageFormat('jpg');
		$path = 'cdn/'.strval($w).'x'.strval($h).'/l/';
		checkPath($path);
		$image->writeImage(ROOT.$path.$this->id.'.jpg');
		return true;
	}
	
	public function directory(){
		$c = new Collection('Photo');
		T::$body[] = $c->content('preview');
	}
	
	public function thumb($s='',$link=false){
		if($this->isDummy){return false;}
		return $this->img('thumb');
	}
	
	public static function uploadBtn($channel=0,$kid=0,$fileid=0,$container='uploadBox'){
		return File::UploadBtn($channel,$kid,$fileid,$container);
	}
	
	public function addHDbutton(){
		if(empty($this->path_hd)){
			return dv('hdbtn','HDbtn_'.$this->id).Photo::uploadBtn(1,$this->kid,$this->id).xdv();
		}
		else{
			return lnk('Remove HD',true,array('update[Photo]['.$this->id.'][removeHD]'=>true),array('class'=>'btn'));
		}
	}
	
	public function feed($s='600Wide',$txt='',$meta=true){
		$r = '';
		$author = new User($this->uid);
		$buf = $this->img('medium',$path);
		$t = $this->getName().' '.translate('by').' '.$author->fullName('full');
		$url = HOME.'photo/'.$this->id;
		$c = 'Photo feed s'.$s;
		$r .= dv($c).'<h2><span class="date">'.date('d/m',$this->created).'</span>'.$this->title.'</h2>'.dv('relative').lnk($this->img('medium'),'photo/'.$this->id).share($url,$t,$path).xdv();
		$r .= dv('txt').$txt.xdv();
		if($meta){$r .= dv('meta').dv('left').$author->fullName('link').xdv().dv('right').xtlnk('http://pixyt.com','via Pixyt',array('class'=>'via lightgrey')).xdv().xdv();}
		return $r.xdv();
	}
	
	public function img_src($s){
		$im = $this->img($s,$path);
	}
	
	public function img($s,&$path=''){
		$sizes = self::sizes();
		if(!isset($sizes[$s])){
			$this->error('Wrong IMG size ('.$s.').');
			return false;
		}
		$size = $sizes[$s];
		$path = 'cdn/'.$this->uid.'/'.$s.'/'.$this->id.'.jpg';
		if(!file_exists($path)){$this->resize($s);}
		$classes = '';
		$data='';
		if(!empty($this->height)&&!empty($this->width)){
			$ratio = $this->width/$this->height;
			if(!isset($size[2])||$size[2]===false){
				if($this->width>$this->height){
					if($size[1]!=0){
						$size[0]=round($size[1]*($this->width/$this->height));
					}
					else{
						$size[1]=round($size[0]/($this->width/$this->height));
					}
				}
				else{
					if($size[0]!=0){
						$size[1]=round($size[0]*($this->height/$this->width));
					}
					else{
						$size[0]=round($size[1]/($this->height/$this->width));
					}
				}
			}
			if($ratio>1.1){$classes.=' h';}
			elseif($ratio<0.9){$classes.=' v';}
			else{$classes.=' sq';}
		}
		if(!$this->isOwner()){
			$classes.=' nocopy';
			if($size[1]>400||$size[0]>400){
				$this->increment('views');
				$data .= ' data-prev="'.$this->previousImage().'" data-next="'.$this->nextImage().'"';
			}
		}
		$attr = ' alt="photo '.$this->id.'" class="photo'.$classes.'"';
		if(!empty($size[0])){$attr .= ' width="'.$size[0].'"';}
		if(!empty($size[1])){$attr .= ' height="'.$size[1].'"';}
		if(PROTOCOL == 'https://'){$host = 'https://pixyt.com';}
		else{$host = '//pix.yt';}
		$path = $host.'/'.$path;
		return '<img id="Photo_'.$this->id.'_'.$s.'" title="'.$this->title.'" src="'.$path.'"'.$attr.$data.'/>';
	}
	
	public function image($w,$h,$hd=false){
		if($hd){$d='h';$ishd='&hd';}else{$d='l';$ishd='';}
		$path = 'cdn/'.strval($w).'x'.strval($h).'/'.$d.'/'.$this->id.'.jpg';
		if(empty($this->height)||empty($this->height)){
			$this->saveExif(true);
			if(empty($this->height)||empty($this->height)){$this->error('Could not set image Height and Width in Photo::image()');}
		}
		$ratio = $this->width/$this->height;
		$ih=$h;
		$iw=$w;
		if(!$hd){
			if($w==0){$iw=round($h*$ratio);}
			elseif($h==0){$ih=round($w/$ratio);}
		}
		else{
			if($ratio > $w/$h){
				$ih=round($w/$ratio);
			}
			else{
				$iw=round($h*$ratio);
			}
		}
		$classes = '';
		if($ratio>1.1){$classes.=' h';}
		elseif($ratio<0.9){$classes.=' v';}
		else{$classes.=' sq';}
		if($ih>$this->height&&$iw>$this->width){
			$ih=$this->height;
			$iw=$this->width;
		}
		$data = '';
		if(!$this->isOwner() && ($w >= 400 || $h >= 400) && User::$browser['crawler'] != 1){
			$this->increment('views');
			$data = ' data-prev="'.$this->previousImage().'" data-next="'.$this->nextImage().'"';
			$this->loadParent();
		}
		if(!$this->isOwner){$classes.= ' nocopy';}
		return '<img id="Photo_'.$this->id.'_'.strval($w).'x'.strval($h).'" title="'.$this->title.'" alt="photo '.$this->id.'" class="photo'.$classes.'" width="'.$iw.'" height="'.$ih.'" src="'.HOME.'image.php?w='.$iw.'&h='.$ih.'&id='.$this->id.'&'.$this->modified.$ishd.'"'.$data.'/>';
	}
	
	public function embedInstant($s='s'){
		if(!file_exists(ROOT.'cdn/instant/'.$s.'/'.$this->id.'.jpg')){
			if(!$this->mkinstant($s)){$this->error('Failed to create instant!',95);return false;}
		}
		$author = new User($this->uid);
		$u = 'http://pixyt.com/photo/'.$this->id.'/instant';
		$t = $this->title.''.translate('by').' '.$author->fullName('full');
		$form = new Form('','','',array('class'=>'full'));
		$form->embed('textarea',dv('instant','','style="box-shadow:20px 40px 60px rgba(140,110,110,0.4);margin:20px auto;border-radius:3px;padding:10px;text-shadow:1px 1px 1px #bbb;color:#999;background:url("http://pixyt.com/img/texture-9.png") repeat;width:550px;"').dv('spaced').lnk($author->picture(25,25).' '.$author->fullName('full'),'user/'.$this->uid,'',array('class'=>'grey')).'<span class="right">'.xtlnk('http://facebook.com/sharer.php?u='.urlencode($u).'&amp;t='.urlencode($t),'<img src="http://pixyt.com/img/share/facebook-black.png" height="30" alt="'.translate('Share on Facebook').'" title="'.translate('Share on Facebook').'"/>',array('rel'=>'nofollow')).'<a href="https://twitter.com/intent/tweet?original_referer='.urlencode(HOME).'&amp;source=tweetbutton&amp;text='.urlencode($t).'&amp;url='.urlencode($u).'" target="_blank" height="30" rel="nofollow"><img src="http://pixyt.com/img/share/twitter-black.png" alt="'.translate('Share on Twitter').'" title="'.translate('Tweet this').'"/></a>'.'</span>'.xdv().lnk('<img id="Photo_'.$this->id.'_instant" title="'.$this->title.'" alt="photo '.$this->id.'" class="photo polaroid loadme nocopy" src="'.HOME.'cdn/instant/'.$s.'/'.$this->id.'.jpg?'.$this->modified.'"/>','photo/'.$this->id.'/instant').xdv(),'Copy this');
		return $form->returnContent();
	}
	
	public function instant($s='s'){
		if(!file_exists(ROOT.'cdn/instant/'.$s.'/'.$this->id.'.jpg')){
			if(!$this->mkinstant($s)){$this->error('Failed to create instant!',95);return false;}
		}
		$author = new User($this->uid);
		$u = 'http://pixyt.com/photo/'.$this->id.'/instant';
		$t = $this->title.''.translate('by').' '.$author->fullName('full');
		return dv('instant').dv('spaced').lnk($author->fullName('full'),'user/'.$this->uid,'',array('class'=>'grey')).'<span class="right">'.$this->socialFeedback().xtlnk('http://facebook.com/sharer.php?u='.urlencode($u).'&amp;t='.urlencode($t),'<img src="/img/share/facebook-black.png" height="30" alt="'.translate('Share on Facebook').'" title="'.translate('Share on Facebook').'"/>',array('rel'=>'nofollow')).'<a href="https://twitter.com/intent/tweet?original_referer='.urlencode(HOME).'&amp;source=tweetbutton&amp;text='.urlencode($t).'&amp;url='.urlencode($u).'" target="_blank" height="30" rel="nofollow"><img src="/img/share/twitter-black.png" alt="'.translate('Share on Twitter').'" title="'.translate('Tweet this').'"/></a><a href="http://pinterest.com/pin/create/button/?url='.urlencode($u).'&amp;media='.urlencode(HOME.'cdn/instant/'.$s.'/'.$this->id.'.jpg').'&amp;description='.urlencode($t).'" target="_blank" rel="nofollow"><img src="/img/share/pinterest-black.png" height="30" alt="'.translate('Share on Pinterest').'" title="'.translate('Pin It').'"/></a>'.lnk('<img src="/img/share/embed.png" height="30" alt="embed" title="Embed this Instant"/>','photo/'.$this->id.'/embedInstant','',array('data-type'=>'popup')).'</span>'.xdv().lnk('<img id="Photo_'.$this->id.'_instant" title="'.$this->title.'" alt="photo '.$this->id.'" class="photo polaroid loadme nocopy" src="'.HOME.'cdn/instant/'.$s.'/'.$this->id.'.jpg?'.$this->modified.'"/>','photo/'.$this->id.'/instant').xdv();
	}
	
	public function resizeAll(){
		$sizes = self::sizes();
		foreach($sizes as $size=>$attr){
			if(!$this->resize($size)){
				return false;
			}
		}
		return true;
	}
	
	public function resize($s){
		$sizes = self::sizes();
		$size = $sizes[$s];
		if(!class_exists('Imagick')){$this->error('Imagick not installed.',90);return false;}
		$file = new File($this->fileid);
		if(!file_exists($file->path())){
			$this->error('Image file does not exist. '.'('.$file->path().')',90);
			return false;
		}
		$image = new Imagick($file->path());
		if(empty($this->width) || empty($this->height)){
			$this->width = $image->getImageWidth();
			$this->height = $image->getImageHeight();
			if(empty($this->width) || empty($this->height)){
				$this->error('Failed to load image geometry.',95);
				return false;
			}
		}
		$image->setimagebackgroundcolor('#ffffff');
		
		if(isset($size[2])&&$size[2]===true){
			$image->cropThumbnailImage($size[0],$size[1]);
		}
		else{
			if($this->width>$this->height&&$size[1]!=0){$size[0]=round($size[1]*($this->width/$this->height));}
			elseif($size[0]!=0){$size[1]=round($size[0]*($this->height/$this->width));}
			$image->resizeImage($size[0],$size[1],Imagick::FILTER_LANCZOS,0.8);
		}
		if($image->getImageColorspace() != Imagick::COLORSPACE_SRGB){
			$image->setImageColorspace(Imagick::COLORSPACE_SRGB);
		}
		$image->setImageCompression(Imagick::COMPRESSION_JPEG);
		$image->setImageCompressionQuality(90);
		$image->setImagePage(0,0,0,0);
		$image->setImageFormat('jpg');
		$path = 'cdn/'.$this->uid.'/'.strval($s).'/';
		checkPath($path);
		$image->writeImage(ROOT.$path.$this->id.'.jpg');
		return true;
	}
	
	//OBJECT RELATED FUNCTIONS
	protected function postDelete($force=false){
		foreach(self::$sizes as $n=>$desc){
			if(file_exists(ROOT.'cdn/'.$this->uid.'/'.$n.'/'.$this->id.'.jpg')){
				unlink(ROOT.'cdn/'.$this->uid.'/'.$n.'/'.$this->id.'.jpg');
			}
		}
		$addempty='';
		if(!empty($this->kid)){
			$stack = new Stack($this->kid);
			if(count($stack->children())<=1){
				$addempty = '$(".uploadBox").addClass("empty");';
			}
		}
		res('script','$("[id^=photo_'.$this->id.'],[id^=Photo_'.$this->id.']").fadeOut().remove();'.$addempty);
		$col = new Collection('Feedback');
		$col->objectId('=',$this->id,true);
		$col->objectType('=',2,true);
		foreach($col->results as $f){
			$f->delete(true);
		}
		$file = new File($this->fileid);
		if(!$file->delete($force)){
			$this->error('Failed to delete file.',90);
			return false;
		}
		return true;
	}
	
	public function isUniqueTitle($t){
		$q = 'SELECT COUNT(*) FROM `Photo` WHERE `uid` = "'.$this->uid.'" AND `kid` = "'.$this->kid.'" AND `id` != '.intval($this->id).' AND UPPER(title) = "'.strtoupper($t).'";';
		if(LP::$db->customQuery($q,$d)){
			if($d[0]['COUNT(*)'] > 0){
				return false;
			}
			else{
				return true;
			}
		}
		return false;
	}
	
	public static function getUserFile($photo,$stack=0){
		$q = 'SELECT id FROM `Photo` WHERE `uid` = "'.App::$user->id.'" AND (`id` = '.intval($photo).' OR  UPPER(title) = "'.strtoupper($photo).'")';
		if(!empty($kid)){$q.=' AND `kid` = "'.$stack.'"';}
		if(LP::$db->customQuery($q,$d)){
			$d = reset($d);
			return $d['id'];
		}
		return false;
	}
	
	protected function postInsert(){
		if(!empty($this->kid)){
			$stack = new Stack($this->kid);
			if(empty($this->customOrder)){
				$this->customOrder = count($stack->children());
			}
			$this->access=$stack->access;
			$this->update();
		}
		return true;
	}
	
	protected function postUpdate(){
		return true;
	}
}
?>