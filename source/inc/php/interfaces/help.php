<?php
class Help extends Interfaces{
	public function directory(){
		$r = '';
		$r .= dv('d2 center');
		$r .= '<h1>'.translate('FAQ').'</h1>';
		$col = new Collection('Question');
		$col->load(0,999,true,'id',false);
		foreach($col->results as $q){
			$r .= dv('padded').$q->display('faq').xdv();
		}
		$r .= xdv();
		return $r;
	}
}
?>