<?php
class Account extends Interfaces{

	// do not allow public access to /account/*
	function __construct(){
		parent::__construct();
		if(!$_SESSION['uid']){
			$this->noaccess();
		}
	}

	public function usage(){
		$used = File::getUsage($_SESSION['uid']);
		$allocation = App::$user->allocation;
		$percentage = round(($used/$allocation)*100,2);
		return array('used'=>$used,'allocated'=>$allocation,'percentage'=>$percentage);
	}
	
	private function followers(){
		$r = '';
		$r .= dv('d750 center');
		$r .= '<h1>You have '.App::$user->getFollowersCount().' followers.</h1>';
		foreach(App::$user->followers as $follower){
			$r .= $follower->display('preview');
		}
		$r .= xdv();
		return $r;
	}
	
	public function logout(){
		$_SESSION['uid']=0;
		// invalidate MessagesAuth token
		App::$user->destroyMessagesAuthCode();
		App::$user=new User(0);
		return true;
	}
	
	private function following(){
		$r = '';
		$r .= dv('d750 center');
		$r .= '<h1>You are following '.App::$user->getFollowingCount().' people.</h1>';
		foreach(App::$user->following as $follower){
			$r .= $follower->display('preview');
		}
		$r .= xdv();
		return $r;
	}
	
	public function validateUser(){
		if(!isset($_REQUEST['uid'])){
			self::addError('UID not defined for account::validate',40);
		}
		$u = new User($_REQUEST['uid']);
		$u->validate(!isset($_REQUEST['unvalidate']));
	}
	
	public static function validatePhoto(){
		if(!isset($_REQUEST['pid'])){
			self::addError('PID not defined for account::validate',40);
		}
		$v=4;
		if(isset($_REQUEST['unvalidate'])){$v=3;}
		$u = new Photo($_REQUEST['pid']);
		$u->access=$v;
		$u->update(true);
	}
	
	public function activity($id=-1){
		$r = '';
		if(empty($id)){$id=-1;}
		$id = intval($id);
		$r .= dv('accountActivity');
		T::$page['title'] = translate('Pixyt');
		$this->sidebar(' ');
		//USER TABS
		$r .= dv('').dv('tab').lnk(translate('New message'),'message/compose',array(),array('data-type'=>'popup','class'=>'btn')).xdv();
		if($id==-1){$c='grey';}else{$c='';}
		$r .= dv('tab').lnk(translate('See all'),'account/activity',array(),array('class'=>$c)).xdv();
		
		$q = new Query();
		$q->select('from')->from('Message')->where(array('uid'=>$_SESSION['uid'],'via !='=>'log'))->group_by('from');
		$c = $q->get();
		$ids=array();
		if(!empty($c)){
			foreach($c as $d){
				$ids[] = $d->from;
				$user = new User($d->from);
				$r .= dv('tab').lnk($user->fullName(),'account/activity/'.$user->id).xdv();
			}
		}
		$r .= xdv();
		$r .= dv('','activities');
		$feedback_collection = new Collection('Feedback');
		$feedback_collection->limit(50)->where('proprietor',$_SESSION['uid']);
		if($id != -1){
			$feedback_collection->where('uid',$id);
		}
		$feedbacks = $feedback_collection->get();
		
		$message_collection = new Collection('Message');
		$message_collection->limit(50)->where('via !=','log');
		if($id != -1){
			$message_collection->where('((`uid` = '.App::$user->id.' AND `from` = '.$id.') OR (`from` = '.App::$user->id.' AND `uid` = '.$id.'))');
		}
		else{
			$message_collection->where('uid',$_SESSION['uid']);
		}
		
		$messages = $message_collection->get();
		
		$items = array_merge($messages,$feedbacks);
		
		$stories = array();
		foreach($items as $item){
			$stories[$item->created] = $item;
		}
		ksort($stories);
		$stories = array_reverse($stories);
		
		return $stories;
		/*
		if($id != -1){
			$u = new User($id);
			$form = new Form('Message',NULL,true,array('ajax'=>true,'class'=>'full'));
			$title = translate('Subject');
			$form->input('uid',array('type'=>'hidden','value'=>$u->id));
			$form->input('from',array('type'=>'hidden','value'=>$_SESSION['uid']));
			$form->textarea('content');
			$form->button('send');
			$r .= '<h1>'.$u->fullName('link').'</h1><h2 class="lightgrey">('.$message_collection->count().' messages, '.$feedback_collection->count().' feedbacks)</h2>';
			$r .= dv('message').$form->content().xdv();
		}
		
		//$cols=array();
		//$i=0;
		foreach($stories as $story){
			$r .= $story->display('activity');
		//	$cols[$i][] = $story->display('activity');
		//	if($i==1){$i=0;}else{$i++;}
		}
		
		//if(!empty($cols[0])){$r .= dv('d2 col','history').implode('',$cols[0]).xdv();}
		//if(!empty($cols[1])){$r .= dv('d2 col').implode('',$cols[1]).xdv();}
		$r .= xdv().'<br class="clearfloat"/>';
		$r .= xdv();
		return $r;
		*/
	}

