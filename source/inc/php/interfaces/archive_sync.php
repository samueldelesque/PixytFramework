<?php
class Archive_Sync extends Interfaces{
	public function login(){
		if(isset($_REQUEST["username"]) && isset($_REQUEST["password"])){
			if(User::login($_REQUEST["username"], $_REQUEST["password"])){
				T::$body[] = "OK";
			}else{
				T::$body[] = "AUTH_ERROR";
				session_destroy();
			}
		}else{
			T::$body[] = "REQUEST_ERROR";
		}
	}
	
	public function files(){
		if(isset($_REQUEST["username"]) && isset($_REQUEST["mode"])){
			if(App::$user->email == $_REQUEST["username"]){
				$data = File::fileslist($_REQUEST["mode"]);
				T::$body[] = json_encode($data);
			}else{
				T::$body[] = "AUTH_ERROR";
			}
		}else{
			T::$body[] = "REQUEST_ERROR";
		}
	}
}
?>