<?php
class Translate extends Pixyt{
	public static $langs = array(1=>'en_GB',2=>'fr_FR',3=>'es_ES',4=>'da_DK',5=>'ru_RU');
	public static $languages = array('en_GB'=>'English (UK)','fr_FR'=>'Français (Fr)','es_ES'=>'Español (Es)','da_DK'=>'Dansk (Dk)','ru_RU'=>'Russian (Ru)');
	public static $flags = array('fr_FR'=>'France.png','en_GB'=>'United Kingdom(Great Britain).png','ru_RU'=>'Russian Federation.png','es_ES'=>'Spain.png','da_DK'=>'Denmark.png');
	public static $checked = false;
	public static $active=array();
	private $lang='en_GB';
	
	//When considering as object
	public $id;
	
	public static function languages($k=NULL){return arrayGetKey($k,self::$languages);}
	
	function __construct($lang=''){
		if(is_numeric($lang)){
			//load like object
			$this->id=$lang;
			return true;
		}
		$lang = str_replace('subslash','_',$lang);
		if(empty($lang)){$lang=$_SESSION['lang'];}
		if(!isset(self::$languages[$lang])){$this->error('Unknown language for translations: '.$lang,100);}
		$this->lang=$lang;
	}
	
	public function display($mode,$settings=array()){
		if(empty($mode)){$mode = 'directory';}
		return call_user_func_array(array($this,$mode),$settings);
	}
	
	public function export(){
		self::exportPOFile($this->lang);
	}
	
	public function isOwner(){
		return true;
	}
	
	public function directory(){
		if(!in_array(3,App::$user->data['settings']['accesses'])){
			Msg::addMsg(401,0,Msg::CRITICAL);
			return false;
		}
		$r = '';
		$r .= dv('page-inner');
		$langs=array();
		$r .= dv('d960 center');
		$i=0;
		$q = 'SELECT `id`,`en_GB`,`'.$_SESSION['lang'].'` FROM `Languages`';
		$r .= dv('d4x3 col');
		if(LP::$db->customQuery($q,$d)){
			foreach($d as $id=>$str){
				if(empty($str[$_SESSION['lang']])){$str[$_SESSION['lang']] = 'no translations';$c='translation-entry not-translated';}
				else{$c='translation-entry';}
				$r .= dv($c,'Translation_'.$str['id']).lnk('<img src="/img/delete-grey.png" width="12px" class="icon" alt="delete"/>','#cur',array('delete[Translate]['.$str['id'].']'=>true)).' '.dv('original').'&laquo;'.$str['en_GB'].'&raquo;'.xdv().dv('translation').'<span class="editable" id="update_Translate_'.str_replace('_','subslash',$_SESSION['lang']).'_'.$str['id'].'">'.$str[$_SESSION['lang']].'</span>'.xdv().xdv();
				$i++;
			}
		}
		$r .= xdv();
		$r .= dv('d4 col').dv('padded fixed').'<ul>';
		$r .= '<li>'.lnk('English (UK)','translate',array('lang'=>'en_GB'),array('data-type'=>'plain','class'=>'btn')).'</li>';
		$r .= '<li>'.lnk('Français (France)','translate',array('lang'=>'fr_FR'),array('data-type'=>'plain','class'=>'btn')).'</li>';
		$r .= '<li>'.lnk('Save','translate/export',array(),array('class'=>'btn','ajax'=>true)).'</li>';
		$r .= '</ul>';
		$r .= xdv();
		$r .= xdv();
		$r .= xdv();
		$r .= xdv();
		return $r;
	}
	
	function add($str,$translations=array()){
		if(empty($str)){return false;}
		if(App::$db->selectBy('Languages','en_GB',$str,array('id'),$d)){
			if(count($d) > 0){
				return false;
			}
			$data = array('en_GB'=>$str,'site'=>HOST,'url'=>App::$url->path,'created'=>time(),'modified'=>time());
			foreach(self::$languages as $lang){
				if(isset($translations[$lang])){$data[$lang] = $translations[$lang];}
			}
			if(isset($translations['created'])){$data['created'] = $translations['created'];}
			if(isset($translations['modified'])){$data['modified'] = $translations['modified'];}
			if(isset($translations['site'])){$data['site'] = $translations['site'];}
			if(isset($translations['url'])){$data['url'] = $translations['url'];}
			
			if(!self::$db->insertInto('Languages',$data)){
				return false;
			}
			self::$active[] = $str;
			return true;
		}
		return false;
	}
	
	function delete(){
		if(empty($this->id)||!is_int($this->id)){
			$this->error('No ID for Translate::delete!',90);
			return false;
		}
		if(LP::$db->deleteData('Languages',$this->id)){
			res('script','$("#Translation_'.$this->id.'").fadeOut();');
			return true;
		}
		return false;
	}
	
	function validateData($id,$val){
		if(!in_array(3,App::$user->data['settings']['accesses'])){
			return false;
		}
		if(LP::$db->updateData('Languages',array($this->lang=>$val),$id)){
			return true;
		}
		return false;
	}
	
	public function update(){
		return true;
	}
	
   	public static function exportPOFile($lang){
		$langs = array($lang);
		if($lang!='en_GB'){$langs[] = 'en_GB';}
		$q = 'SELECT '.implode(',',$langs).' FROM `Languages`';
		$file = 'msgid ""'.PHP_EOL;
		$file .= 'msgstr ""'.PHP_EOL.PHP_EOL;
		$file .= '"Content-Type: text/plain; charset=utf-8"'.PHP_EOL.PHP_EOL;
		if(self::$db->customQuery($q,$d)){
			foreach($d as $t){
				if(!empty($t['en_GB']) && !empty($t[$lang])){
					$file .= 'msgid "'.str_replace(PHP_EOL,'',$t['en_GB']).'"'.PHP_EOL;
					$file .= 'msgstr "'.str_replace(PHP_EOL,'',$t[$lang]).'"'.PHP_EOL.PHP_EOL;
				}
			}
		}
		if(file_put_contents(ROOT.'local/locale/'.$lang.'/LC_MESSAGES/lang.po',$file)){
			//require_once(ROOT.'inc/php/plugins/moconvert/moconvert.php');
			//return phpmo_convert(ROOT.'local/locale/'.$lang.'/LC_MESSAGES/lang.po');
			$cmd = 'msgfmt '.ROOT.'local/locale/'.$lang.'/LC_MESSAGES/lang.po'.' -o '.ROOT.'local/locale/'.$lang.'/LC_MESSAGES/lang.mo';
			system($cmd);
			Msg::notify('Translations saved.');
			return true;
		}
		else{
			Msg::addMsg(translate('Failed to save PO file ('.ROOT.'local/locale/'.$lang.'/LC_MESSAGES/lang.po).'),0,Msg::CRITICAL);
			return false;
		}
	}
}
?>