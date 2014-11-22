<?php
define('WEBDAV',true);
require_once('inc/config.php');
define('SYNC_ROOT',ROOT.'plugins/sync/');
define('UPLOADCLASSONLY','');
require_once('upload.php');

use Sabre\DAV;
use Sabre\HTTP;

// SabreDAV files
require_once 'plugins/sync/SabreDAV/vendor/autoload.php';
//$u = 'pop';
//$p = 'test2';
/*

$realm = 'PixytRealm';

$hash = md5($u . ':' . $realm . ':' . $p);

$auth = new HTTP\DigestAuth();
$auth->setRealm($realm);
$auth->init();

if ($auth->getUsername() != $u || !$auth->validateA1($hash)) {

    $auth->requireLogin();
    echo "Authentication required\n";
    die();
}

*/
if(User::$me->id==0){
	$auth = new HTTP\BasicAuth();
	
	$result = $auth->getUserPass();
	if(!User::login($result[0],$result[1])){
		$auth->requireLogin();
		//LP::addError($result[0].' Failed to connect to webdav!');
		echo "Authentication required\n";
		die();
	}
//	mail('contact@samueldelesque.com','connection',User::$me->fullName('full').' just connected to WebDav');
}
/*
if (!$result || $result[0]!=$u || $result[1]!=$p) {

    $auth->requireLogin();
    echo "Authentication required\n";
    die();

}
*/
//setcookie ('blabla', '123');

// Now we're creating a whole bunch of objects


class MyDirectory extends DAV\Collection {
	private $stack = NULL;
	function __construct($name=''){
		
		//mail('contact@samueldelesque.com',__FUNCTION__.'::'.__CLASS__,'Version:'.\Sabre\DAV\Version::VERSION);
		
		if(!empty($name)){
			if(!Object::objectExists('Stack',$name,'title',$id)){
				throw new DAV\Exception\NotFound('The Stack [' . $name . '] could not be found');
				return;
			}
			$this->stack = new Stack($id);
		}
	}
	
	function createFile($name, $data = null) {
		if(!file_exists(ROOT.'local/files/'.User::$me->id)){mkdir(ROOT.'local/files/'.User::$me->id);}
		$path = ROOT.'local/files/'.User::$me->id.'/dav_'.$name;
		if(!file_put_contents($path,$data)){
			LP::addError('Failed to create tmp file');
			throw new DAV\Exception\NotFound('Failed to create tmp file');
			return false;
		}
		$upload_handler = new UploadHandler();
		$response = $upload_handler->make($path,$name,filesize($path),mime_content_type($path),'',$this->stack->id,1);
		
		//mail('contact@samueldelesque.com',__FUNCTION__.'::'.__CLASS__,'FILE SAVED AT '.$path.PHP_EOL.'RESPONSE: '.print_r($response,true));
		return '';
		/*
		$dir = 'local/files/'.User::$me->id.'/';
		if($this->stack==NULL){
			$a = User::$me;
		}
		else{
			$a = $this->stack;
		}
		if(!checkPath(ROOT.$dir.$a->folder())){
			throw new DAV\Exception\Permission('Failed to make path.');
			return false;
		}
		$file = new File();
		$file->filename = $name;
		$file->size = intval($size);
		$file->type = $type;
		$file->hash = sha1_file($uploaded_file);
		$file->path = $a->folder();
		$file->insert();
		$file->path .= '/'.$file->id.'.'.$extension;
		$file->update(true);
		*/
	}
	
	function getChildren(){
		if($this->stack == NULL){
			$collection = new Collection('Stack');
			$collection->uid = User::$me->id;
			$collection->load(0,100);
			$children = array();
			foreach($collection->results as $stack){
				$children[] = $this->getChild($stack->title);
			}
			return $children;
		}
		else{
			$collection = new Collection('Photo');
			$collection->uid = User::$me->id;
			$collection->kid = $this->stack->id;
			$collection->load(0,100);
			$children = array();
			foreach($collection->results as $photo){
				$children[] = $this->getChild($photo->id,$this->stack->id);
			}
			return $children;
		}
	}
	
	function getChild($name){
		if(!$this->stack==NULL){
			$title = stripFileExtension($name);
			return new MyFile($title,$this->stack->id);
		}
		else{
			return new MyDirectory($name);
		}
	}
	
	function childExists($name){
		return Object::objectExists('Stack',$name,'title');
	}
	
	function getName(){
		if($this->stack==NULL){return '/';}
		return '/'.$this->stack->title;
	}
	
	function setName($name){
		if($this->stack->validateData('title',$name)){
			if($this->stack->update()){
				return true;
			}
		}
		return false;
	}
	
	function createDirectory($name){
		$stack = new Stack();
		if(!$stack->validateData('title',$name)){
			throw new DAV\Exception\Forbidden('Wrong directory name');
		}
		else{
			if($stack->insert()){return true;}
			throw new DAV\Exception\Forbidden('Failed to insert Stack');
		}
	}
	
