<?php
class Question extends Object{
	public $uid;
	public $relate;
	public $question;
	public $answer;
	public $tags=array();
	public $hits;
	
	protected static function publicFunctions(){
		return array(
			'fullView'=>true,
			'preview'=>true,
			'directory'=>true,
			'faq'=>true,
		);
	}
	
	protected static function ownerFunctions(){
		return array(
			'edit'=>true,
			'editPreview'=>true,
			'answerQuestion'=>true,
		);
	}
	
	public function descriptor(){
		return array (
			'id'=>'int',
			'uid'=>'int',
			'relate'=>'int',
			'question'=>'string',
			'answer'=>'string',
			'tags'=>'string',
			'hits'=>'int',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		);
	}
	
	public $requiredFields = array('question');
	
	public function validateData($n,$v){
		switch ($n){
			case 'id':
			case 'uid':
			case 'created':
			case 'modified':
				return true;
			break;
			
			case 'isQuestion':
				$this->$n = (bool)$v;
				return true;
			break;
			
			case 'question':
			case 'answer':
				$this->$n=sanitize($v);
				return true;
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
			case 'tag':
				$tags = explode(',',$v);
				foreach($tags as $tag){
					$tag = sanitize($tag);
					$id = md5($tag);
					if(!isset($this->tags[$id])){
						$this->tags[$id] = $tag;
						res('script','$("#'.$this->className.'_'.$this->id.'_tags").append("'.addslashes($this->tag($tag,$id)).'");activate($("#tags"));$(".xbox").slideUp();');
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
		User::preload($this->uid);
	}
	
	//GLOBAL FUNCTIONS
	public function summary($l=0){
		$r = '';
		$txt = htmltotext($this->answer);
		$t = strlen($txt);
		if($l > $t){$l = $t;}
		$r .= substr($txt,0,$l);
		if($t > $l){$r .= '...';}
		return $r;
	}
	
	protected function answerQuestion(){
		$form = new Form('Question',$this->id);
		$form->answer('textarea',$this->answer);
		$form->{'save'}('submit');
		return dv('question','Question_'.$this->id.'_answerQuestion').'<h3><span class="editable" id="update_Question_'.$this->id.'_question">'.$this->question.'</span></h3>'.$form->returnContent().xdv();
	}
	
	protected function faq(){
		return dv('question','Question_'.$this->id.'_faq').'<h3>'.translate($this->question).'</h3><p class="padded">'.frmt(translate($this->answer)).'</p>'.xdv();
	}
	
	protected function preview(){
		return dv('question','Question_'.$this->id.'_preview').'<p class="padded">'.$this->summary(200).'</p>'.xdv();
	}
	
	protected function tableData(){
		return array($this->summary(40));
	}
	
	
	//PUBLIC FUNCTIONS
	public function directory(){
		$c = new Collection('Question');
		$c->isQuestion=1;
		return $c->content('preview');
	}
}
?>