	/**
	 * Function messages
	 * intended to control /account/messages for the main messages table
	 * @return array
	 */
	public function messages($region=null, $id=-1, $from=0){
		// messages main
		if($region == null)
		{
			return array('message_list' => array());
		}
		// conversations (list) region
		if($region == 'conversations')
		{
			/*
				EXAMPLE SQL

				SELECT m.uid, m.from, MAX(m.created) as created, u.id, u.firstname, u.lastname FROM Message as m, User as u WHERE (m.uid = 5 OR m.from = 5) AND ((m.uid != 5 AND u.id = m.uid) OR (m.uid = 5 AND u.id = m.from)) GROUP BY u.id ORDER BY created DESC;
			*/

			// TODO: Convert to Collections
			// $query_str = "SELECT m.uid, m.from, m.content, MAX(m.created) as created, u.id, u.firstname, u.lastname FROM Message as m, User as u WHERE (m.uid = ? OR m.from = ?) AND ((m.uid != ? AND u.id = m.uid) OR (m.uid = ? AND u.id = m.from)) GROUP BY u.id ORDER BY created DESC;";
			$query_str = "SELECT sub.*, COUNT(case when sub.unread = 1 and sub.uid = ? then sub.unread end) as ucount FROM (SELECT m.id, m.uid, m.from, m.content, m.created as created, m.unread as unread, u.id as contact_id, u.firstname, u.lastname FROM Message as m, User as u WHERE ((m.uid = ? AND m.from=u.id ) OR (m.from=? AND m.uid=u.id )) AND ((m.uid = ? AND m.archived_receiver = 0) OR (m.from = ? AND m.archived_sender = 0)) ORDER BY m.id DESC) as sub GROUP BY sub.contact_id ORDER BY MAX(sub.created) DESC";
			$qd = array(App::$user->id, App::$user->id, App::$user->id, App::$user->id, App::$user->id);
			$conversations_r = App::$db->query_prepare($query_str);
			$conversations_r->execute($qd);

			$conversations = array();

			while($row = $conversations_r->fetch())
			{
				$conversations[] = array(
					'contact_id' => $row['contact_id'],
					'contact_name' => stripslashes($row['firstname'].' '.$row['lastname']),
					'contact_lastmsg' => stripslashes($row['content']),
					'last_update' => $row['created'],
					'profile_picture_url' => '',
					'unread' => $row['ucount'] > 0 ? true : false
				);
			}

			return array('conversations' => $conversations);
		}
		// Suggest contacts for a new conversation, according to their 
		else if($region == 'suggest')
		{
			if(!isset($_POST['keyword']))
			{
				return array();
			}
			$suggestion_limit = 8;
			// PDO will later do the required escaping to prevent SQL injections
			$keyword = trim($_POST['keyword']);
			$words = explode(' ',$keyword);
			// TODO: Convert to Collections

			$qd = array($keyword.'%', $keyword.'%', App::$user->id);
			if(count($words) <= 1)
			{
				// single word. look up in both firstname and lastname
				// $query_str = "SELECT * FROM `User` WHERE `firstname` != '' AND `lastname` != '' AND (levenshtein(?, `firstname`) BETWEEN 0 AND 3 OR levenshtein(?, `lastname`) BETWEEN 0 AND 3) AND `id` != ? LIMIT ".$suggestion_limit;
				$query_str = "SELECT * FROM `User` WHERE `firstname` != '' AND `lastname` != '' AND (`firstname` LIKE ? OR `lastname` LIKE ?) AND `id` != ? ORDER by `firstname` LIMIT ".$suggestion_limit;
			}
			else
			{
				// two words
				$qd = array($words[0].'%',
							$words[1].'%',
							$words[1].'%',
							$words[0].'%',
							App::$user->id
						);

				$query_str = "SELECT * FROM `User` WHERE `firstname` != '' AND `lastname` != '' AND (`firstname` LIKE ? OR `lastname` LIKE ? OR `firstname` LIKE ? OR `lastname` LIKE ?) AND `id` != ? ORDER BY `firstname` LIMIT ".$suggestion_limit;
			} 

			$suggestions_r = App::$db->query_prepare($query_str);
			$suggestions_r->execute($qd);

			$suggestions = array();
			
			// somewhat works
			while($row = $suggestions_r->fetch())
			{
				$suggestions[] = array(
					'cid' => 0,
					'contact_id' => $row['id'],
					'contact_name' => stripslashes($row['firstname'] . ' ' . $row['lastname']),
					'profile_picture_url' => ''
				);
			}

			return $suggestions;
		}
		// Create or retrieve an auth key for messages that will later be used in socket.io handshake
		else if($region == 'authorize')
		{
			return array('id' => App::$user->id, 'auth_key' => App::$user->messagesAuthCode());
		}
	}
	
