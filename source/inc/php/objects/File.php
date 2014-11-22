<?php
class File extends Object{
	public $uid;
		//0:glob, 1:document, 2:factura, 3:contact file, 4:site logo, 5:site icon, 6:profile photo, 7:article image
	public $channel;
	public $type;
	public $views;
	public $path;
	public $filename;
	public $size;
	public $hash;
	public $encrypted;
	public $accessCode;
	
	public function descriptor(){
		return array(
			'id'=>'int',
			'uid'=>'int',
			'channel'=>'int',
			'type'=>'string',
			'views'=>'int',
			'path'=>'string',
			'filename'=>'string',
			'size'=>'string',
			'hash'=>'string',			//To verify integrity on download/upload
			'encrypted'=>'bool',		//Prompt for user password every time + verify SSL
			'accessCode'=>'string',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
		);
	}
	
	protected static function publicFunctions(){
		return array(
			'fullView'=>true,
			'preview'=>true,
		);
	}
	
	protected static function ownerFunctions(){
		return array(
			'edit'=>true,
			'editPreview'=>true,
			'directory'=>true,
		);
	}
	
	public function validateData($n,$v){
		switch ($n){
			case 'id':
			case 'uid':
			case 'sid':
			case 'width':
			case 'height':
			case 'path':
			case 'filename':
			case 'exif':
			case 'created':
			case 'modified':
			case 'deleted':
				return true;
			break;
			
			case 'aid':
				if(is_numeric($v)){
					$this->$n = $v;
					$a = new Serie($v);
					$pics = $a->getPhotos();
					$pics[] = $this->id;
					if(!$a->setPhotos($pics)){
						return true;
					}
				}
				return false;
			break;
			
			case 'title':
				if(strlen($v)>0 && strlen($v) <= 255){
					if($n == 'title' && empty($this->title)){$this->story->buzz += Story::ADDTITLE;}
					$this->$n = stripslashes($v);
					return true;
				}
				else{
					Msg::addMsg($n.' '.translate('too long (255 chars max)'));
					return false;
				}
			break;
			
			case 'description':
				if(strlen($v)>0 && strlen($v) <= 900){
					if(empty($this->description)){$this->story->buzz += Story::ADDDESCRIPTION;}
					$this->$n = stripslashes(urldecode($v));
					return true;
				}
				else{
					Msg::addMsg($n.' '.translate('too long (900 chars max)'));
					return false;
				}
			break;
		}
		return false;
	}
	
	function construct(){
	
	}
	
	public static function getUsage($uid='',$int=false){
		return App::$db->sum('size','File',array('uid'=>$uid));
	}
	
	protected function fullView(){
		if(!isset($_REQUEST['accessCode']) || $_REQUEST['accessCode'] != $this->accessCode){
			Msg::addMsg(401,0,Msg::CRITICAL);
			return false;
		}
		else{
			self::increment('views');
			header('Content-type: '.$this->type);
			exit(file_get_contents($this->path()));
		}
	}
	
	protected function edit(){
		$r = '';
		$r .= dv('uploadBox emptyfull').dv('myfile').xdv().xdv();
		$r .= self::uploadBtn(8,0,$this->id,'myfile');
		return $r;
	}
	
	public function tableHead(){
		return array('Filename','size','path','type','uploaded');
	}
	
	public function tableData(){
		return array(lnk($this->filename,'file/'.$this->id.'',array('accessCode'=>$this->accessCode),array('target'=>'_blank')),prettyBytes($this->size),$this->path(),$this->type,prettyTime($this->created));
	}
	
	public function directory(){
		$r = '';
		$max = 200;
		if(!IS_AJAX){
			$r .= dv('append','files','data-s="1"');
		}
		$c = new Collection('File');
		if(App::$user->access < 40 && isset($_REQUEST['uid'])){
			$c->uid('=',$_REQUEST['uid'],true);
		}
		else{
			$c->uid('=',App::$user->id,true);
		}
		$r .= dv('contentBox').$c->returnTable($_REQUEST['s']*$max,$max).xdv();
		if(!IS_AJAX){
			$r .= xdv();
			if($c->total(true) > ($_REQUEST['s']+1)*$max){
				$r .= dv('loadMore').lnk('...','#cur',array(),array('data-containerid'=>'files')).xdv();
			}
		}
		return $r;
	}
	
	public static function fileslist($mode='f'){
		$col = new Collection('Stack');
		$col->uid=App::$user->id;
		$data = array();
		$load = $col->load(0,1000);
		foreach($col->results as $stack){
			switch($mode){
				case 'f':
					$node = array();
					foreach($stack->photos as $pid){
						$photo = new Photo($pid);
						$file = new File($photo->fileid);
						$node[] = array('id'=>$file->id,'hash'=>$file->hash,'modified'=>$file->modified);
					}
					$data[] = array('id'=>$stack->id,'url'=>$stack->url,'access'=>$stack->story->access,'files'=>$node);
				break;
				
				case 'y':
					$data[]=array('id'=>$stack->id,'modified'=>$stack->modified);
				break;
			}
		}
		return $data;
	}
	
