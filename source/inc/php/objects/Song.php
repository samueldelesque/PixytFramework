<?php
class Song extends Object{
	
	public $uid;
	public $kid;
	public $fileid;
	public $validated;
	public $channel;
	public $title;
	public $artist;
	public $album;
	public $genre;
	public $bitrate;
	public $length;
	public $plays;
	public $access=3;
	public $customOrder;
	
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
		);
	}
	
	public static $mime_types = array(
		'audio/mp3'=>'mp3',
	);
	
	public function descriptor(){
		return array (
			'id'=>'int',
			'uid'=>'int',
			'kid'=>'int',
			'prid'=>'int',
			'fileid'=>'int',
			'channel'=>'int',
			'title'=>'string',
			'artist'=>'string',
			'album'=>'string',
			'genre'=>'string',
			'bitrate'=>'int',
			'length'=>'string',
			'plays'=>'int',
			'access'=>'int',
			'customOrder'=>'int',
			'created'=>'int',
			'modified'=>'int',
			'deleted'=>'int',
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
			case 'created':
			case 'modified':
				return true;
			break;
			
			case 'removeHD':
				if($this->removeHD()){
					return true;
				}
				else{
					Msg::addMsg(translate('Failed to remove file.'));
				}
			break;
			
			case 'title':
				if(strlen($v)>0 && strlen($v) <= 255){
					$this->$n = $v;
					return true;
				}
				else{
					Msg::addMsg($n.' '.translate('too long (255 chars max)'));
					return false;
				}
			break;
			
			case 'description':
				if(strlen($v)>0 && strlen($v) <= 900){
					$this->$n = $v;
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
	
	private function analyze($save = true){
		require_once(ROOT.'inc/php/plugins/getid3-1.9.3/getid3/getid3.php');
		$file = new File($this->fileid);
		$parser = new getID3();
		$data = $parser->analyze($file->path());
		if($save===true){
			$this->title = $data['tags']['id3v2']['title'][0];
			$this->artist = $data['tags']['id3v2']['artist'][0];
			$this->album = $data['tags']['id3v2']['album'][0];
			$this->genre = $data['tags']['id3v2']['genre'][0];
			$this->customOrder = $data['tags']['id3v2']['track_number'][0];
			$this->bitrate = $data['bitrate'];
			$this->length = $data['playtime_string'];
			$this->update(true);
		}
		return $data;
	}

	//DISPLAY MODES
	protected function fullView(){
		return 'Not yet available.';
	}
	
	protected function editPreview(){
		return $this->preview();
	}
	
	protected function edit(){
		return 'Not yet available';
	}
	
	protected function preview(){
		$r= dv('preview song file','Song_'.$this->id).dv('player_small','Song_'.$this->id.'_player').xdv();
		$r .= dv('tools');
		$r .= lnk('<img src="/img/tool-edit.png" height="25px" alt="edit"/>','song/'.$this->id.'/edit').lnk('<img src="/img/tool-delete.png" height="25" alt="delete"/>','#cur',array('delete[Song]['.$this->id.']'=>true),array('data-type'=>'confirm','data-matter'=>translate('Are you sure you want to delete this song?')));
//		$r .= '<a href="#" onClick="$(\'#Song_'.$this->id.'_player\').jPlayer(\'pause\');" class="jp-pause">pause</a>';
		$r .= dv('jp-current-time').xdv().dv('jp-duration').xdv();
		$r .= xdv();
		$r .= '<a href="javascript:;" onClick="$(\'#Song_'.$this->id.'_player\').jPlayer(\'play\');" class="jp-play"><img src="/img/player/play.png" alt="play"/></a>';
		$r .= '<a href="javascript:;" onClick="$(\'#Song_'.$this->id.'_player\').jPlayer(\'pause\');" class="jp-pause"><img src="/img/player/pause.png" alt="pause"/></a>';
		$r .= '<p class="title">'.$this->title.'</p>';
		$r .= dv('head').$this->length.' - '.($this->bitrate/1000).'Kb/s'.xdv();
		$file = new File($this->fileid);
		T::$js[] = '
$(function(){
	var Song'.$this->id.'isPlaying=false;
	$("#Song_'.$this->id.'_player").jPlayer({
		ready: function(){
			$(this).jPlayer("setMedia",{
				mp3: "'.$file->url().'"
			});
		},
		supplied: "mp3",
		swfPath: HOME+"inc/js/jQuery.jPlayer.2.1.0",
	}).bind($.jPlayer.event.play, function(event){
		if(!Song'.$this->id.'isPlaying){
			query(HOME+"ajax",{"gethtml":0,"datatype":"json","show[Song]['.$this->id.']":1});
		}
		Song'.$this->id.'isPlaying=true;
	});
});
		';
		$r .= xdv();
		return $r;
	}
	
	//PUBLIC FUNCTIONS
	public function tableData(){
		$data = $this->analyze();
		$file = new File($this->fileid);
		return array($this->title,$this->artist,$this->album,$this->genre,round($file->size/1000000,2).'Mo');
	}
	
	public function directory(){
		$c = new Collection('Song');
		T::$body[] = $c->content('preview');
	}
	
	public function postDelete($force = false){
		if($this->fileid != 0){
			$file = new File($this->fileid);
			$file->delete();
		}
		$addempty='';
		if(!empty($this->kid)){
			$stack = new Stack($this->kid);
			if(count($stack->children())<=1){
				$addempty = '$(".uploadBox").addClass("empty");';
			}
		}
		res('script','$("[id^=song_'.$this->id.'],[id^=Song_'.$this->id.']").fadeOut().remove();'.$addempty);
		return true;
	}
	
	
	//OBJECT RELATED FUNCTIONS
	protected function postInsert(){
		$data = $this->analyze();
		$this->update();
		return true;
	}
	
	protected function postUpdate(){
		return true;
	}
}
?>