	function delete(){
		$this->stack->delete();
	}
	
	function getLastModified(){
		if($this->stack==NULL){return time();}
		return $this->stack->modifyDate;
	}
}
	
class MyFile extends DAV\File {
	private $photo = NULL;
	private $file = NULL;
	
	function __construct($title,$stack=0){
		$pid = Photo::getUserFile($title,$stack);
		//mail('contact@samueldelesque.com',__FUNCTION__,$title.'::'.$pid);
		$this->photo = new Photo($pid);
		$this->file = new File($this->photo->fileid);
	}
	
	function getName() {
		if(!empty($this->photo->title)){return $this->photo->title.'.jpg';}
		return $this->photo->id.'.jpg';
	}
	
	function setName($name){
		$title = stripFileExtension($name);
		if($title == $this->photo->id || $title == $this->photo->title){return true;}
		if($this->photo->validateData('title',$title)){
			if($this->photo->update()){
				return true;
			}
		}
		throw new DAV\Exception\Forbidden('Failed to set name!');
		return;
	}
	
	function get(){
		if(!file_exists($this->file->path())){return false;}
		return fopen($this->file->path(),'r');
	}
	
	function put($data){
		if(!file_exists($this->file->path())){return false;}
		file_put_contents($this->file->path(),$data);
		$this->photo->saveExif();
		return true;
	}
	
	function getSize(){
		return $this->file->size;
	}
	
	function getLastModified(){
		if($this->photo==NULL){return time();}
		return $this->photo->modifyDate;
	}
	
	function delete(){
		$this->photo->delete();
	}
	
	function getETag(){
		if(!file_exists($this->file->path())){return false;}
		return '"' . md5_file($this->file->path()) . '"';
	}
	
	function getContentType(){
		return $this->file->type;
	}
}
/*
class MyDirectory extends DAV\Collection {
	private $uuid = 0;
	function __construct($uuid=0){
		$this->uuid = $uuid;
	}
	
	function getChildren() {
		$collection = new Collection('Uniqueid');
		$collection->uid = User::$me->id;
		$collection->parent = $this->uuid;
		$collection->load(0,100);
		$children = array();
		foreach($collection->results as $uuid){
			$children[] = $this->getChild($uuid->id);
		}
		return $children;
	}
	
	function getChild($id){
		$uuid = new Uniqueid($id);
		if (!Object::objectExists('Uniqueid',$id)) {
			throw new DAV\Exception\NotFound('The Uniqueid[' . $id . '] could not be found');
		}
		switch($uuid->objecttype){
			case 1:
				return new MyDirectory($uuid->id);
			break;
			
			default:
			case 2:
				return new MyFile($uuid->id);
			break;
		}
	}
	
	function childExists($id){
		return Object::objectExists('Uniqueid',$id);
	}
	
	function getName() {
		return $this->uuid;
	}
}
	
class MyFile extends DAV\File {
	private $uuid;
	private $file;
	private $object;
	function __construct($uuid) {
		$this->uuid = new Uniqueid($uuid);
		switch($this->uuid->objecttype){
			case 1:
				throw new DAV\Exception\NotFound('Cannot open folder as a file.');
			break;
			case 2:
				$this->object = new Photo($this->uuid->objectid);
				$this->file = new File($this->object->fileid);
			break;
			default:
				throw new DAV\Exception\NotFound('Only Photos currently supported.');
			break;
		}
	}
	
	function getName() {
		return $this->uuid->id.'.jpg';
	}
	
	function get(){
		return fopen($this->file->path(),'r');
	}
	
	function getSize(){
		return $this->file->size;
	}
	
	function getETag(){
		return '"' . md5_file($this->file->path()) . '"';
	}
}
*/
/*
$rootDirectory = new DAV\FS\Directory(SYNC_ROOT.'public');

// The server object is responsible for making sense out of the WebDAV protocol
$server = new DAV\Server($rootDirectory);

// If your server is not on your webroot, make sure the following line has the correct information

// $server->setBaseUri('/~evert/mydavfolder'); // if its in some kind of home directory
// $server->setBaseUri('/dav/server.php/'); // if you can't use mod_rewrite, use server.php as a base uri
// $server->setBaseUri('/'); // ideally, SabreDAV lives on a root directory with mod_rewrite sending every request to server.php

// The lock manager is reponsible for making sure users don't overwrite each others changes. Change 'data' to a different 
// directory, if you're storing your data somewhere else.
*/
$dir = new MyDirectory();
$server = new DAV\Server($dir);
$server->setBaseUri('/');

//$lockBackend = new DAV\Locks\Backend\File(SYNC_ROOT.'data/locks');
//$lockPlugin = new DAV\Locks\Plugin($lockBackend);

//$server->addPlugin($lockPlugin);
// All we need to do now, is to fire up the server
$server->exec();

?>