<?php
class Stacks extends Interfaces{
	public function directory(){
		return $this->all();
	}
	
	public function all(){
		$stacks = new Collection('Stack');
		if(isset($_REQUEST['uid'])){
			if($_REQUEST['uid'] == 'me' && $_SESSION['uid'] != 0){$_REQUEST['uid'] = $_SESSION['uid'];}
			$stacks->where('uid',$_REQUEST['uid']);
		}
		
		$stacks->load();
		return $stacks->data();
	}
	
	public function create(){
		$r = '';
		if(isset($_REQUEST['title'])){
			$title = $_REQUEST['title'];
		}
		if(!empty($title)){
			$stack = new Stack();
			if(!$stack->validateData('title',$title)){
				Msg::notify(translate('A stack with that name already exists. Please use unique titles.'));
			}
			else{
				if($stack->insert()){
					if(IS_AJAX){
						res('script','window.location.href="'.HOME.'stack/'.$stack->id.'/edit";');
					}
					else{
						header('location: '.HOME.'stack/'.$stack->id.'/edit');
					}
				}
				else{
					Msg::notify('Failed to create stack.');
				}
			}
			
		}
		else{
			$r .= '<h2>'.translate('Create a new stack').'</h2>';
			$form = new Form('','','stacks/create',array('ajax'=>true,'class'=>'inline'));
			$form->input('title');
			$form->button(t('Create'));
			$r .= $form->content();
		}
		return $r;
	}
}
?>