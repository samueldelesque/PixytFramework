<?php
class Search extends Interfaces{
	public function __call($method,$param){
		return self::directory($method);
	}
	
	private function performSearch($string){
		return 'in construction...';
	}
	
	public function directory($x=NULL){
		return $this->performSearch($x);
		$r = '';
		$maxcols=floor(WINDOWW/250)-1;
		if(!empty($x)){$_REQUEST['x']=$x;}
		if(isset($_REQUEST['x']) && strlen($_REQUEST['x'])>0){
			$searchpattern = str_replace('-',' ',prettyUrlDecode($_REQUEST['x']));
			T::$page['title'] = $searchpattern;
			//if(in_array($searchpattern,parent::$interfaces) || method_exists($this,$searchpattern)){header('Location: '.HOME.$searchpattern);}
			
			$s=microtime(true);
			$matches = 0;
			$buf = '';
			$max = 50;
			if(!empty($searchpattern)){
				//Save Search pattern
				if(preg_match('/^[0-9]$/',$searchpattern)||is_numeric($searchpattern)){$match = intval($searchpattern);}
				else{$match = normalize($searchpattern);}
				if(!empty($match)){
					$query = new Query();
					$matches = $query->count('Search',array('pattern LIKE'=>'%'.$match.'%'));
					if($matches > 0){
						App::$db->increment('Search',array('strid',$match),'counter',1,true);
					}
					else{
						$data = array('pattern'=>$searchpattern,'strid'=>$match,'counter'=>1,'modified'=>time(),'created'=>time());
						if(!LP::$db->insertInto('Search',$data)){
							$this->error('Failed to log search');
						}
					}
				}
				$columns = array();
				$i=0;
				/*
				if(isset($_REQUEST['newEngine'])){
					$q = '	SELECT *, MATCH(title,genre,description,tags) AGAINST ("'.$searchpattern.'") AS score FROM `Photo`
							WHERE MATCH(title,genre,description,tags) AGAINST("'.$searchpattern.'")
							LIMIT 0,100;';
					if(!LP::$db->customQuery($q,$d)){
						$this->error('Failed to do query ['.$q.']',90);
						return;
					}
					foreach($d as $p){
						$photo = new Photo($p['id'],$p);
						if($i>=$maxcols){$i=0;}
						if(!isset($columns[$i])){$columns[$i]='';}
						$columns[$i] .= $photo->preview('250Wide').'score:'.round($p['score'],2);
						$i++;
						$results++;
					}
				}
				else{
					*/
					$photos = new Collection('Photo');
					$photos->access('>=',3,true);
					if(isset(Site::$current->uid)&&Site::$current->uid!=28){$photos->uid('=',Site::$current->uid,true);}
					$photos->any('LIKE',$searchpattern,false);
					$date = strtotime($searchpattern);
					if($date !== false){
						$photos->addFilter('((`created` > '.($date-44000).' AND `created` < '.($date+44000).') OR (`modified` = '.($date-44000).' AND `modified` = '.($date+44000).'))');
					}
					$photos->load($_REQUEST['s']*$max,$max,false);
					$results = $photos->total();
					if(empty($photos->results)){
						res('script','infiniteSearch=false;s=0;');
					}
					else{
						foreach($photos->results as $item){
							if($i>=$maxcols){$i=0;}
							if(!isset($columns[$i])){$columns[$i]='';}
							$columns[$i] .= $item->preview('250Wide');
							$i++;
						}
					}
				//}
				foreach($columns as $id=>$column){
					if(!isset($_REQUEST['loadmore'])){
						$buf .= dv('d250 col c'.$id).$column.xdv();
						T::$jsfoot[] = '
var p = false;
var infiniteSearch = true;
s='.$_REQUEST['s'].';
function infinitySearch(){
if(p===false&&infiniteSearch===true){
if($(window).scrollTop() >= $(window).height()-800){
	p=true;
	s++;
	activateLoadingState();
	$.ajax({
		url: HOME+"search",
		data:{"ajax":1,"datatype":"json","s":s,"x":"'.$searchpattern.'","loadmore":true},
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
setInterval("infinitySearch()",500);
';
					}
					else{
						res('script','$(".c'.$id.'").append("'.addslashes($column).'");s++;');
					}
				}
			}
			else{
				$searchpattern='No search pattern';
			}
			
			$e=microtime(true);
			
			if(empty($buf)){$buf = '<h3 class="red">'.t('No results!').'</h3>';}
			$time = round($e-$s,4);
			$r .= dv('d'.strval((250*$maxcols)).' center');
			$r .= dv('contentBox').'<h1>'.$results.' photos found for "'.htmlspecialchars($searchpattern).'"</h1>'.dv('time lightgrey').'query took '.$time.'s '.xdv().xdv();
			$r .= $buf;
			$r .= xdv();
		}
		else{
			$r .= dv('contentBox').'<h1>'.t('Search results').'</h1>'.'<h2>'.t('No search pattern sent').'</h2>'.xdv();
		}
	}
}
?>