	public static function uploadBtn($channel=0,$a=0,$fileid=0,$container='uploadBox'){
		$r = '';
		$r .= dv('uploadForm');
		if($fileid!=0){$t=translate('Replace this file.');}
		else{$t=translate('Add files');}
		if(in_array($channel,array(0,1,2,3,4,6,7))){
			$r .= '<img src="/img/addphoto40.png" class="icon" alt="add files"/>';
		}
		$r .= '<form action="upload.php" method="POST" enctype="multipart/form-data"><input type="file" name="files[]" class="files" multiple title="'.$t.'" data-containerid="'.$container.'" data-a="'.$a.'" data-fileid="'.$fileid.'" data-channel="'.$channel.'"></form>';
		$r .= xdv();
		return $r;
	}
	
	
	public static function uploadEncryptedBtn($a='',$container=''){
		if(PROTOCOL !== 'https://'){
			return '<h3 class="error">Please use SSL connection to upload encrypted file!</h3>';
		}
		return '<form action="upload.php" method="POST" enctype="multipart/form-data"><input type="file" name="files[]" class="files" multiple title="'.translate('Add photos').'" data-containerid="'.$container.'" data-a="'.$a.'" data-channel="6"></form>';
	}
	
	public function path(){
		return ROOT.'local/files/'.$this->uid.'/'.$this->path;
	}
	
	public function url(){
		return HOME.'local/files/'.$this->uid.'/'.$this->path;
	}
	
	protected function preview(){
		if($this->id == 0){return 'Dummy story.';}
		if(!file_exists($this->path())){
			return '<div class="previewBox"><h3>'.translate('The file').' #'.$this->id.' '.translate('is not available anymore.').'</h3></div>';
		}
		$r= '<div class="previewBox"><h3>'.$this->filename.'</h3>';
		$r .= '</div>';
		return $r;
	}
	
	protected function editPreview(){
		$r='';
		$r .= dv('preview','file_'.$this->id);
		switch($this->type){
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/png':
			case 'image/gif':
				$r .= '<img src="/img/img40.png" alt="Image" title="Image"/>';
			break;
		}
		$r .= xdv();
		return $r;
	}
	
	public function fileinfo(){
		$r = '';
		$r .= dv('fileinfo small').'<h3>'.translate('File info').'</h3><p>'.translate('Original filename').': '.$this->filename.'</p>';
		$r .= '<p title="'.translate('File uploaded').'">'.translate('uploaded').' '.prettyTime($this->created).' ('.date('d/m/y H:i',$this->created).')'.'</p>';
		$r .= xdv();
		return $r;
	}
	
	private function encrypt($decrypted, $password) { 
		// Build a 256-bit $key which is a SHA256 hash of $salt and $password.
		$key = hash('SHA256', SITE_KEY.$password, true);
		// Build $iv and $iv_base64.  We use a block size of 128 bits (AES compliant) and CBC mode.  (Note: ECB mode is inadequate as IV is not used.)
		srand();
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
		if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22) return false;
		// Encrypt $decrypted and an MD5 of $decrypted using $key.  MD5 is fine to use here because it's just to verify successful decryption.
		$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $decrypted . md5($decrypted), MCRYPT_MODE_CBC, $iv));
		// We're done!
		return $iv_base64.$encrypted;
	} 
	
	private function decrypt($encrypted, $password){
		// Build a 256-bit $key which is a SHA256 hash of $salt and $password.
		$key = hash('SHA256', SITE_KEY.$password, true);
		// Retrieve $iv which is the first 22 characters plus ==, base64_decoded.
		$iv = base64_decode(substr($encrypted, 0, 22) . '==');
		// Remove $iv from $encrypted.
		$encrypted = substr($encrypted, 22);
		// Decrypt the data.  rtrim won't corrupt the data because the last 32 characters are the md5 hash; thus any \0 character has to be padding.
		$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($encrypted), MCRYPT_MODE_CBC, $iv), "\0\4");
		// Retrieve $hash which is the last 32 characters of $decrypted.
		$hash = substr($decrypted, -32);
		// Remove the last 32 characters from $decrypted.
		$decrypted = substr($decrypted, 0, -32);
		// Integrity check.  If this fails, either the data is corrupted, or the password/salt was incorrect.
		if (md5($decrypted) != $hash) return false;
		return $decrypted;
	}
	
	public function upload($tmp_path,$type,$moveFile=true){
		if(!file_exists($tmp_path)){
			$this->error('The temporary file does not exists.');
			return false;
		}
		if($moveFile){
			$dir = 'local/files/'.App::$user->id.'/';
			$when = time();
			if(!empty($this->created)){$when = $this->created;}
			$name = $when.'_'.randStr().'.'.$type;
			if(!checkPath(ROOT.$dir)){
				$this->error('Failed to create user directory');
				return false;
			}
			if(!rename($tmp_path,ROOT.$dir.$name)){
				$this->error('Failed to move file');
				return false;
			}
		}
		else{
			$this->path = $tmp_path;
		}
		$parts = explode('/',$tmp_path);
		$this->type = mime_content_type(ROOT.$dir.$name);
		$this->size = filesize(ROOT.$dir.$name);
		$this->hash = sha1_file(ROOT.$dir.$name);
		$this->accessCode = randStr();
		$this->path = $name;
		chmod(ROOT.$dir.$name,0755);
		return true;
	}
	
	protected function postInsert(){
		if(empty($this->accessCode)){$this->accessCode = randStr();$this->update(true);}
		if(empty($this->hash)){$this->hash = sha1_file($this->path());$this->update(true);}
		return true;
	}
	
	protected function postDelete($force = false){
		if(file_exists($this->path())){
			if(unlink($this->path())){
				return true;
			}
		}
		return false;
	}
	
}
?>