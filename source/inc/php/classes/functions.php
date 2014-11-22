<?php

define('LINK_REG','|(http://)?(https://)?+(www.)?[a-z]+(\.)+[a-z]{2,3}(:[0-9]+)?(/.*)?$|i');
define('EMAIL_REG','/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]{2,4}+$/');

//Fix functions not available before PHP 5.5
if(!function_exists('boolval')){
	function boolval($var){
		return (bool)$var;
	}
}
function res($n,$v=''){
	if($n=='script' && !IS_AJAX){
		T::js(addcslashes($v, '"\\/'));
	}
	else{
		T::$body[$n] = $v;
	}
}
function read_json($content,$isFile=true){
	if($isFile)$content = file_get_contents($content);
	return json_decode(trim($content));
}
function display_error($code='404'){
	require_once(ROOT.'inc/php/interfaces/error.php');
	exit(Error::e404());
}
function e404(){
	display_error();
}
function htmlsafefonts(){
	$fonts = array();
	$fonts[] = 'Impact, Charcoal, sans-serif';
	$fonts[] = 'Palatino Linotype, Book Antiqua, Palatino, serif';
	$fonts[] = 'Tahoma, Geneva, sans-serif';
	$fonts[] = 'Century Gothic, sans-serif';
	$fonts[] = 'Lucida Sans Unicode, Lucida Grande, sans-serif';
	$fonts[] = 'Arial Black, Gadget, sans-serif';
	$fonts[] = 'Times New Roman, Times, serif';
	$fonts[] = 'Lucida Console, Monaco, monospace';
	$fonts[] = 'Courier New, Courier, monospace';
	$fonts[] = 'Georgia, Serif';
	return $fonts;
}
function stripFileExtension($name){
	return preg_replace('/\\.[^.\\s]{3,4}$/','',$name);
}
function convertSite($site){
	// $site = json_decode($json);
	$new = new stdClass;

	/*
		array(
			pageid=>array(
			.	x=>(portfolio(2)/shop(3)/folder(1)),contact(4),client(5),custom HTML(6), predifined pages (7)
			.	t=>NULL,[TITLE]
			.	d=>NULL,[DESCRIPTION]
				u=>NULL,[URL]
				c=>array([CONTENT]
					itemid=>array(
						if x==folder - list of pages
						objectType,objectId
					)
				),
				m=>BOOL,[Show in Menu?]
				v=>BOOL [view mode] (0=>vertical,1=>horizontal,2=>thumbs,3=>slideshow)
				i,[CREATIONDATE]
				y[MODIFYDATE]
			)
		)
	*/
	function build_page($page){
		$add = new stdClass;
		$add->title = (string)$page->t;
		$add->description = (string)$page->d;
		$add->visible = (bool)$page->m;
		switch(intval($page->x)){
			case 4:
				$add->template = 'contact';
			break;

			default:
			case 2:
				switch(intval($page->v)){
					case 1:
						$add->template = 'horizontal';
					break;

					case 2:
						$add->template = 'thumbs';
					break;

					case 3:
						$add->template = 'slideshow';
					break;

					default:
					case 0:
						$add->template = 'vertical';
					break;
				}
			break;
		}
		$add->content = array();
		foreach($page->c as $obj){
			$item = new stdClass;
			$item->type = (string)$obj[0];
			$item->id = (int)$obj[1];
			$add->content[] = $item;
		}

		return $add;
	}
	foreach($site as $page){
		if(intval($page->x) == 1){
			$add = new stdClass;
			$add->title = $page->t;
			$add->description = $page->d;
			$add->visible = (bool)$page->m;
			$add->pages = new stdClass;
			foreach($page->c as $p){
				if($p->x == 1){die('Sub sub directory found!');}
				$add->pages->{$p->u} = build_page($p);
			}
			$new->{$page->u} = $add;
		}
		else{
			$new->{$page->u} = build_page($page);
		}
	}
	return $new;
}
function htmllogo($s=30,$color=false){
	$r = dv('','htmllogo','style="width:'.($s*17.5).'px;height:'.($s*8).'px;"');
	for($i=1;$i<=30;$i++){
		switch($i){
			case 1:
				$top = 2;
				$left = 0;
			break;
			case 2:
				$top = 3;
				$left = 0;
			break;
			case 3:
				$top = 4;
				$left = 0;
			break;
			case 4:
				$top = 5;
				$left = 0;
			break;
			case 5:
				$top = 6;
				$left = 0;
			break;
			case 6:
				$top = 7;
				$left = 0;
			break;
			case 7:
				$top = 2;
				$left = 1;
			break;
			case 8:
				$top = 3;
				$left = 2;
			break;
			case 9:
				$top = 4;
				$left = 1;
			break;
			case 10:
				$top = 0;
				$left = 4;
			break;
			case 11:
				$top = 2;
				$left = 4;
			break;
			case 12:
				$top = 3;
				$left = 4;
			break;
			case 13:
				$top = 3;
				$left = 4;
			break;
			case 14:
				$top = 4;
				$left = 4;
			break;
			case 15:
				$top = 2;
				$left = 6;
			break;
			case 16:
				$top = 3;
				$left = 7;
			break;
			case 17:
				$top = 4;
				$left = 6;
			break;
			case 18:
				$top = 2;
				$left = 8;
			break;
			case 19:
				$top = 4;
				$left = 8;
			break;
			case 20:
				$top = 2;
				$left = 10;
			break;
			case 21:
				$top = 3;
				$left = 10.5;
			break;
			case 22:
				$top = 4;
				$left = 11.5;
			break;
			case 23:
				$top = 5;
				$left = 11;
			break;
			case 24:
				$top = 3;
				$left = 12;
			break;
			case 25:
				$top = 2;
				$left = 12.5;
			break;
			case 26:
				$top = 1;
				$left = 14.5;
			break;
			case 27:
				$top = 2;
				$left = 14.5;
			break;
			case 28:
				$top = 3;
				$left = 14.5;
			break;
			case 29:
				$top = 4;
				$left = 14.5;
			break;
			case 30:
				$top = 2;
				$left = 15.5;
			break;
		}
		$r .= '<span style="width:'.(string)$s.'px;height:'.(string)$s.'px;top:'.(string)($top*$s).'px;left:'.(string)($left*$s).'px;" title="'.translate('Pixyt logo pixel').'"></span>';
	}
	$colors = 'var colors = new Array("#ccc","#ddd","#bbb");';
	T::$jsfoot[] = $colors.'
var times = new Array(116,218,329,412,154,738,385,64,853,432,632,146,574,768,869,63,456,943,355,465,234,1002,1412,201);
var i = 0;
$("#htmllogo").find("span").each(function(){
	if(i == 9)
		$(this).hide().css("background-color", "#29ABE2").delay(times[Math.floor(Math.random() * times.length)]).fadeIn("fast");
	else
		$(this).hide().css("background-color", colors[Math.floor(Math.random() * colors.length)]).delay(times[Math.floor(Math.random() * times.length)]).fadeIn("fast");
		
	i++;
});
function intToHex(n){
n = n.toString(16);
if( n.length < 2)
n = "0"+n;
return n;
}
function getHex(r, g, b){
return "#"+intToHex(r)+intToHex(g)+intToHex(b);
}
';
	return $r.xdv();
}
function playerHTML(){
	return '<div id="jPlayer" class="jp-jplayer"></div><div id="playerControls" class="jp-audio"><div class="jp-type-playlist"><div class="jp-gui jp-interface"><a href="javascript:;" class="jp-previous" tabindex="1"><img src="/img/player/previous.png" width="80" height="80" alt="previous"/></a><a href="javascript:;" class="jp-play" tabindex="1"><img src="/img/player/play.png" width="80" height="80" alt="play"/></a><a href="javascript:;" class="jp-pause" tabindex="1"><img src="/img/player/pause.png" width="80" height="80" alt="pause"/></a><a href="javascript:;" class="jp-next" tabindex="1"><img src="/img/player/next.png" width="80" height="80" alt="next"/></a></ul><div class="jp-progress"><div class="jp-seek-bar"><div class="jp-play-bar"></div></div></div><span class="jp-current-time"></span><span class="jp-duration"></span><ul class="jp-toggles"></ul><div class="jp-volume-bar"><div class="jp-volume-bar-value"></div></div></div><div class="jp-playlist"><ul><li></li></ul></div><div class="jp-no-solution"><span>Update Required</span>To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.</div></div></div>';
}
function playerJS($list){
	if(!is_string($list)){$list = json_encode($list);}
	return 'var p;var c=0;$(document).ready(function(){p = new jPlayerPlaylist({jPlayer: "#jPlayer",cssSelectorAncestor: "#playerControls"},'.$list.', {swfPath: HOME+"inc/js/jQuery.jPlayer.2.1.0",supplied: "mp3",wmode: "window"});var c = p.playlist[p.current].id;$("#jPlayer").bind($.jPlayer.event.play, function(event){var id = p.playlist[p.current].id;if(id!=c){c=id;var d = new Object();d.gethtml=0;d["show[Song]["+c+"]"]=1;query(HOME,d);}});});';
}
function facebook($url,$t,$m=''){
	return xtlnk('http://facebook.com/sharer.php?u='.urlencode($url).'&amp;t='.urlencode($t),'<img src="/img/share/facebook.png" alt="'.translate('Share on Facebook').'" title="'.translate('Share on Facebook').'"/>',array('rel'=>'nofollow','class'=>'socialBtn facebook'));
}
function pin($url,$t,$m=''){
	return '<a href="http://pinterest.com/pin/create/button/?url='.urlencode($url).'&amp;media='.urlencode($m).'&amp;description='.urlencode($t).'" target="_blank" rel="nofollow" class="socialBtn pin"><img src="/img/share/pinterest.png" alt="'.translate('Share on Pinterest').'" title="'.translate('Pin It').'"/></a>';
}
function tweet($url,$t,$m=''){
	return '<a href="https://twitter.com/intent/tweet?original_referer='.urlencode(HOME).'&amp;source=tweetbutton&amp;text='.urlencode($t).'&amp;url='.urlencode($url).'" target="_blank" rel="nofollow" class="socialBtn tweet"><img src="/img/share/twitter.png" alt="'.translate('Share on Twitter').'" title="'.translate('Tweet this').'"/></a>';
}
function share($url,$t,$m=''){
	$fb = '<span>'.xtlnk('http://facebook.com/sharer.php?u='.urlencode($url).'&amp;t='.urlencode($t),'<img src="/img/share/facebook.png" alt="'.translate('Share on Facebook').'" title="'.translate('Share on Facebook').'"/>',array('rel'=>'nofollow')).'</span>';
	$pin = '<span><a href="http://pinterest.com/pin/create/button/?url='.urlencode($url).'&amp;media='.urlencode($m).'&amp;description='.urlencode($t).'" target="_blank" rel="nofollow"><img src="/img/share/pinterest.png" alt="'.translate('Share on Pinterest').'" title="'.translate('Pin It').'"/></a></span>';
	$tweet = '<span><a href="https://twitter.com/intent/tweet?original_referer='.urlencode(HOME).'&amp;source=tweetbutton&amp;text='.urlencode($t).'&amp;url='.urlencode($url).'" target="_blank" rel="nofollow"><img src="/img/share/twitter.png" alt="'.translate('Share on Twitter').'" title="'.translate('Tweet this').'"/></a></span>';
	return dv('share').$pin.$fb.$tweet.'<br class="clearfloat"/>'.xdv();
}
function shorten($str,$l=30){
	$r = '';
	$txt = htmltotext($str);
	$t = strlen($txt);
	if($l > $t){$l = $t;}
	$r .= substr($txt,0,$l);
	if($t > $l){$r .= '...';}
	return $r;
}
function swtch($field,$values=array(),$current='',$object='',$id=''){
	$field = explode('_',$field);
	$r = '';
	$c = '';
	$url = HOME.'ajax';
	$n = '';
	if(!empty($object) && !empty($id) && Object::objectExists($object,$id)){
		$n .= 'update['.$object.']['.$id.']';
		foreach($field as $level){$n .= '['.$level.']';}
	}
	else{
		if(count($field==1)){$n.=$field[0];}
		else{$n .= 'glob';}
	}
	
	$name = end($field);
	$isbool = false;
	$curval = false;
	if(count($values==2) && reset($values) == 1 && end($values) == 0){
		$isbool = true;
		$c .= 'bool';
	}
	$r .= dv('btn-group switch '.$c,'','data-name="'.$n.'" data-value="'.$current.'"');
	$i=0;
	foreach($values as $id=>$value){
		if(is_array($value) && isset($value['value'])){
			$value = $value['value'];
			if(isset($value['name'])){$id=$value['name'];}
			else{$id=$value['value'];}
		}
		if($current == $value){
			$c='active ajax btn';
			if($isbool && $value==1){$c.= ' btn-success';}
			elseif($isbool && $value == 0){$c.= ' btn-danger';}
		}
		else{$c='ajax btn';}
		$r .= '<a data-value="'.$value.'" href="'.$url.'?'.$n.'='.$value.'" class="'.$c.'">'.$id.'</a>';
		$i++;
	}
	$r .= xdv();
	return $r;
}
function slct($name,$values=array(),$current='',$object='',$id='',$do='',$holder=''){
	$r = '';
	if(empty($holder)){
		$holder = $name;
	}
	$r .= '<span class="slct" data-name="'.$name.'"><span class="name">'.$holder.'</span><span class="options" style="display:none;">';
	$i=0;
	$o='';
	foreach($values as $n=>$v){
		if(is_array($v)){
			$attr = '';
			foreach($v as $t=>$s){
				$attr .= ' '.$t.'="'.$s.'"';
			}
			$r .= '<a '.$attr.'>'.$n.'</a>';
		}
		else{
			if($n == $i){$n = $v;}
			if(!empty($object) && !empty($id)){$update = '?update['.$object.']['.$id.']['.$name.']='.$n;}
			elseif(!empty($do)){$update = $do;}
			else{$update=$object;}
			if((empty($current) && $i==0) || $current == $n){$class = ' current';}else{$class='';}
			$r .= '<a data-value="'.$v.'" data-gethtml="false" data-name="'.$n.'" href="#'.$update.'" class="'.$name.$class.'">'.$v.'</a>';
		}
		$i++;
	}
	$r .= '</span></span>';
	return $r;
}
function csvToArray($file, $delimiter) { 
  if (($handle = fopen($file, 'r')) !== FALSE) { 
    $i = 0; 
    while (($lineArray = fgetcsv($handle, 4000, $delimiter, '"')) !== FALSE) { 
      for ($j = 0; $j < count($lineArray); $j++) { 
        $arr[$i][$j] = $lineArray[$j]; 
      } 
      $i++; 
    } 
    fclose($handle); 
  } 
  return $arr; 
}
function read_csv($file){
	$keys = array();
	$newArray = array();
	// Do it
	$data = csvToArray($file, ',');

	// Set number of elements (minus 1 because we shift off the first row)
	$count = count($data) - 1;

	//Use first row for names  
	$labels = array_shift($data);  

	foreach ($labels as $label) {
		$keys[] = $label;
	}

	// Add Ids, just in case we want them later
	$keys[] = 'id';

	for ($i = 0; $i < $count; $i++) {
		$data[$i][] = $i;
	}

	// Bring it all together
	for ($j = 0; $j < $count; $j++) {
		$d = array_combine($keys, $data[$j]);
		$newArray[$j] = $d;
	}
	return $newArray;
}
function frmt($str){
	if(strlen($str) != strlen(strip_tags($str))){return $str;}
	if(!is_string($str)){return $str;}
	$words = str_replace(PHP_EOL,PHP_EOL.' ',htmltotext($str));
	$words = explode(' ',$words);
	$r = '';
	foreach($words as $word){
		if(isEmail($word)){
			if(App::$user->id!=0)
				$r.=xtlnk($word,$word,array('rel'=>'me','target'=>'_self')).' ';
			else
				$r.=str_replace('@',' at ',$word);
		}
		elseif(isLink($word)){$r.=xtlnk(makeUrl($word),$word,array('rel'=>'external nofollow')).' ';}
		elseif(substr($word,0,1)=='#'){$r.=lnk($word,'search/'.substr($word,1,strlen($word))).' ';}
		elseif(substr($word,0,1)=='@'){$r.=lnk($word,'search/'.substr($word,1,strlen($word)),array('map'=>1)).' ';}
		else{$r.=$word.' ';}
	}
	return nl2br($r);
}
function price2str($price){
	return sprintf('%.2f€',intval($price)/100);
}
function btn($name,$url='',$extra=array()){
	$extras = '';
	foreach($extra as $n=>$v){
		$extras .= ' '.$n.'="'.$v.'"';
	}
	return '<a href="'.$url.'"'.$extras.'>'.$name.'</a>';
}
function lnk($name,$url='',$params=array(),$extra=array(),$give_href=false){
	$allowedLinkAttr = array('id','class','lang','style','title','dir','accesskey','target','rel','rev','onclick','onmouseover');
	
	$useAjax = false;
	if(isset($extra['ajax']) && $extra['ajax']==true){$useAjax=true;}
	//if(!is_object(LP::$urlhandler)){if($give_href){return $url;}return '<a href="'.HOME.$url.'">'.$name.'</a>';}
	if(is_bool($url)){
		if($url === true){
			$url = $_REQUEST['url'];
			if(isset($extra['ssl'])&&$extra['ssl']==true){
				$href = 'https://'.HOST.'/';
			}
			else{
				$href='/';
			}
		}
		else{
			$href = 'javascript:;';
			$url = '';
		}
	}
	elseif($url == '#cur'){
		$url = substr($_SERVER['REQUEST_URI'],1);
		$href='/';
		$useAjax = true;
	}
	elseif(isset($url[0]) && $url[0] == '#'){
		$useAjax=true;
		$href = '';
		$url = substr($url,1);
		if(empty($url)){$url = 'ajax';}
	}
	else{
		if(isset($extra['ssl'])&&$extra['ssl']==true){
			$extra['data-ssl'] = '1';
			$extra['data-reload'] = '1';
			$href = '/';
		}
		else{
			$href='/';
		}
	}
	
	$parts = explode('?',$url);
	$url = $parts[0];
	if(is_bool($params) && $params === true && isset($parts[1])){$params = array_merge($params,urlToVars($parts[1]));}
	
	if(empty($params)){$params=array();}
	if(defined('FULL_AJAX') && !isset($extra['data-type'])){
		//if($href!='#'){$href .= '#';}
		$useAjax=true;
		$extra['data-containerId'] = 'mainColumn';
		$extra['data-gethtml'] = 'true';
	}
	$href .= $url;
	if(!empty($params)){
		$href .= '?';
		$i = 0;
		foreach($params as $n=>$v){
			if($i!=0){$href .= '&';}
			$href .= $n.'='.prettyUrl($v);
			$i++;
		}
	}
	$extras = '';
	if($useAjax){
		if(isset($extra['class'])){$extra['class'] .= ' ajax';}
		else{$extra['class'] = 'ajax';}
	}
	foreach($extra as $n=>$v){
		if(in_array($n,$allowedLinkAttr)){
			$extras .= ' '.$n.'="'.$v.'"';
		}
		elseif(preg_match('/^data-*/',$n)){
			$extras .= ' '.$n.'="'.$v.'"';
		}
	}
	if($give_href){return $href;}
	return '<a href="'.$href.'"'.$extras.'>'.$name.'</a>';
}
function xtlnk($url,$name='',$extra=array()){
	$allowedLinkAttr = array('id','class','lang','style','title','dir','accesskey','target','rel','rev');
	$parts = explode('?',$url);
	$url = $parts[0];
	if(!isset($extra['target'])){$extra['target'] = '_blank';}
	if(empty($name)){$name = $url;}
	if(isset($parts[1])){$url.='?'.$parts[1];}
	$extras = '';
	foreach($extra as $n=>$v){
		if(in_array($n,$allowedLinkAttr)){
			$extras .= ' '.$n.'="'.$v.'"';
		}
		elseif(preg_match('/^data-*/',$n)){
			$extras .= ' '.$n.'="'.$v.'"';
		}
	}
	if(isEmail($url)){$url = 'mailto:'.$url;}
	return '<a href="'.$url.'" '.$extras.'>'.$name.'</a>';
}
function dv($class='',$id='',$other=''){
	if(!empty($class)){$class = ' class="'.$class.'"';}
	if(!empty($id)){$id = ' id="'.$id.'"';}
	if(!empty($other)){$other = ' '.$other;}
	//T::$dvs++;
	//if(DEBUG){return '<div'.$class.$id.$other.'>'.PHP_EOL;}
	return '<div'.$class.$id.$other.'>';
}
function xdv(){
	//T::$dvs--;
	//if(DEBUG){return '</div>'.PHP_EOL;}
	return '</div>';
}
function littlegraph($values,$options=array()){
	//sparkline graph
	if(!is_array($values)){$values = array($values);}
	$allowed = array('type','barWidth','zeroAxis','barColor','negBarColor','height','width','stackedBarColor');
	$default = array('type'=>'bar','barWidth'=>25,'zeroAxis'=>false,'barColor'=>'#29ABE2','stackedBarColor'=>array('#444444','#555555','#666666','#777777'));
	foreach($options as $opt=>$val){
		if(in_array($opt, $allowed)){$default[$opt] = $val;}
	}
	$id = 'graph_'.substr(md5(randStr()),0,6);
	T::js('$("#'.$id.'").sparkline('.json_encode($values).','.json_encode($default).');');
	return dv('littlegraph',$id).xdv();
}
function graph($values,$options=array()){
	//highchart graph
	if(!is_array($values)){$values = array($values);}
	$allowed = array('type','barWidth','zeroAxis','barColor','negBarColor','height','width','stackedBarColor');
	$default = array('type'=>'bar','barWidth'=>25,'zeroAxis'=>false,'barColor'=>'#29ABE2','stackedBarColor'=>array('#444444','#555555','#666666','#777777'));
	foreach($options as $opt=>$val){
		if(in_array($opt, $allowed)){$default[$opt] = $val;}
	}
	$default['series'] = $values;
	$id = 'graph_'.substr(md5(randStr()),0,6);
	T::$js[] = '$("#'.$id.'").highcharts('.json_encode($default).');';
	return dv('highchart',$id).xdv();
}
function css2array($css){
	$results = array();
	preg_match_all('/(.+?)\s?\{\s?(.+?)\s?\}/', $css, $matches);
	foreach($matches[0] AS $i=>$original)
	foreach(explode(';', $matches[2][$i]) AS $attr)
	if (strlen(trim($attr)) > 0){
		list($name, $value) = explode(':', $attr);
		$results[$matches[1][$i]][trim($name)] = trim($value);
	}
	return $results;
}
function prettyNumber($b,$float=1){
	if($b>1000000){
		return strval(round($b/1000000,$float)).'M';
	}
	elseif($b > 1000){
		return strval(round($b/1000,$float)).'K';
	}
	else{
		return strval(round($b,$float)).'';
	}
}
function prettyBytes($b,$float=2){
	if($b >= 1099511627776){
		return strval(round($b/1099511627776,$float)).'TB';
	}
	elseif($b >= 1073741824){
		return strval(round($b/1073741824,$float)).'GB';
	}
	elseif($b >= 1048576){
		return strval(round($b/1048576,$float)).'MB';
	}
	elseif($b >= 1024){
		return strval(round($b/1024,$float)).'KB';
	}
	else{
		return strval(round($b,2)).'B';
	}
}
function prettyMB($b,$float=2){
	if($b >= 1024000){
		return strval(round($b/1024000,$float)).'TB';
	}
	elseif($b >= 1024){
		return strval(round($b/1024,$float)).'GB';
	}
	else{
		return strval(round($b,2)).'MB';
	}
}
function prettyTime($inputtime){
	$t = time() - $inputtime;
	if($t < 0){
		$time = translate('in').' '.round(((-1)*$t/86400)).' '.translate('days');
	}
	elseif($t < 60){
		$time = translate('a few seconds ago');
	}
	elseif($t > 30 && $t < 60){
		$time = translate('a half minute ago');
	}
	elseif($t > 60 && $t < 120){
		$time = translate('a minute ago');
	}
	elseif($t > 120 && $t < 180){
		$time = translate('two minutes ago');
	}
	elseif($t > 180 && $t < 1800){
		$time = translate('a few minutes ago');
	}
	elseif($t > 1800 && $t < 3600){
		$time = translate('half an hour ago');
	}
	elseif($t > 3600 && $t < 86400){
		$time = translate('a few hours ago');
	}
	elseif($t > 86400 && $t < 172800){
		$time = translate('yesterday');
	}
	elseif($t > 172800 && $t < 259200){
		$time = translate('the day before yesterday');
	}
	else{
		//$time = translate(round($t/31104000).' years ago');
		$time = translate('on').' '.date('d/m/Y',$inputtime);
	}
	return $time;
}
function localisedDate($format, $timestamp){
	if($format = 'normaldate'){
		return date('d/m/Y', $timestamp);
	}
}
function rmvmrkr($str){
	return trim(preg_replace('/\s*\([^)]*\)/', '', preg_replace('/\s*\[[^]]*\]/', '', $str)));
}
function stripAccents($string){
	return strtr($string,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}

function gcd($a, $b){
    if ($a == 0 || $b == 0)
        return abs( max(abs($a), abs($b)) );

    $r = $a % $b;
    return ($r != 0) ?
        gcd($b, $r) :
        abs($b);
}
function htmlMail($title,$content){//Content must be an array like content=>array(line1=>array(abc,def),line2..)
	$table = new Table('email');
	foreach($content as $line){
		$table->addLine($line);
	}
	return '<html><head><title>'.$title.'</title><style type="text/css">'.file_get_contents(ROOT.'assets/css/mail.css').'</style></head><body>'.$table->returnContent().'</body></html>';
}
function templateEmail($to_email, $from_user, $from_email, $subject, $template,$data,$getPreview=false){

	$email_from = "=?UTF-8?B?".base64_encode($from_user)."?=";
	$email_subject = "=?UTF-8?B?".base64_encode($subject)."?=";

	$m = new Handlebars\Handlebars;
	$path = ROOT.$template;
	if($template==NULL || !file_exists($path)){
		return false;
	}
	$headers = 'From: '.$email_from.' <'.$from_email.'>'.PHP_EOL.'MIME-Version: 1.0'.PHP_EOL .'Content-type: text/html; charset=UTF-8'.PHP_EOL;
	$message = $m->render(file_get_contents($path),$data);

	if($getPreview){die($message);}
	else{
		$uid = 0;
		if(Object::objectExists('User',$to_email,'email',$id)){
			$user = new User($id);
			$uid = $user->id;
		}
		$log = new Message();
		$log->uid = $uid;
		$log->to_email = $to_email;
		$log->content = $message;
		$log->from_email = $from_email;
		$log->title = $subject;
		$log->via = 'log';
		$log->format = 'html';
		$log->insert();
		$message = str_replace('</body></html>','<img src="http://'.HOST.'/message/'.$log->id.'/?read=1" height="1" width="1"/></body></html>',$message);
		if(!mail($to_email, $subject, $message, $headers)){
			Pixyt::addError('Failed to send email: to='.$to_email.' subject='.$email_subject.' headers='.$headers.' message='.$message,98,false);
			return false;
		}
		return true;
	}
}
function sendMail($to_email, $from_user, $from_email, $subject = '(No subject)', $message = '',$getPreview=false,$via='form'){
	$email_from = "=?UTF-8?B?".base64_encode($from_user)."?=";
	$email_subject = "=?UTF-8?B?".base64_encode($subject)."?=";

	$headers = 'From: '.$email_from.' <'.$from_email.'>'.PHP_EOL.'MIME-Version: 1.0'.PHP_EOL .'Content-type: text/html; charset=UTF-8'.PHP_EOL;
	if(is_array($message)){
		$message = htmlMail($email_subject,$message);
		$message = str_replace('https','http',$message);
		$message = str_replace('http://pixyt.com:443','http://pixyt.com',$message);
		$message = str_replace('//pix.yt/','http://pix.yt/',$message);
		$format = 'html';
	}
	else{$format = 'text';}
	
	if($getPreview){die($message);}
	else{
		$uid = 0;
		if(Object::objectExists('User',$to_email,'email',$id)){
			$user = new User($id);
			$uid = $user->id;
		}
		$log = new Message();
		$log->uid = $uid;
		$log->to_email = $to_email;
		$log->content = $message;
		$log->from_email = $from_email;
		$log->title = $subject;
		$log->via = 'log';
		$log->format = $format;
		$log->insert();
		$message = str_replace('</body></html>','<img src="http://'.HOST.'/message/'.$log->id.'/?read=1" height="1" width="1"/></body></html>',$message);
		if(!mail($to_email, $subject, $message, $headers)){
			Pixyt::addError('Failed to send email: to='.$to_email.' subject='.$email_subject.' headers='.$headers.' message='.$message,98,false);
			return false;
		}
		return true;
	}
}
function isNumber($n){
	if(preg_match('/^[0-9]{1,}$/',$n)){return true;}
	return false;
}
function chk($var,$type='na'){
	if(!isset($var)){return false;}
	if(empty($var)){return false;}
	if($type !== 'na'){
		switch($type){
			case int:
				if(is_int($var)){return true;}
			break;
			case string:
				if(is_string($var)){return true;}
			break;
			case array():
				if(is_array($var)){return true;}
			break;
			case object:
				if(is_object($var)){return true;}
			break;
		}
		return false;
	}
	return true;
}
function htmltotext($html){
	return strip_tags($html); 
}
function killScript($msg=''){
	if(!is_null($e = error_get_last()) === false && empty($msg)){return false;}//AN EMPTY ERROR SEEMS TO BE GIVEN ANY TIME. FIX IT THEN REMOVE THIS
	if(!empty($msg)){
		$content = $msg;
	}
	elseif(is_null($e = error_get_last()) === false){
		$content = $e['message'].' in '.$e['file'].' on line '.$e['line'];
	}
	else{
		$content = 'no information given';
	}
	LP::addError($content,100);
}
function normalize($k=''){
	$pattern = array("'é'", "'è'", "'ë'", "'ê'", "'É'", "'È'", "'Ë'", "'Ê'", "'á'", "'à'", "'ä'", "'â'", "'å'", "'Á'", "'À'", "'Ä'", "'Â'", "'Å'", "'ó'", "'ò'", "'ö'", "'ô'", "'Ó'", "'Ò'", "'Ö'", "'Ô'", "'í'", "'ì'", "'ï'", "'î'", "'Í'", "'Ì'", "'Ï'", "'Î'", "'ú'", "'ù'", "'ü'", "'û'", "'Ú'", "'Ù'", "'Ü'", "'Û'", "'ý'", "'ÿ'", "'Ý'", "'ø'", "'Ø'", "'œ'", "'Œ'", "'Æ'", "'ç'", "'Ç'");
	$replace = array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E', 'a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'i', 'i', 'i', 'I', 'I', 'I', 'I', 'I', 'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U', 'y', 'y', 'Y', 'o', 'O', 'a', 'A', 'A', 'c', 'C');
	$k = preg_replace($pattern, $replace, $k);
    $k = preg_replace("/[^a-zA-Z0-9]/", "", $k);
	return strtolower($k);
}
function aBtn($name,$id){
	return '<a href="#" id="'.$id.'">'.$name.'</a>';
}
function errorHandler(){
	$e = error_get_last();
	if(!empty($e)){
		LP::addError('PHP Error: '.$e['message'].' in file:'.$e['file'].' line '.$e['line']);
	}
    return true;
}
function randStr($length = 10, $chars = 'abcdegineopstuv'){//ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890
	$chars_length = (strlen($chars) - 1);
	$string = $chars{rand(0, $chars_length)};
	for ($i = 1; $i < $length; $i = strlen($string)){
		$r = $chars{rand(0, $chars_length)};
		if ($r != $string{$i - 1}) $string .=  $r;
	}
    return $string;
}
function randAlnumStr($length = 10, $chars = '//ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'){
	$chars_length = (strlen($chars) - 1);
	$string = $chars{rand(0, $chars_length)};
	for ($i = 1; $i < $length; $i = strlen($string)){
		$r = $chars{rand(0, $chars_length)};
		if ($r != $string{$i - 1}) $string .=  $r;
	}
	return $string;
}
function charsMap($str){
	$l = strlen($str);
	$r = array();
	for ($i = 1; $i < $l; $i++){
		$r[] = $str{$i};
	}
	return $r;
}
function objectToArray($d) {//StdClass to object
	if(is_object($d)) {
		$d = get_object_vars($d);
	}
	if(is_array($d)){
		return array_map(__FUNCTION__, $d);
	}
	else {
		return $d;
	}
}
function encrypt($string){
	$chars = str_split($string);
	$i=0;$r='';
	foreach($chars as $char){if($i!==0){$r.='|';}$r.=keyKrypt(hexdec(bin2hex($char)));$i++;}
	return $r;
}
function keyKrypt($number){
	if(App::$user->access>80 || !isset($_SESSION['SESSIONKEY'])){return $number;}
	$key = hexdec(bin2hex($_SESSION['SESSIONKEY']));
	$key = $key/10e79;
	return pow(($number)/$key,1/2);
}
function keyDeKrypt($number){
	if(!isset($_SESSION['SESSIONKEY']) || !is_array($_SESSION['hash'])){return false;}
	else return $_SESSION['hash'][$number];
}
function prettyUrl($string){
	return urlencode($string);
	$ugly=array(',','.','_',' ','@','%','(',')','&');
	$pretty=array('-coma-','-dot-','-underslash-','-','-arobase-','-percent-','-openparenthesis-','-closeparenthesis-','-amp-');
	return urlencode(str_replace($ugly,$pretty,$string));
}
function prettyUrlDecode($string){
	return urldecode($string);
	$ugly=array('-coma-','-dot-','-underslash-','-','-arobase-','-percent-','-openparenthesis-','-closeparenthesis-','-amp-');
	$pretty=array(',','.','_',' ','@','%','(',')','&');
	return urldecode(str_replace($ugly,$pretty,$string));
}
function sanitize($str){
	return htmlspecialchars($str);
}
function download_file($fullPath){
	if(headers_sent()){
		die('Headers Sent');
	}
	if(ini_get('zlib.output_compression')){
		ini_set('zlib.output_compression', 'Off');
	}
	// File Exists?
	if(file_exists($fullPath)){
		// Parse Info / Get Extension
		$fsize = filesize($fullPath);
		$path_parts = pathinfo($fullPath);
		$ext = strtolower($path_parts["extension"]);
		
		// Determine Content Type
		switch ($ext) {
			case "pdf": $ctype="application/pdf"; break;
			case "exe": $ctype="application/octet-stream"; break;
			case "zip": $ctype="application/zip"; break;
			case "doc": $ctype="application/msword"; break;
			case "xls": $ctype="application/vnd.ms-excel"; break;
			case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
			case "gif": $ctype="image/gif"; break;
			case "png": $ctype="image/png"; break;
			case "jpeg":
			case "jpg": $ctype="image/jpg"; break;
			default: $ctype="application/force-download";
		}
		header("Pragma: public"); // required
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); // required for certain browsers
		header("Content-Type: $ctype");
		header("Content-Disposition: attachment; filename=\"".basename($fullPath)."\";" );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".$fsize);
		//ob_clean();
		flush();
		readfile($fullPath);
	}
	else{
		die('File Not Found');
	}
}
function checkPath($path){
	$path = str_replace(ROOT,'',$path);
	if(substr($path,0,1) == '/'){$path = substr($path,1,0);}
	$pathParts = explode('/',$path);
	$checked = ROOT;
	$i = 0;
	foreach($pathParts as $part){
		if(!is_dir($checked.'/'.$part)){
			if(mkdir($checked.'/'.$part)){
				$checked .= '/'.$part;
			}
			else{
				return false;
			}
		}
		else{
			$checked .= '/'.$part;
		}
		$i++;
	}
	if(is_dir($checked)){
		return true;
	}
	return false;
}
function isEmail($email){
	if(preg_match(EMAIL_REG, $email)){
		return true;
	}
	return false;
}
function isLink($url){
	return preg_match(LINK_REG, $url);
}
function makeUrl($url){
	if(preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url)){
		return $url;
	}
	elseif(preg_match('|^mailto:[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$|i', $url)){
		return $url;
	}
	return 'http://'.$url;
}
function translate($str){
	return t($str);
}
function t($str,$replacements=array(),$lang='default'){
	if(!is_string($str)){return $str;}
	if(
		(
			(isset($_SESSION['addtranslations']) && $_SESSION['addtranslations'] === true)
		) && 
		DB_STATUS === true
	){
		if(!App::$db->exists('Translation',array('en_GB'=>$str))){
			$t = new Translation();
			$t->en_GB = $str;
			$t->site = HOST;
			$t->url = $_REQUEST['url'];
			$t->insert();
		}
	}
	$raw = gettext($str);
	if(!is_array($replacements)){$replacements = array($replacements);}
	foreach($replacements as $i=>$rep){
		$raw = str_replace('{$'.($i+1).'}',$rep,$raw);
	}
	return $raw;
}
function arrayGetKey($k,$a){
	if(!$k){return $a;}
	if(!isset($a[$k])){return NULL;}
	return $a[$k];
}
function replace_unicode_escape_sequence($match) {
	return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
}
function chkutf8($str) { 
	$len = strlen($str); 
	for($i = 0; $i < $len; $i++){ 
		$c = ord($str[$i]); 
		if ($c > 128) { 
			if (($c > 247)) return false; 
			elseif ($c > 239) $bytes = 4; 
			elseif ($c > 223) $bytes = 3; 
			elseif ($c > 191) $bytes = 2; 
			else return false; 
			if (($i + $bytes) > $len) return false; 
			while ($bytes > 1) { 
				$i++; 
				$b = ord($str[$i]); 
				if ($b < 128 || $b > 191) return false; 
				$bytes--; 
			} 
		} 
	} 
	return true; 
}

/**
 * Generates a unique hash that will be used for authentication in account/messages for a given user
 */
function generateMessagesKey($userid)
{
	return sha1(sha1($userid) . randAlnumStr(40));
}
?>