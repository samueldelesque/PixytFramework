<?php
class T extends Pixyt{
	//base info
	public static $page = array(
		//meta
		'title'=>'Untitled',
		'description'=>'Pixyt is a photo network connecting photographers and other people in the photography industry.',
		'keywords'=>'photo network, photography network, photographer, photo contest, professionnal, photo sharing, fashion photography, portrait photography',
		'icon'=>'/favicon.ico',
		'og:image'=>'',
		'og:type'=>'', //@TODO: 'website',
		'og:title'=>'',
		'fb:app_id'=>'508730959165083',
		'robots'=>'index follow',
		//includes
		'css'=>array(),
		'js'=>array(),
		'tpl'=>array(),
		//#page html
		'html'=>'',
		'theme'=>'default',
		'target'=>'index',
		//styles
		'class'=>'',
	);

	public static $options = array(
		'FB'=>"508730959165083",
		'TWITTER'=>true,
		'USERVOICE'=>false,
		'GAQ'=>true,
	);
	
	function __construct($class=''){
		parent::__construct();
	}
	
	/*
	 * Define template variables
	 *
	 */
	 
	public static function title($title=NULL){
		self::$page['title'] = $title;
	}
	
	public static function description($description=NULL){
		self::$page['description'] = $description;
	}
	
	public static function css($file){
		if(is_array($file)){
			self::$page['css'] = array_merge($file,self::$css);
		}
		else{
			self::$page['css'][] = $file;
		}
	}
	
	public static function js($file){
		if(is_array($file)){
			self::$page['js'] = array_merge($file,self::$js);
		}
		else{
			self::$page['js'][] = $file;
		}
	}

	public static function tpl($file){
		if(is_array($file)){
			self::$page['tpl'] = array_merge($file,self::$tpl);
		}
		else{
			self::$page['tpl'][] = $file;
		}
	}

	public function main($html){
		self::$page['html'] = $html;
	}
	
	public function body($html,$name=''){
		$r = '';
		self::$page['target'] = $name;
		$style = ' fluid';
		if(isset($_REQUEST['viewport'])){
			$style .= ' '.$_REQUEST['viewport'];
		}
		elseif(App::$device->isMobile()){
			$style.=' mobile';
			if(App::$device->isTablet()){$style.=' tablet';}
		}
		else{
			$style .= ' desktop';
		}
		
		if($_SESSION['uid']==0){
			$style.=' unlogged';
		}
		else{
			$style.=' logged';	
		}
		$r .= '<body class="'.$style.'">';
		
		if(self::$options['FB'])$r .= '<div id="fb-root"></div>';

		$path = 'assets/themes/'.self::$page['theme'].'/tpl/menu';
		if(!file_exists($path.'.html')){$path = 'assets/themes/default/tpl/menu';}

		$menu = App::$handlebars->render($this->get_tpl($path),$this->tpl_data(array('content'=>App::$site->content)));
		
		//the HTML structure
		$r .= '<div id="container" class="'.self::$page['theme'].' '.$name.' '.self::$page['class'].'">';
		$r .= '<div id="menu">'.$menu.'</div>';
		$r .= '<div id="lightbox" style="display:none"></div>';
		$r .= '<div id="lightbox_bgd" style="display:none"></div>';
		$r .= '<div id="notifications"></div>';
		$r .= '<div id="page">'.$html.'</div>';
		$r .= '</div>';

		//display MySQL requests
		if((App::$user->hasAccess(1) || HOST == 'localhost') && isset($_REQUEST['getqueries'])){
			$r .= dv('queries');
			$r .= '<h2>'.count(Mysql::$queries).' queries in total</h2>';
			$r.=(implode('<br/>',Mysql::$queries));
			$r .= xdv();
		}

		//load the javascript
		$r .= '<script type="text/javascript" src="/assets/js/libs.min.js"></script>';
		$r .= '<script type="text/javascript">'.$this->pagejs().'</script>';
		foreach(self::$page['js'] as $script){
			$r .= '<script type="text/javascript" src="'.$script.'"></script>';
		}

		$r .= '</body>';
		return $r;
	}
	
