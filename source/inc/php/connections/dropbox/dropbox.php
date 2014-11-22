<?php
class Dropbox extends Connections{
	private $key = 'rxjc16cxeme11e7';
	private $key = 'ye64vioyau4a719';
	public $box;
	
	function connect(){
		$oauth = new Dropbox_OAuth_PHP($this->key,$this->secret);
		$this->box = new Dropbox_API($oauth);
	}
}
?>