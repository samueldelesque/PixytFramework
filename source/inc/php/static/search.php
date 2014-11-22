<?php
if(IS_AJAX){
	require_once(ROOT.'local/config.php');
	T::$actionBar[] = xtlnk('X','javascript:document.getElementById(\'glob_x\').focus();document.getElementById(\'glob_x\').value = \'\';document.getElementById(\'glob_x\').blur();');}
if(isset($_REQUEST['x']) && strlen($_REQUEST['x'])>0){
	$searchpattern = str_replace('-',' ',prettyUrlDecode($_REQUEST['x']));
	T::$page['title'] = $searchpattern;
	
	$s=microtime(true);
	$matches = 0;
	$buf = '';
	$max = 50;
	if(!empty($searchpattern)){
		$photos = new Collection('Photo');
		$photos->access('=',3,true);
		if(isset($_REQUEST['uid'])){$photos->uid('=',$_REQUEST['uid'],true);}
		$photos->any('LIKE',$searchpattern,false);
		if(App::$user->id==1){
			$photos->load($_REQUEST['s']*$max,$max,false);
			$columns = array();
			$i=0;
			if(empty($photos->results)){
				$columns[0] = dv('preview').'No photos'.xdv();
			}
			else{
				foreach($photos->results as $item){
					if($i>=5){$i=0;}
					if(!isset($columns[$i])){$columns[$i]='';}
					$columns[$i] .= $item->preview('250Wide');
					$i++;
				}
			}
			foreach($columns as $id=>$column){
				if(!isset($_REQUEST['loadmore'])){
					$buf .= dv('d5 col c'.$id).$column.xdv();
				/*	T::$js[] = '
var p = false;
var infinite = true;
s='.$_REQUEST['s'].';
function infinity(){
if(p===false&&infinite===true){
	if($(window).scrollTop() >= ($(document).height() - $(window).height())*0.7){
		p=true;
		s++;
		activateLoadingState();
		$.ajax({
			url: HOME+"inc/php/static/search.php?loadmore=true&x='.$searchpattern.'",
			data:{"ajax":1,"datatype":"json","s":s},
			dataType: "json",
			success: function(data){
				deactivateLoadingState();	
				$(".loading").fadeOut();
				if(data.error != null){alert(data.error);}
				else if(data[0] != null && data[0].error != null){alert(data[0].error);}
				if(data.msg != null){LPalert(data.msg);}
				if(data.script != null){jQuery.globalEval(data.script);}
				activate();
				p=false;
				return true;
			}
		});
	}
}
}
setInterval("infinity()",500);
';
*/
				}
				else{
					res('script','$(".c'.$id.'").append("'.addslashes($column).'");');
				}
			}
		}
		else{
			$buf .= $photos->content('preview',$_REQUEST['s'],$max,false);
		}
	}
	else{
		$searchpattern='No search pattern';
	}
	
	$e=microtime(true);
	
	if(empty($buf)){$buf = '<h3 class="red">'.translate('No results!').'</h3>';}
	$time = round($e-$s,4);
	//'<h2>'.$matches.' '.translate('results in').' '.$time.'s '.translate('for').' <span class="red italic">'.$searchpattern.'</span></h2>'
	T::$body[] = dv('contentBox').'<h1>'.htmlspecialchars($searchpattern).'</h1>'.dv('time lightgrey').'query took '.$time.'s '.xdv().xdv();
	T::$body[] = dv('contentBox').$buf.'<br class="clearfloat"/>'.xdv();
}
else{
	T::$body[] = dv('contentBox').'<h1>'.translate('Search results').'</h1>'.'<h2>'.translate('No search pattern sent').'</h2>'.xdv();
}
if(isset($_REQUEST['map'])){
	T::$body[] = '<br class="clearfloat"/>'.dv('padder').'<h2>'.translate('The map').'</h2><iframe width="100%" height="500" id="map_canvas" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.fr/maps?f=q&amp;source=s_q&amp;hl=fr&amp;geocode=&amp;q='.$searchpattern.'&amp;aq=0&amp;oq='.$searchpattern.'&amp;ie=UTF8&amp;hq='.$searchpattern.'&amp;t=m&amp;output=embed"></iframe>'.xdv();
}
if(IS_AJAX){
	if(!isset($_REQUEST['outputId'])){$page = new T();echo $page->pageContent();}
	elseif($_REQUEST['outputId'] == 'mainColumn'){$page = new T();echo $page->mainColumn();}
	elseif($_REQUEST['outputId'] == 'results'){$page = new T();echo $page->pageContent();}
}
?>