	public function content(){
		return self::$page['html'];
	}

	public static function isometric_logo_1(){
		return '
      ___                     ___                             
     /  /\      ___          /__/|          ___         ___   
    /  /::\    /  /\        |  |:|         /__/|       /  /\  
   /  /:/\:\  /  /:/        |  |:|        |  |:|      /  /:/  
  /  /:/~/:/ /__/::\      __|__|:|        |  |:|     /  /:/   
 /__/:/ /:/  \__\/\:\__  /__/::::\____  __|__|:|    /  /::\   
 \  \:\/:/      \  \:\/\    ~\~~\::::/ /__/::::\   /__/:/\:\  
  \  \::/        \__\::/     |~~|:|~~     ~\~~\:\  \__\/  \:\ 
   \  \:\        /__/:/      |  |:|         \  \:\      \  \:\
    \  \:\       \__\/       |  |:|          \__\/       \__\/
     \__\/                   |__|/                            
';	
	}

	public static function isometric_logo_2(){
		return '
      ___                       ___           ___           ___     
     /\  \          ___        |\__\         |\__\         /\  \    
    /::\  \        /\  \       |:|  |        |:|  |        \:\  \   
   /:/\:\  \       \:\  \      |:|  |        |:|  |         \:\  \  
  /::\~\:\  \      /::\__\     |:|__|__      |:|__|__       /::\  \ 
 /:/\:\ \:\__\  __/:/\/__/ ____/::::\__\     /::::\__\     /:/\:\__\
 \/__\:\/:/  / /\/:/  /    \::::/~~/~       /:/~~/~       /:/  \/__/
      \::/  /  \::/__/      ~~|:|~~|       /:/  /        /:/  /     
       \/__/    \:\__\        |:|  |       \/__/         \/__/      
                 \/__/        |:|  |                                
                               \|__|                                
';
	}
	
	public function show($template=NULL,$data=array()){
		// die($template);
		$r = '';
		$r .= '<!doctype html><html itemscope="itemscope" itemtype="http://schema.org/WebPage">';
		$fn = 'isometric_logo_'.rand(1,2);
		$r .= '<!--'."\n\n".$this->$fn()."\n\n".'-->';
		
		if(is_object($data)){
			self::$page = array_merge(self::$page,objectToArray($data));
		}
		
		$r .= $this->head();

		$template = str_replace('.','/',$template);

		if($template==NULL && file_exists(ROOT.'assets/themes/'.self::$page['theme'].'/tpl/404.html')){
			if(ENV!='prod')echo '<!-- 404: [template=NULL] -->';
			$template = 'assets/themes/'.self::$page['theme'].'/tpl/404';
			$name = 'notfound';
		}
		elseif(file_exists(ROOT.$template.'.html')){
			$parts = explode('/',$template);
			$name = end($parts);
		}
		else{
			if(ENV!='prod')echo '<!-- 404: [template='.$template.'.html] -->';
			$name = 'notfound';
			$template = 'assets/themes/'.self::$page['theme'].'/tpl/404';
			if(!file_exists(ROOT.$template.'.html')){$template = 'assets/themes/default/tpl/404';}
		}
		// echo '<pre>';
		// var_dump($this->tpl_data($data));
		// die();
		$html = '<div id="'.$name.'">'.App::$handlebars->render($this->get_tpl($template),$this->tpl_data($data)).'</div>';
		$r .= $this->body($html,$name);
		$r .= '</html>';
		return $r;
	}
	
