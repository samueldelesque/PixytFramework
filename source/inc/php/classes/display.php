<?php
class Display extends Pixyt{
	public $pagenumber = 1;
	public $action='';
	
	function __construct(){
		parent::__construct();
	}
	
	public function directory(){
		return array();
	}
	
	public function show($template=NULL,$header=200,$host=false){
		if($host===false){$host = HOST;}

		//feed the pages info into a JS object for use within the app
		T::$page['pages'] = App::$site && isset(App::$site->content)?App::$site->content:array();

		//load the current page data
		if(isset(App::$site->content->{App::$url->interface})){
			$pageData = App::$site->content->{App::$url->interface};
			if(isset($pageData->content) && isset($pageData->content->{App::$url->function})){
				$pageData = (object)array_merge((array)$pageData,(array)$pageData->content->{App::$url->function});
			}
		}
		else{
			if(App::$url->interface == "index" && isset(App::$site->content->index)){
				$pageData = App::$site->content->index;
			}
			elseif(App::$url->interface == "index" && !isset(App::$site->content->index)){
				$pageData = reset(App::$site->content);
			}
			else{
				$pageData = new stdClass;
			}
		}

		//set settings as default values (Title/Description)
		if(isset(App::$site))$pageData = (object)array_merge((array)App::$site->settings,(array)$pageData);

		if(isset(App::$site->settings->theme)){
			$theme_path = 'assets/themes/'.App::$site->settings->theme.'/tpl/';
		}
		else{
			$theme_path = 'assets/themes/default/tpl/';
		}

		//handle the input method for CRUD capabilities
		if(isset(App::$url->object)){
			switch(App::$url->method){
				case 'get':
					$pageData->data = App::$url->object->data();
					//return $this->response($template,App::$url->object->data());
				break;
				
				case 'post':
					$errors = array();
					$hasError = false;
					foreach(App::$input as $field=>$value){
						if(array_key_exists($field,App::$url->object->descriptor())){
							if(!App::$url->object->validateData($field,$value)){
								$hasError = true;
								//$errors[] = 'Failed to validate '.$field;
							}
						}
					}
					if(!$hasError){
						if(!in_array(App::$url->object->className,array('Message','Subscriber','User')) && App::$user->id == 0){
							$errors[] = 'Please login to do that.';
						}
						elseif(App::$url->object->id > 0){
							$errors[] = 'This object already exists.';
						}
						elseif(empty($errors)){
							if(App::$url->object->insert()){
								$pageData->data = App::$url->object->data();
								//return $this->response($template,App::$url->object->data());
							}
						}
					}
					else{
						$errors = array_merge($errors,Msg::getMessages());
						$pageData->data['errors'] = $errors;
						return $this->response($template,$pageData,401);
					}	
				break;
				
				case 'put':
					$errors = array();
					$invalid = array();
					$hasError = false;
					foreach(App::$input as $field=>$value){
						if(isset(App::$url->object->$field) && !App::$url->object->validateData($field,$value)){
							$hasError = true;
							$invalid[] = $field;
							//$errors[] = 'Failed to validate '.$field;
						}
					}
					if(!$hasError){
						if(!App::$url->object->isOwner()){
							$errors[] = 'You may not edit this item.';
						}
						elseif(App::$url->object->id == 0){
							$errors[] = 'Cannot update object since it doesn\'t exists!';
						}
						elseif(empty($invalid)){
							if(App::$url->object->update()){
								$pageData->data = App::$url->object->data();
								return $this->response($template,$pageData);
							}
							else{
								//$errors[] = 'Failed to update '.App::$url->object->className;
								return $this->response($template,array('errors'=>$errors,'invalid'=>$invalid),401);
							}
						}
					}
					else{
						$errors = array_merge($errors,Msg::getMessages());
						return $this->response($template,array('errors'=>$errors,'invalid'=>$invalid),401);
					}
				break;
				
				case 'delete':
					return $this->response($template,array('errors'=>array('Deletion not allowed currently.')),401);
				break;
				
				default:
					return $this->response($template,array('error'=>array('Method not allowed ('.App::$url->method.').')),404);
				break;
			}
		}
		else{
			if(in_array(App::$url->interface,App::$interfaces)){
				//load interface methods (for Pixyt site)
				$obj = ucfirst(App::$url->interface);
				require_once(ROOT.'inc/php/interfaces/'.App::$url->interface.'.php');
				$interface = new $obj();

				$pageData->data = $interface->{App::$url->function}(App::$url->params[0],App::$url->params[1]);
			}
			else{
				$pageData->data = new stdClass;
			}
		}

		//return the html
		return $this->response((isset($pageData->template)?$theme_path.$pageData->template:$template),$pageData,$header);
	}
	
	//Handle the output format
	public function response($template,$pageData,$header=200){
		// die('Show: '.$template);
		//if(!is_object($pageData)){$d = $pageData;$pageData = new stdClass;$pageData->data = $d;}
		// var_dump($pageData);
		// die('|KILLED');
		$pageData = (object)$pageData;
		if(isset($pageData->data)&&($pageData->data===401||$pageData->data===404)){$header=$pageData->data;}
		switch($header){
			case 404:
				header('HTTP/1.0 404 Not Found');
			break;
			
			case 401:
				header('HTTP/1.1 401 Unauthorized');
			break;
		}
		switch(App::$url->format){
			case 'json':
				header('Content-type: application/json');
				return json_encode($pageData);
			break;
			
			case 'html':
				$page = new T();
				T::$page = array_merge(T::$page,(array)$pageData);
				return $page->show($template,$pageData);
			break;
			
			default:
				return 'Format not supported!';
			break;
		}
	}
	
	public function validate(){
		header('Location: '.HOME,true,301);
	}
	
	public function pageError(){
		header('Location: '.HOME.'error',true,301);
	}
	
	public function guest(){
		header('Location: '.HOME,true,301);
	}
	
	public function exhibit(){
		header('Location: '.HOME,true,301);
	}
	
	public function about(){
		header('Location: '.HOME,true,301);
	}
	
	public function compta(){
		header('Location: '.HOME.'accounting',true,301);
	}
	
	public function termsofsales(){
		header('Location: '.HOME.'terms/sales',true,301);
	}

	public function termsofuse(){
		header('Location: '.HOME.'terms/service',true,301);
	}
	
	public function termsofservice(){
		header('Location: '.HOME.'terms/service',true,301);
	}
	
	public function contactus(){
		header('Location: '.HOME.'about',true,301);
	}
	
	public function privacy(){
		header('Location: '.HOME.'terms/service',true,301);
	}
	
	public function construct(){
		return true;
	}
	
	public static function ajax(){
		//process ajax requests silently
		return true;
	}
}
?>