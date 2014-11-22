 <?php
class Organize extends Interfaces{
	function __construct(){
		parent::__construct();
		if(!$_SESSION['uid']){
			$this->noaccess();
		}
	}
	
	public function directory(){
		// $r='';
		// if(!IS_AJAX){
		// 	$sidebar = dv('navigation');
		// 	$sidebar .= dv('toolbox').lnk('<img src="/img/icons/1.0/add-stack.png" alt="create stack" title="Create Stack"/>','stacks/create',array(),array('data-type'=>'popup')).lnk('<img src="/img/icons/1.0/upload-photos.png" alt="upload" title="Upload some photos"/>','stacks/upload',array(),array('data-type'=>'popup')).xdv();
		// 	$sidebar .= dv('tab');
		// 	$sidebar .= dv('denomination').translate('photos').xdv();
		// 	$sidebar .= dv('el ajax','','data-url="'.HOME.'organize" data-containerid="pagelet" data-gethtml="true"').'<span class="bold">'.translate('all').'</span>'.xdv();
		// 	$sidebar .= xdv();
		// 	$sidebar .= dv('tab');
		// 	$sidebar .= dv('denomination').translate('sites').xdv();
		// 	foreach(App::$user->sites() as $site){
		// 		$sidebar .= dv('el softlink','','data-url="site/'.$site->id.'/edit" data-containerid="pagelet" data-gethtml="true"').'<img class="icon" src="/img/site/100.png" height="18" width="18" alt="site"/>'.$site->url.xdv();
		// 	}
		// 	$sidebar .= dv('el softlink','','data-url="website" data-containerid="pagelet" data-gethtml="true"').'<span class="bold">+'.translate('create').'</span>'.xdv();
		// 	$sidebar .= xdv();
		// 	$sidebar .= xdv();//navigation
		// 	T::sidebar($sidebar);
		// 	if(File::getUsage(App::$user->id,true) > App::$user->allocation){
		// 		Msg::notify(translate('You have no more diskspace!'));
		// 	}
		// 	elseif(File::getUsage(App::$user->id,true)*1.2 > App::$user->allocation){
		// 		Msg::notify(translate('You are running low on disk space.'));
		// 	}
		// }
		$query = new Query();
		$d = $query->select('genre')->from('Stack')->where('uid',$_SESSION['uid'])->group_by('genre')->get();
		
		$genres=array();
		$genres['any'] = translate('any');
		foreach($d as $val){
			$genre = $val->genre;
			if(!empty($genre)&&!isset($genres[$genre])){
				$genres[$genre] = $genre;
			}
		}
		
		$max =  255;
		//T::$page['title'] = t('Organize');
		if(!isset($_REQUEST['genre'])){
			$_REQUEST['genre'] = 'any';
		}
		if(!isset($_REQUEST['year'])){
			$_REQUEST['year'] = 'any';
		}
		if(!isset($_REQUEST['month'])){
			$_REQUEST['month'] = 'any';
		}
		if(isset($_REQUEST['max'])){
			$max=$_REQUEST['max'];
		}
		if(!isset($_REQUEST['otype'])){
			$_REQUEST['otype']='stack';
		}
		if(!in_array($_REQUEST['otype'],array('stack','photo'))){$_REQUEST['otype'] = 'stack';}
		$collection = new Collection(ucfirst($_REQUEST['otype']));
		$collection->where('uid',$_SESSION['uid']);
		if($_REQUEST['genre']!='any'){
			$collection->where('genre',$_REQUEST['genre']);
		}
		
		if($_REQUEST['year']!='any'&&$_REQUEST['month']!='any'){
			$collection->where('created >',mktime(0,0,0,$_REQUEST['month'],0,$_REQUEST['year']));
			$collection->where('created <',mktime(0,0,0,$_REQUEST['month']+1,0,$_REQUEST['year']));
		}
		elseif($_REQUEST['year']!='any'){
			$collection->where('created >',mktime(0,0,0,0,0,$_REQUEST['year']));
			$collection->where('created <',mktime(0,0,0,0,0,$_REQUEST['year']+1));
		}
		//$collection->limit(100);
		// $r .= dv('uploadBox');
		// $r .= dv('','root','data-s="'.($_REQUEST['s']+1).'" data-otype="'.$_REQUEST['otype'].'" data-genre="'.$_REQUEST['genre'].'" data-year="'.$_REQUEST['year'].'" data-month="'.$_REQUEST['month'].'"');
		//T::js('function updateContent(){var r =$("#root.organize");r.load(HOME+"organize.html",r.data());}');
		//$objects = $collection->get();
		//T::data($collection->data());
		return $collection->data();
		// $dummy = new Stack();
		// foreach($objects as $o){
		// 	//$r .= $this->html($o->display('editPreview'));
		// }
		// $r .= '<br class="clearfloat"/>'.xdv();//root
		// $r .= xdv();//uploadBox
		// return $r;
	}
}
?>