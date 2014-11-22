<?php
class Error extends Interfaces{
	public function directory($code='404'){
		$r = '';
		if(isset($_REQUEST['code'])){$code = $_REQUEST['code'];}
		switch(strval($code)){
			case '400':
				T::$page['title'] = t('Oops.. Bad request!');
				$e = t('Error 400: Bad Request');
			break;
			
			case '401':
				T::$page['title'] = t('Oops.. No access!');
				$e = t('Error 401: You may not access this area');
			break;
			
			case '403':
				T::$page['title'] = t('Oops.. Forbidden!');
				$e = t('Error 403: You don\'t have permission to access this area');
			break;
			
			case '404':
			case NULL:
				T::$page['title'] = t('Oops.. Not found!');
				$e = t('Sorry, the page was not found!');
			break;
			
			case '500':
				T::$page['title'] = t('Oops.. server error!');
				$e = 'Error 500: Internal server error';
			break;
			
			default:
				T::$page['title'] = t('Oops.. unknown error occured!');
				$e = 'Unknown Internal server error!';
			break;
		}
		if(IS_AJAX){return $e;}
		$r .= dv('d600 center');
		$r .= dv('hero').'<h1>'.$e.'</h1>'.xdv();
		$r .= '<img src="/img/error/404-'.rand(1,2).'.png" alt="Page error" title="ouch" width="500" class="nocopy"/>';
		$r .= xdv();
		return $r;
	}
	
	static function e404(){
		$i = new Error;
		return $i->directory(404);
	}
}
?>