	public function head(){
		$r = '';
		$r .= '<head>';
		$r .= '<title>'.self::$page['title'].'</title>';
		$r .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
		$r .= '<meta name="description" content="'.shorten(self::$page['description'],260).'"/>';
		$r .= '<meta name="robots" content="'.self::$page['robots'].'"/>';
		$r .= '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>';
		
		if(!empty(self::$page['og:image'])){
			$r .= '<meta property="og:image" content="'.HOME.self::$page['og:image'].'"/>';
		}
		if(!empty(self::$page['og:type'])){
			$r .= '<meta property="og:type" content="'.HOME.self::$page['og:type'].'"/>';
		}
		if(!empty(self::$page['og:title'])){
			$r .= '<meta property="og:title" content="'.HOME.self::$page['og:title'].'"/>';
		}
		if(!empty(self::$page['fb:app_id'])){
			$r .= '<meta property="fb:app_id" content="'.self::$page['fb:app_id'].'"/>';
		}

	    $r .= '<link rel="shortcut icon" href="'.self::$page['icon'].'"/>';

	    //DEV ONLY
	    $r .= '<link href="https://fontastic.s3.amazonaws.com/JjApkoXMA5weM2BAFkqBUW/icons.css" rel="stylesheet">';

	    //load the css
		foreach(self::$page['css'] as $sheet){
			$r .= '<link rel="stylesheet" type="text/css" href="'.$sheet.'"/>';
		}
        
		$r .= '</head>';
		return $r;
	}
	
	public static function pagejs(){
		if(!IS_AJAX){
			if(PROTOCOL=='https://'){$protocol='https://ssl.';}else{$protocol='http://www.';}
			if(defined('USER_ANALYTICS')){$aux=',[\'b._setAccount\',\''.USER_ANALYTICS.'\']]';$auxb='[\'b._trackPageview\']';}
			else{$aux='';$auxb='';}
			self::$options['PHPSESSID'] = session_id();
			self::$options['HOME'] = HOME;
			self::$options['CURRENT'] = HOME.$_REQUEST['url'];
			self::$options['TARGET'] = self::$page['target'];
			self::$options['PAGIN'] = HOME.$_REQUEST['s'];
			self::$options['AUXB'] = $auxb;
			self::$options['PROTOCOL'] = $protocol;
			self::$options['CONNECTED'] = (App::$user->id > 0);
			self::$options['ME'] = App::$user->data();
			self::$options['FROM'] = $_SESSION['from'];
			self::$options['CAMPAIGN'] = $_SESSION['campaign'];
			self::$options['MEDIUM'] = $_SESSION['medium'];
			self::$options['KEYWORD'] = $_SESSION['keyword'];

			//define the Backbone app here, to be sure its always available;
			$script='var app = new Backbone.Marionette.Application();app.views = {};app.models = {};app.collections = {};';
			foreach(self::$options as $k=>$v){$script.='app.'.$k.'='.json_encode($v).';';}
			if(isset(self::$page['pages'])){$script .= 'app.pages = '.json_encode(self::$page['pages']).';';}
			if(!isset($_COOKIE['pixyt'])){$script .= '$.cookie("pixyt", "'.time().'", {expires:730, path:"/"});';}
			// if(ENV == "dev")$script.='console.log("app var: ",'.json_encode(self::$options).');';
			// $script .= '$(document).on("ready", function () {app.start({});});';
		}
		else{
			$script = '';
		}
		return $script;
	}
	
	public function get_tpl($tpl){
		return str_replace(PHP_EOL,'',str_replace('	','',file_get_contents(ROOT.$tpl.'.html')));
	}

	public function tpl_data($data,$path=''){
		if($path=='/404')return;
		$clean = new stdClass;
		$clean->url = ($path=='/index')?'/':$path;
		foreach($data as $field=>$value){
			if($field=='content'){
				$clean->content = array();
				if(is_array($value)||is_object($value)){
					foreach($value as $f => $v){
						$v->key = $f;
						$tree = $this->tpl_data($v,$path.'/'.$f);
						if(!empty($tree))$clean->content[] = $tree;
					}
				}
				else{
					$clean->content = $value;
				}
			}
			else{
				$clean->$field = $value;
			}
		}
		return $clean;
	}

	public static function _strip($data){
		return $data;
		//Not working atm..
		$encoded = json_encode($data);
		$stripped = str_replace('\t','',$encoded);
		$stripped = str_replace('\n','',$stripped);
		return json_decode($stripped);
	}	
}
?>