	public function settings($tab=''){
		return App::$user->display('settings',array($tab));
	}
	
	public function organize(){
		header('Location: '.HOME.'organize',301);
	}
	
	public function loadvcf(){
		if(!IS_AJAX){
			$this->contacts();
			return false;
		}
		if(!isset($_REQUEST['vcffile']) || empty($_REQUEST['vcffile'])){return json_encode(array('error'=>'No file given'));}
		if(!isset($_REQUEST['i'])){return json_encode(array('error'=>'No index given'));}
		if(!isset($_SESSION['contacts'])){$_SESSION['contacts'] = array();}
		$res = new stdClass();
		$res->error = NULL;
		$res->vcffile = urldecode($_REQUEST['vcffile']);
		$res->i = intval($_REQUEST['i']);
		$res->finished = false;
		
		$file = ROOT.$_REQUEST['vcffile'];
		$s = $_REQUEST['i'];
		$max = 50000;
		$end = $s+$max;
		
		if(!file_exists(ROOT.$res->vcffile)){
			$res->error = translate('File does not exists!');
			exit(json_encode($res));
		}
		if(!$filedata = file_get_contents(ROOT.$res->vcffile)){
			$res->error = translate('Failed to get file content!');
			exit(json_encode($res));
		}
		
		$contacts = explode('BEGIN:VCARD',$filedata);
		$t = count($contacts)-1;
		if($t == 0){$res->error = translate('No contacts found in file.');}
		$parse='';
		for($i=$s;$i<$end;$i++){
			$parse .= 'BEGIN:VCARD'.$contacts[$i];
		}
		$res->i = $i;
		$conv = new vcard_convert(array());
		$conv->fromText($parse);
		foreach($conv->cards as $card){
			$contact = new Contact();
			$contact->firstname = rmvmrkr($card->firstname);
			$contact->lastname = rmvmrkr($card->surname);
			$contact->jobtitle = $card->title;
			$contact->company = $card->organization;
			$phones = array();
			$emails = array();
			$websites = array();
			$address = array();
			if(!empty($card->home['phone'])){$phones['home']=$card->home['phone'];}
			if(!empty($card->home['email'])){$emails[]=$card->home['email'];}
			if(!empty($card->home['url'])){$websites[]=$card->home['url'];}
			if(!empty($card->home['fax'])){$phones['fax']=$card->home['url'];}
			if(!empty($card->home['addr1'])){$address['home']['addr1']=$card->home['addr1'];}
			if(!empty($card->home['addr2'])){$address['home']['addr2']=$card->home['addr2'];}
			if(!empty($card->home['city'])){$address['home']['city']=$card->home['city'];}
			if(!empty($card->home['state'])){$address['home']['state']=$card->home['state'];}
			if(!empty($card->home['zipcode'])){$address['home']['zipcode']=$card->home['zipcode'];}
			if(!empty($card->home['country'])){$address['home']['country']=$card->home['country'];}
			
			if(!empty($card->work['phone'])){$phones[]=$card->work['phone'];}
			if(!empty($card->work['email'])){$emails[]=$card->work['email'];}
			if(!empty($card->work['url'])){$websites[]=$card->work['url'];}
			if(!empty($card->work['fax'])){$phones['fax']=$card->work['url'];}
			if(!empty($card->work['addr1'])){$address['work']['addr1']=$card->work['addr1'];}
			if(!empty($card->work['addr2'])){$address['work']['addr2']=$card->work['addr2'];}
			if(!empty($card->work['city'])){$address['work']['city']=$card->work['city'];}
			if(!empty($card->work['state'])){$address['work']['state']=$card->work['state'];}
			if(!empty($card->work['zipcode'])){$address['work']['zipcode']=$card->work['zipcode'];}
			if(!empty($card->work['country'])){$address['work']['country']=$card->work['country'];}
			
			if(!empty($card->email)){$emails[] = $card->email;}
			if(!empty($card->email2)){$emails[] = $card->email2;}
			if(!empty($card->email3)){$emails[] = $card->email3;}
			
			if(!empty($card->mobile)){$phones[] = $card->mobile;}
			
			$contact->phones = $phones;
			$contact->emails = $emails;
			$contact->websites = $websites;
			$contact->address = $address;
			
			if(!empty($card->birthday)){
				$contact->birthday = $card->birthday['y'].'-'.$card->birthday['m'].'-'.$card->birthday['d'];
			}
			$contact->cameFrom = $_REQUEST['vcffile'];
			
			if(!empty($contact->firstname) && (!empty($emails) || !empty($phones))){
				if($contact->insert()){
					$_SESSION['contacts']['added']++;
				}
				else{
					$_SESSION['contacts']['failed']++;
				}
			}
			else{
				$_SESSION['contacts']['failed']++;
			}
		}
		if((int)$res->i == 0){$res->error = 'Wrong index (i:'.$i.', end:'.$end.', t:'.$t.')';}
		$res->added = intval($_SESSION['contacts']['added']);
		$res->failed = intval($_SESSION['contacts']['failed']);
		if($i >= $t){
			$res->finished = true;
			$res->text = translate('Done!');
			$_SESSION['contacts']['failed'] = 0;
		}
		else{
			$res->text = $end.'/'.$t;
		}
		exit(json_encode($res));
	}
	/*
	private function uploadForm(){
		T::$jsfoot[] = '
$(function () {
    $("#fileupload").fileupload({
		drop: function (e, data) {
			$.each(data.files, function (index, file) {
				//LPalert("Dropped file: " + file.name);
			});
		},
		change: function (e, data) {
			$.each(data.files, function (index, file) {
			//	LPalert("Selected file: " + file.name);
			})
		},
		add: function (e, data) {
			var jqXHR = data.submit()
				.success(function (result, textStatus, jqXHR) {
					//LPalert("Upload successfull");
				})
				.progress(function (jqXHR) {
					LPalert("Upload error: "+errorThrown);
				})
				.error(function (jqXHR, textStatus, errorThrown) {
					LPalert("Upload error: "+errorThrown);
				})
				.complete(function (result, textStatus, jqXHR) {
					LPalert("Upload complete");
				});
		},
        dataType: "json",
        url: "'.HOME.'upload.php",
        done: function (e, data) {
            $.each(data.result, function (index, file) {
				uploadSuccess(data.result[0]);
            });
        }
    });
});
var uploadSuccess = function(result){
	if(result.error != null){alert(result.error);return false;}
	console.log(result);
	$("#appPanel").slideToggle("slow", function() {
		$("#appPanel").html("<h2>'.translate('Parsing').' "+result.name+"...</h2><div id=\'vcfUploadProgress\'>Please wait...</div>");
		$("#appPanel").slideToggle("slow");
		
		loadVCF(result.tmp_path,0);
	});
}
var loadVCF = function(vcffile,i){
	$.ajax({
	  url: "'.HOME.'account/loadvcf",
	  dataType: "json",
	  data: {"i":i,"vcffile":vcffile},
	  success: function(data){
		  if(data.error != null){$("#vcfUploadProgress").html(data.error);alert(data.error);return false;}
		  else if(data.finished == true){$("#vcfUploadProgress").html(data.text);alert("'.translate('Your address book was successfully imported!').' "+data.added+" '.translate('contacts added').', "+data.failed+" '.translate('contacts could not be added (they had no email nor phone or had duplicate name...)').'");return true;}
		  else{$("#vcfUploadProgress").html(data.text);setTimeout("loadVCF(\'"+data.vcffile+"\',\'"+parseInt(data.i)+"\')",100);}
		 }
	});
}
';
		return '<form id="fileupload" action="upload.php" method="POST" enctype="multipart/form-data"><input type="hidden" name="uploadtype" value="contacts"/><input type="file" class="files" name="files[]" id="fileupload" multiple></form>';
	}
	
	public function contacts(){
		
		T::$actionBar[] = lnk('view all',true,array(),array('class'=>'btn'));
		if(isset($_REQUEST['do']) && $_REQUEST['do'] == 'selectvcf'){
			T::$body[] = dv('splitLeft').dv('padder','appPanel').translate('Please save all you contacts in VCF format').' <div class="info">in you address book: <ol><li>click on one contact</li><li>press CMD+A</li><li>click in File (menu)</li><li>export</li><li>Export VCards</li><li>choose a location</li><li>Save</li><li>use the form on the right to point to the file</li></ol></div>'.xdv().xdv();
			T::$body[] = dv('splitRight').dv('padder').$this->uploadForm().xdv().xdv();
		}
		else{
			T::$body[] = dv('mycontacts');
			T::$actionBar[] = lnk('add contacts',true,array('do'=>'selectvcf'),array('class'=>'btn'));
			T::$page['title'] = translate('Contacts');
			$myContacts = new Collection('Contact');
			$myContacts->uid = App::$user->id;
			$max = 200;
			if(isset($_REQUEST['first'])){$first = $_REQUEST['first'];}else{$first = 0;}
			$last = $first+$max;
			
			$contacts = $myContacts->content(3,$first,$last,true,'firstname',false);
			if(!empty($contacts)){T::$body[] = $contacts;}
			else{T::$body[] = '<p>'.translate('You do not have any contacts yet.').'</p>';}
			T::$body[] = xdv();
		}
	}
	*/
}
?>