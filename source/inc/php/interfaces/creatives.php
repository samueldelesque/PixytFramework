<?php
class Creatives extends Interfaces{
	public function directory($type=''){
		$r = '';
		$t = $type;
		if(empty($t)){$t = 'creative';}
		T::$page['title']= t('A network of').' '.t($t.'s').' |';
		$m=count(User::$functions);
		foreach(User::$functions as $i=>$f){if($i>0&&$i<4){T::$page['title'].=' '.t($f.'s');if($i!=3){T::$page['title'].=',';}else{T::$page['title'].='...';}}}
		T::$page['description'] = t('Pixyt is the home for image professionals.').' '.t('Find').' ';
		foreach(User::$functions as $i=>$f){if($i>0&&$i<9){T::$page['description'].=' '.t($f.'s');if($i!=8){T::$page['description'].=',';}}}
		$users = new Collection('User');
		$users->load(0,100,true,'validated');
		$r .= dv('d');
		$maxcols=floor(WINDOWW/300);
		$r .= dv('d'.strval((300*$maxcols)).' center');
		$r .= '<h1>'.t('Looking for a').' '.t($t).'?</h1>';
		$r .= '<h2>'.t('There are plenty of image professionals on Pixyt network.').'</h2>';
		$r .= '<h3>'.t('Find the talent you are looking for.').'</h3>';
		$types=array();
		$r .= dv('padded small');
		foreach(User::$functions as $i=>$f){if($f==$t){$c='blue4';}else{$c='grey';}if($i>0&&$i<=12)$r .= ' '.lnk($f.'s','creatives/'.$f,array(),array('class'=>$c));}
		$r .= xdv();
		$col = array();
		for($i=0;$i<$maxcols;$i++){$col[$i]=array();}
		$c=0;
		foreach($users->results as $user){
			if($c>=$maxcols){$c=0;}
			if(!isset($user->data['profile']['function'])){$user->data['profile']['function']=1;}
			if(isset($user->data['profile']['function']) && ($type == '' || $type == User::$functions[$user->data['profile']['function']])){
				$col[$c][] = dv('creative-card post').'<h4>'.lnk($user->picture('text'),'user/'.$user->id).' '.$user->fullName('link').' '.User::$functions[$user->data['profile']['function']].'</h4><p class="small">'.shorten($user->data['profile']['headline'],80).'</p><p class="tiny">'.shorten($user->data['profile']['about'],80).'<p>'.xdv();
			}
			$c++;
		}
		foreach($col as $column){
			$r.=dv('d300 col').implode('',$column).xdv();
		}
		$r .= '<br class="clearfloat"/>'.xdv();
		return $r;
	}
}
?>