<?php
class Translation extends Object{
	public $uid;
	public $site;
	public $url;
	public $en_GB;
	public $fr_FR;
	public $da_DK;
	public $es_ES;
	public $it_IT;
	public $ru_RU;
	
	public static $languages = array('en_GB'=>'English (UK)','fr_FR'=>'Français (Fr)','es_ES'=>'Español (Es)','da_DK'=>'Dansk (Dk)','ru_RU'=>'Russian (Ru)');
	
	public function descriptor(){
		return array (
			'id'=>'int',
			'uid'=>'int',
			'site'=>'string',
			'url'=>'string',
			'en_GB'=>'string',
			'fr_FR'=>'string',
			'da_DK'=>'string',
			'es_ES'=>'string',
			'it_IT'=>'string',
			'ru_RU'=>'string',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		);
	}
	
	public function translate(){
		if(empty($this->$_SESSION['lang'])){
			$this->$_SESSION['lang'] = 'no translations';
			$c='translation-entry not-translated';
		}
		else{$c='translation-entry';}
		$r .= dv($c,'Translation_'.$this->id).lnk('<img src="/img/delete-grey.png" width="12px" class="icon" alt="delete"/>','#cur',array('delete[Translation]['.$this->id.']'=>true)).' '.dv('original').'&laquo;'.$this->en_GB.'&raquo;'.xdv().dv('translation').'<span class="editable" id="update_Translation_'.$this->id.'">'.$this->$_SESSION['lang'].'</span>'.xdv().xdv();
	}
}