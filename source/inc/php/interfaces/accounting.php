<?php
class Accounting extends Interfaces{
	public function nomenclature(){
		$transaction = new Transaction();
		T::$body[] = Transaction::nommenclatureFrancaise();
	}
	
	public function directory(){
		T::$page['title'] = translate('Accounting');
		T::$body[] = dv('head').lnk(translate('Add payment'),'transaction/create',array(),array('class'=>'btn','data-type'=>'popup')).xdv();
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
		T::$body[] = dv('splitRight').dv('contentBox').Transaction::grandTotal().xdv().xdv();
	}
}
?>