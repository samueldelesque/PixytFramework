<?php
class Upload extends Interfaces{
	public function directory(){
		return $this->handle($_FILES['files'],$_REQUEST['kid']);
	}
	
	private function handle($f,$kid){
		switch(reset($f['type'])){
			case 'image/jpeg':
				$file = new File();
				$file->upload(reset($f['tmp_name']),'jpg');
				$file->filename = reset($f['name']);
				$file->insert();
				
				$photo = new Photo();
				$photo->kid = $kid;
				$photo->fileid = $file->id;
				$photo->processExif();
				$photo->insert();
			
				//must be called after insert!
				$photo->resizeAll() || die('Failed to resize images');
				
				return $photo->data();
			break;
			
			default:
				return array('error'=>'This file type is not supported!');
			break;
		}
	}
}
?>