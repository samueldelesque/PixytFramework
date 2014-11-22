<?php
class Transactions extends Interfaces{
	public function directory(){
		return $this->all();
	}
	
	public function all(){
		$Transactions = new Collection('Transaction');
		if(isset($_REQUEST['uid'])){
			if($_REQUEST['uid'] == 'me' && $_SESSION['uid'] != 0){$_REQUEST['uid'] = $_SESSION['uid'];}
			$Transactions->where('uid',$_REQUEST['uid']);
		}
		if(isset($_REQUEST['date'])){
			$Transactions->where('date','LIKE "'.$_REQUEST['date'].'%"');
		}
		$Transactions->load();
		return $Transactions->data();
	}
	
	public function create(){
		$r = '';
		if(isset($_REQUEST['title'])){
			$title = $_REQUEST['title'];
		}
		if(!empty($title)){
			$transaction = new Transaction();
		
			if($transaction->insert()){
				if(IS_AJAX){
					res('script','window.location.href="'.HOME.'transaction/'.$transaction->id.'/edit";');
				}
				else{
					header('location: '.HOME.'transaction/'.$transaction->id.'/edit');
				}
			}
			else{
				Msg::notify('Failed to create transaction.');
			}
			
		}
		else{
			$r .= '<h2>'.translate('Create a new transaction').'</h2>';
			$form = new Form('','','transactions/create',array('ajax'=>true,'class'=>'inline'));
			$form->input('title');
			$form->button(t('Add new'));
			$r .= $form->content();
		}
		return $r;
	}
}
?>