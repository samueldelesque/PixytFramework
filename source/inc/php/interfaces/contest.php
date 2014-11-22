<?php
class Contest extends Interfaces{
	public function display($mode,$settings=array()){
		return call_user_func_array(array($this,$mode),$settings);
	}
	public static $jury = array(
		0=>array(
			'name'=>'John Ross',
			'function'=>'Advertising photographer',
			'location'=>'London',
			'site'=>'http://johnross.co.uk',
			'img'=>'john_ross.jpeg'
		),
		1=>array(
			'name'=>'Dominique Coulon',
			'location'=>'Lille',
			'function'=>'Galery owner',
			'site'=>'https://www.facebook.com/pages/quai26-galeriephotos/175456642267',
			'img'=>'dominique_coulon.jpg'
		),
		2=>array(
			'name'=>'Patrick Boackaert',
			'location'=>'Belgium',
			'function'=>'Teacher',
			'site'=>'',
			'img'=>'Patrick_Bockaert.jpg'
		),
		3=>array(
			'name'=>'Dara Mon',
			'location'=>'Lille',
			'function'=>'Advertising photographer',
			'site'=>'http://studio-dara.fr/',
			'img'=>'dara_mon.jpg'
		),
		4=>array(
			'name'=>'Asger Mortensen',
			'function'=>'Portrait/editorial photographer',
			'location'=>'Copenhagen',
			'site'=>'http://www.facebook.com/pages/Asger-Mortensen-Photography/336216289832615',
			'img'=>'asger_mortensen.jpg'
		),
	);
		
	public static $sponsors = array(
		0=>array(
			'name'=>'L\'Instant - Monaco',
			'function'=>'Lab',
			'location'=>'Monaco',
			'site'=>'http://linstant.mc',
			'img'=>'linstant-logo.png'
		),
		1=>array(
			'name'=>'Light Architect',
			'location'=>'Lille',
			'function'=>'Photography agency',
			'site'=>'http://lightarchitect.com/',
			'img'=>'lightarchitect-logo-black.png'
		),
		2=>array(
			'name'=>'Quai26',
			'location'=>'Lille',
			'function'=>'Gallery',
			'site'=>'http://www.quai26.fr/',
			'img'=>'galerie_quai26.jpg'
		),
		3=>array(
			'name'=>'Photo Time',
			'location'=>'Lille',
			'function'=>'Lab',
			'site'=>'http://phototime.fr/',
			'img'=>'labo_phototime.png'
		),
	);
	
	public static $prizes = array(
		0=>array(
			'name'=>'Jury Prize',
			'description'=>'Your artworks printed and exhibited.',
			'img'=>'jury-prize.png'
		),
		1=>array(
			'name'=>'Agency Prize',
			'description'=>'Your portfolio Print+Web created by the agency.',
			'img'=>'agency-prize.png'
		),
		2=>array(
			'name'=>'Public Prize',
			'description'=>'100€ Cash + 3 40x60 fine art prints of your work.',
			'img'=>'public-prize.png'
		),
	);
	
	public static $rules = array(
		'fr'=>'Participation
Quiconque âgé de 18 ans ou plus peux participer au concours. Si vous avez moins de 18 ans vous pouvez participer avec l\'accord et supervision de vos parents ou responsables légaux.

La participation se fait en ligne sur http://pixyt.com, et afin les participants doivent avoir ou créer un compte et ainsi accepter les condition d\'utilisation et de vie privée (http://pixyt.com/privacy et http://pixyt.com/terms). Tout participant ouvrant un compte peut décider de supprimer son compte à la fin du concours. Toute suppression antérieure entrainerait la suppression de la participation au concours.

Frais d\'inscription
Le concours est entièrement gratuit.

Prix
Les prix consistes en trois catégories distinctes: Prix du Jury, Prix de l\'Agence et prix du Public.

Le Prix du Jury contient des tirages réalisés par le laboratoire l\'Instant - Monaco, exposés à la galerie Quai26 à Lille suite à quoi l\'auteur peux réclamer ses oeuvres + un compte expert sur Pixyt, ce qui inclut de l\'espace de stockage et un site internet avec nom de domaine propre + une interview de l\'auteur qui sera publiée avec ses photos.

Le prix de l\'Agence contient un compte Pixyt premium pendant 3 ans, ce qui inclut un site avec nom de domaine propre + un portfolio complet créer par l\'agence et imprimé en deux exemplaires: 1 pour le photographe, et 1 pour l\'Agence pour le présenter à des clients potentiels. L\'auteur devra fournir entre 20 et 50 images supplémentaires pour la réalisation de son portfolio.

Le prix du Public contient 100€ cash + 3 tirages de 40x60cm réalisés par PhotoTime Lille + un compte premium Pixyt pendant 1 ans, ce qui inclut un site internet avec nom de domaine propre.

Jury
Le jury est composé de professionnels et enseignant à travers l\'Europe. Le prix du Public est décidé par vote de toutes les personnes utilisant le site. Il est strictement interdit de voter depuis plusieurs comptes. Toute personne contrevenant à cette règle auront leurs comptes suspendus sans notification préalable de notre part.

Droit à l\'image
En soumettant vos images, les auteurs doivent être certain d\'être en droit de les utiliser sans enfreindre le code de Propriété Intellectuelle ou une quelconque autre loi visant à protéger la personne, et de doit pas non-plus être nuisible à un tiers.
Si des modèles apparaissent sur l\'image, l\'auteur doit obtenir les autorisations appropriées avant de soumettre les images, de même si des oeuvres d\'art ou autre objets sujet au code la propriété intellectuelle apparaissent.

Licence
En soumettant ses images, l\'auteur autorise les organisateurs de manière perpétuelle et irrévocable d\'utiliser ces images dans le but de promouvoir le concours et le site par tous les moyens connus et encore inconnus dans toutes les formes de media existantes. Cela inclus mais n\'est pas limité à: exposition des images des gagnant, publication dans des livres ou revues, publication sur les réseaux sociaux, publication sur site propre.

Annulation
Light Architect se réserve le droit d\'annuler le concours sans notification préalable à un quelconque moment en cas d\'incident technique, opération frauduleuse, infection par virus informatique, ou tout autre problème technique pouvant subvenir ou tout autre évènement que qui pourrait être nuisible à l\'agence ou ses partenaires.

Vie privée
Les participants acceptent que leur données personnelles, y compris nom prénom et adresse pourront être divulgué, enregistré et utilisé dans le cadre du concours.
',
		'en'=>'Entry
Anyone over 18 is entitled to participate in the contest. If under 18, you may enter with authorization and supervision by your parents or legal guardian.

The submission is done online at http://pixyt.com, and to do so the contestant must create a Pixyt account and therefore also agree to Pixyt Privacy and Terms of service (http://pixyt.com/privacy and http://pixyt.com/terms). Any contestant can decide to delete their account once the contest is over. If you delete your account prior that time, your submission will be deleted.

Fee
The contest is entirely free.

Prices
The prices consists of three distinct prices: Jury Price, Agency Price and Public Price.

The Jury Prize consists of fine art prints made by the lab l\'Instant, in Monaco that will be exhibited at the gallery Quai26 in Lille after which the author may reclaim his images + an expert account on Pixyt to access all the functionalities given  for free, including a website with own domain and the disk space entitled on any expert account + an interview with the author which will be published along with the photos.

The Agency Prize consists a premium Pixyt account, including a website with own domain and the disk space entitled by any premium account + a full portfolio book created by the Agency and printed out in a book format in two editions: one for the author, and one for the Agency in order to present the author to potential clients. The author will have to provide 20 to 50 images to fill the book in order to be eligible for that prize.

The Public Prize consists of 100€ cash + 3 40x60cm prints made by PhotoTime - Lille + a premium Pixyt account, including a website with own domain and the disk space entitled by any premium account.

Jury
The jury is composed of professionals and teachers from across Europe. The Public price is voted by anyone using the site. It is strictly forbidden to vote from multiple accounts. Any voter failing to identify themselves or appearing under several identities may be banned from voting, and their account will be closed without further notice.

Releases
By submitting their content, authors must verify that their content does not violate any third party copyright, moral rights or otherwise break any law or be threatening to a third party.
If models appear on the photographs, the authors must make sure they have the releases allowing them to use the photo.
If statues/artworks or other material having intellectual property appear on their photo they must make sure to have the appropriate authorization to use their images.

Licence
By entering the Contest, all entrants grant an irrevocable, perpetual, worldwide non-exclusive license to the Light Architect agency and its partners, to reproduce, distribute, display and create derivative works of the entries (along with a name credit) in connection with the Contest and the Pixyt site, and promotion of the Contest, in any media now or hereafter known, including, but not limited to: Display at a potential exhibition of winners; publication of a book featuring select entries in the Contest; publication on social pages; publication on own website.

Cancelation
Light Architect reserves the rights to cancel the contest at any time without prior notice in case of a technical error, fraud, infection by computer virus or any kind of malware, security issues, or any other event that may cause the web service to fault or any other event that would be harmful for the Agency itself or its partners.

Privacy
Entrants agree that personal data, especially name and address, may be processed, shared, and otherwise used in the context of the Contest.'
	);
	
	public function construct(){
		return true;
	}
	
	public function rules(){
		$r = '';
		$r .= dv('rules');
		$r .= '<h1>'.translate('Contest rules').'</h1>';
		switch($_SESSION['lang']){
			case 'fr_FR':
				$l='fr';
			break;
			default:
				$l='en';
			break;
		}
		$r .= nl2br(self::$rules[$l]);
		$r .= xdv();
		return $r;
	}
	
	public function winners(){
		$r = '';
		T::$page['title'] = 'Pixyt Contest results';
		$r .= dv('d1000 center');
		$r .= dv('','actionbar').translate('We would like to thank all our participants for all these amazing images.').' '.translate('You can see all the entries here:').' '.lnk(translate('submissions'),'contest/submissions').xdv();
		$r .= '<h1>'.translate('Pixyt Contest results').'</h1>';
		$r .= '<h3 class="padder">'.translate('With about 500 applicants, and over 1000 photos, choosing the contest winner has been really difficult.').' '.translate('Many images caught our attention, and we would have liked to give out many more prizes.').' '.translate('However, since we could only select one for each category, here are the winners:').'</h3>';
		$winner = new User(263);
		$entry = new Stack(822);
		$slider = dv('d900 h600 relative').dv('slider');
		foreach($entry->children() as $p){
			if($p->className == 'Photo'){
				$slider .= dv('slide').lnk($p->img('horizont'),'contest/synchrodogs_jury_prize').xdv();
			}
		}
		$slider .= xdv().xdv();
		$r .= dv('marger').dv('padded').'<h2>'.lnk(translate('Jury Prize'),'contest/synchrodogs_jury_prize').'</h2><p>'.translate('The most prestigious prize of this award, judged by professionnal photographers, teachers and gallery owners. The Jury focused on selecting the entry which was the most creative, which was eligible for an exhibition and matched the theme.').'</p><p>'.translate('The winner of this category won the opportunity to be printed and exhibited in L\'instant in Monaco and then in the gallery Quai26 in Lille for a second exhibition.').'</p><p>'.translate('The winner of the Jury Prize is:').' '.lnk($winner->fullName(),'contest/synchrodogs_jury_prize').'</p>'.xdv().$slider.xdv();
		
		$winner = new User(159);
		$entry = new Stack(616);
		$slider = dv('d900 h600 relative').dv('slider');
		foreach($entry->children() as $p){
			if($p->className == 'Photo'){
				$slider .= dv('slide').lnk($p->img('horizont'),'contest/kimdary_yin_agency_prize').xdv();
			}
		}
		$slider .= xdv().xdv();
		$r .= dv('marger').dv('padded').'<h2>'.lnk(translate('Agency Prize'),'contest/kimdary_yin_agency_prize').'</h2><p>'.translate('The Agency Prize winner has been elected by the Light Architect Agency. The aim was to highlight the work of a photographer by producing a unique portfolio, both on the web and in print. The photographer chosen for this prize is:').' '.lnk($winner->fullName(),'contest/kimdary_yin_agency_prize').'</p>'.xdv().$slider.xdv();
		
		$winner = new User(243);
		$entry = new Stack(801);
		$slider = dv('d900 h600 relative').dv('slider');
		foreach($entry->children() as $p){
			if($p->className == 'Photo'){
				$slider .= dv('slide').lnk($p->img('horizont'),'contest/s_m_shajjad_hossain_shajib_public_prize').xdv();
			}
		}
		$slider .= xdv().xdv();
		$r .= dv('marger').dv('padded').'<h2>'.lnk(translate('Public Prize'),'contest/s_m_shajjad_hossain_shajib_public_prize').'</h2><p>'.translate('The Public Prize was determined how many likes the entry were given by users.').' '.translate('The winner of this category is ').lnk($winner->fullName(),'contest/s_m_shajjad_hossain_shajib_public_prize').', '.translate('who got 426').'&hearts;</p>'.xdv().$slider.xdv();
		
		
		$r .= '<h2><span class="blckbgd white padded">'.translate('Honorable mentions').'</span></h2><p>'.translate('Although we could not ellect all the entries we liked, we would like to share these great images, that made it to the top. Keep on the good work and we are sure you will be rewarded - maybe at our next Pixyt contest?').'</p><br/>';
		$r .= dv('d2 col');
		
		$winner = new User(764);
		$entry = new Stack(1437);
		$imgs = dv();
		foreach($entry->children() as $p){
			if($p->className == 'Photo'){
				$imgs .= lnk($p->img('stack'),'stack/'.$entry->id);
			}
		}
		$imgs .= xdv();
		$r .= '<h4><span class="blckbgd white padded">'.translate('Best reportage').'</span></h4>'.$imgs.'<p>'.translate('Congratulation').' '.$winner->fullName('link').', '.translate('your images touched us.').'</p><br/><br/>';
		
		$winner = new User(277);
		$entry = new Stack(839);
		$imgs = dv();
		foreach($entry->children() as $p){
			if($p->className == 'Photo'){
				$imgs .= lnk($p->img('stack'),'stack/'.$entry->id);
			}
		}
		$imgs .= xdv();
		$r .= '<h4><span class="blckbgd white padded">'.translate('Special jury prize').'</span></h4>'.$imgs.'<p>'.translate('Great photos').' '.$winner->fullName('link').'</p><br/><br/>';
		
		$winner = new User(95);
		$entry = new Stack(740);
		$imgs = dv();
		foreach($entry->children() as $p){
			if($p->className == 'Photo'){
				$imgs .= lnk($p->img('stack'),'stack/'.$entry->id);
			}
		}
		$imgs .= xdv();
		$r .= '<h4><span class="blckbgd white padded">'.translate('Funniest scenery').'</span></h4>'.$imgs.'<p>'.translate('Fun and brilliant.').' '.$winner->fullName('link').'</p><br/><br/>';
		
		$winner = new User(1087);
		$entry = new Stack(1776);
		$imgs = dv();
		foreach($entry->children() as $p){
			if($p->className == 'Photo'){
				$imgs .= lnk($p->img('stack'),'stack/'.$entry->id);
			}
		}
		$imgs .= xdv();
		$r .= '<h4><span class="blckbgd white padded">'.translate('Interesting point of view').'</span></h4>'.$imgs.'<p>'.translate('Against the glorification made in magasines, well made').' '.$winner->fullName('link').'</p><br/><br/>';
		
		
		
		$r .= xdv();
		
		
		$r .= dv('d2 col');
		$winner = new User(735);
		$entry = new Stack(1403);
		$imgs = dv();
		foreach($entry->children() as $p){
			if($p->className == 'Photo'){
				$imgs .= lnk($p->img('stack'),'stack/'.$entry->id);
			}
		}
		$imgs .= xdv();
		$r .= '<h4><span class="blckbgd white padded">'.translate('Forever young').'</span></h4>'.$imgs.'<p>'.translate('Keep on the good work').' '.$winner->fullName('link').'. '.translate('We love it.').'</p><br/><br/>';
	
		$winner = new User(19);
		$entry = new Stack(582);
		$imgs = dv();
		foreach($entry->children() as $p){
			if($p->className == 'Photo'){
				$imgs .= lnk($p->img('stack'),'stack/'.$entry->id);
			}
		}
		$imgs .= xdv();
		$r .= '<h4><span class="blckbgd white padded">'.translate('Restfull and contemporary').'</span></h4>'.$imgs.'<p>'.translate('Well appreciated, keep working on it.').' - '.$winner->fullName('link').'</p><br/><br/>';
		
		
		$winner = new User(21);
		$entry = new Stack(588);
		$imgs = dv();
		foreach($entry->children() as $p){
			if($p->className == 'Photo'){
				$imgs .= lnk($p->img('stack'),'stack/'.$entry->id);
			}
		}
		$imgs .= xdv();
		$r .= '<h4><span class="blckbgd white padded">'.translate('Something here...').'</span></h4>'.$imgs.'<p>'.$winner->fullName('link').'</p><br/><br/>';
		
		$r .= xdv().'<br class="clearfloat"/>';
		
		$r .= xdv();
		return $r;
	}
	
	public function s_m_shajjad_hossain_shajib_public_prize(){
		$r = '';
		$r .= dv('d1200 center');
		$r .= dv('marger').'<h1>'.lnk(translate('contest winners'),'contest/winners').' &gt; <span class="blckbgd white padded">'.translate('S M Shajjad Hossain Shajib - Winner of the Public Prize').'</span></h1>'.xdv();
		$p1 = new Photo(8695);
		$p2 = new Photo(8696);
		$p3 = new Photo(8697);
		$r .= dv('d400 col').dv('marger').'<p>'.translate('With no less than 426 likes on his images, Shajib became the most liked entry of the contest.').'</p>'.'<p class="quote padded">'.translate('My name is S.M. Shajjad  Hossain Shajib. I have been graduated from Inha University, South-Korea majoring Electronic Engineering and currently working for Samsung Bangladesh R&D Center as a software Engineer. I felt my passion for Photography in the year of 2010. At that time, I didn\'t have any Camera with me but i liked to see photos of different photographers. In November, 2010 I bought my first Camera that was Canon 500D and started my journey of Photography. I am still a learner. I believe the world of photography is like a deep sea and it might be impossible to learn everything of Photography but i will try my level best to learn as much as i can.').'</p>'.xdv().xdv();
		$r .= dv('d800 col').$p2->img('medium').$p3->img('medium').$p1->img('medium').xdv();
		$r .= xdv();
		return $r;
	}
	
	public function synchrodogs_jury_prize(){
		$r = '';
		T::$page['title'] = 'Synchrodogs - Jury Prize winners';
		T::$page['description'] = translate('The judges unanimously praised this entry for its striking aesthetic qualities and Syncrodogs\' bold, distinctive point of view');
		$r .= dv('d1200 center');
		$r .= dv('marger').'<h1>'.lnk(translate('contest winners'),'contest/winners').' &gt; <span class="blckbgd white padded">'.translate('Tania and Roman - Synchrodogs').'</span></h1>'.xdv();
		$p1 = new Photo(8722);
		$p2 = new Photo(8721);
		$p3 = new Photo(8720);
		$r .= dv('d400 col').dv('marger big grey').'<p>'.translate('Synchrodogs are a talented duo of photographers: Tania and Roman.').'</p><p>'.translate('The judges unanimously praised this entry for its striking aesthetic qualities and Syncrodogs\' bold, distinctive point of view. These contemporary scenes were awarded the artistic prize, winning the opportunity to be exhibited both in').' '.xtlnk('http://www.linstant.mc/','l\'Instant in Monaco').' '.translate('and in the gallery').' '.xtlnk('http://www.facebook.com/pages/quai26-galeriephotos/175456642267','Quai26').' '.translate('in Lille. The judges, the crew at Pixyt and also the owners of gallery Quai26 would like to offer congratulations to Tania and Roman Syncrodogs.').'</p>'.xdv().xdv();
		$r .= dv('d800 col').$p2->img('medium').$p3->img('medium').$p1->img('medium').xdv();
		$r .= xdv();
		return $r;
	}
	
	public function kimdary_yin_agency_prize(){
		$r = '';
		T::$page['title'] = 'Kimdary Yin - Rising editorial photographer';
		T::$page['description'] = translate('Kimdary Yin, an emerging editorial and advertising photographer based in paris was selected by the Light Architect agency');
		$r .= dv('d1200 center');
		$r .= dv('marger').'<h1>'.lnk(translate('contest winners'),'contest/winners').' &gt; <span class="blckbgd white padded">'.translate('Kimdary Yin, selected by Light Architect').'</span></h1>'.xdv();
		$p1 = new Photo(8137);
		$p2 = new Photo(8124);
		$p3 = new Photo(8126);
		$r .= dv('d2 col').dv('marger big grey').translate('Kimdary Yin, an emerging editorial and advertising photographer based in paris was selected by the Light Architect agency to have her portfolio produced as the winner of the agency prize for the Pixyt Contest.').xdv().xdv();
		$r .= dv('d2 col').$p1->img('medium').$p2->img('medium').$p3->img('medium').xdv();
		$r .= xdv();
		return $r;
	}
	
	public function myentries(){
		$r = '';
		$c = new Collection('Feedback');
		$c->uid = App::$user->id;
		$c->type=3;
		$c->load($_REQUEST['s'],50);
		$h=600;
		$r .= dv('d1200 center','entries_'.$_REQUEST['s']);
		foreach($c->results as $entry){
			$s = new Stack($entry->objectId);
			if(count($s->children()) > 0){
				$c = '';
				$t = count($s->children());
				if($t>3){$t=3;}
				$w = round(1200/$t);
				$i=0;
				foreach($s->children() as $p){
					if($p->className == 'Photo'){
						$c .= dv('d'.$t.' col').lnk($p->img('medium'),'photo/'.$p->id.'').xdv();
						$i++;
					}
				}
				if($i>0){
					$r .= dv('entry');
					$r .= '<h3>'.$s->heart().lnk($s->title,'stack/'.$s->id).' '.translate('by').' '.User::$users[$s->uid]->fullName('link').' <span class="right lightgrey">('.$s->popularity.'&hearts;)</span>';
					if(in_array(6,App::$user->data['settings']['accesses'])){
						$r .= $s->rate();
					}
					$r .= '</h3>';
					$r .= dv('images').$c.xdv().'<br class="clearfloat"/>';
					$r .= xdv().'<br class="clearfloat"/>';
				}
			}
		}
		$r .= xdv();
		return $r;
	}
	
	public function submissions(){
		$r = '';
		T::$page['title'] = translate('Photography contest - submissions');
		$contest = new Collection('Stack');
		$contest->prid('=',27,true);
		
		$contestPhotos = new Collection('Photo');
		$contestPhotos->prid('=',27,true);
		
		$m=30;
		if(!isset($_REQUEST['orderBy'])){$_REQUEST['orderBy']='rating';$order = 'rating';$desc=true;}
		else{
			switch($_REQUEST['orderBy']){
				case 'random':
					$order = 'RAND()';
					$desc=true;
				break;
				case 'older':
					$order = 'id';
					$desc=false;
				break;
				case 'recent':
					$order = 'id';
					$desc=true;
				break;
				case 'popularity':
					$order = 'popularity';
					$desc=true;
				break;
				default:
				case 'rating':
					$order = 'rating';
					$desc=true;
				break;
			}
		}
		$contest->load($_REQUEST['s']*$m,$m,true,$order,$desc);
		$h=600;
		if(!IS_AJAX){
			$r .= dv('d900 center');
			$r .= dv('d300 left');
			//$r .= dv('','widgets');
			//$r .= dv('','actionbar').lnk(translate('The winners have been announced'),'contest/winners').xdv();
			//$r .= dv('submissionsMenu');
			$widget = '<h3>'.translate('Order by').'</h3>';
			$widget .= '<p>'.lnk('rating',true,array('orderBy'=>'rating')).'</p>';
			$widget .= '<p>'.lnk('recent',true,array('orderBy'=>'recent')).'</p>';
			$widget .= '<p>'.lnk('older',true,array('orderBy'=>'older')).'</p>';
			$widget .= '<p>'.lnk('popularity',true,array('orderBy'=>'popularity')).'</p>';
			$widget .= '<p>'.lnk('random',true,array('orderBy'=>'random')).'</p>';
			$r .= Display::widgets(array($widget));
			//$r .= xdv();
		//	$r .= xdv();
			$r .= xdv();
		}
		$r .= dv('d600 right','entries_'.$_REQUEST['s']);
		foreach($contest->results as $submission=>$s){
			if(count($s->children()) > 0){
				$c = '';
				$t = count($s->children());
				if($t>3){$t=3;}
				$w = round(1200/$t);
				$i=0;
				foreach($s->children() as $p){
					if($p->className == 'Photo'){
			//			$w,$h; dv('d'.$t.' col')..xdv()
						$c .= lnk($p->img('medium'),'photo/'.$p->id.'/lightbox',array(),array('class'=>'lightbox'));
						$i++;
					}
				}
				if($i>0){
					$r .= dv('entry');
					$r .= '<h3>'.$s->heart().lnk($s->title.' #'.($submission+($_REQUEST['s']*$m)+1),'stack/'.$s->id).' '.translate('by').' '.User::$users[$s->uid]->fullName('link').' <span class="right lightgrey">('.$s->popularity.'&hearts;)</span>';
					if(in_array(6,App::$user->data['settings']['accesses'])){
					//	$r .= $s->rate();
					}
					$r .= '</h3>';
					$r .= dv('images').$c.xdv().'<br class="clearfloat"/>';
					$r .= xdv().'<br class="clearfloat"/>';
				}
			}
		}
		$r .= xdv();
		if(!IS_AJAX){
			$r .= xdv();
			
			T::$jsfoot[] = '
			/*
var w = $(window);
var entryHeight = 590;
$(".entry").each(function(){
//	var e = $(this);
	e.addClass("e_"+entryId);
	//if(entryId!=0){e.css({"width":1000,"margin-left":100});}
	//else{e.addClass("active");}
	entryId++;
});
var cur = 0;
w.scroll(function(d,h){
	var c = Math.round(($(window).scrollTop())/entryHeight);
	if(c!=cur){
	//	$(".active").stop(true,true).removeClass("active").animate({"width":1000,"height":entryHeight,"margin-left":100});;
	//	$(".e_"+c).stop(true,true).animate({"width":1200,"height":720,"margin-left":0}).addClass("active");
		cur = c;
	}
});
*/
var w = $(window);
var entryHeight = 710;
var entryId = 0;
$(".entry").each(function(){
	var e = $(this);
	entryId++;
});
var cur = 0;
w.scroll(function(d,h){
	var c = Math.round(($(window).scrollTop())/entryHeight);
	if(c!=cur){
		cur = c;
	}
});
var p = false;
s='.$_REQUEST['s'].';
function infinity(){
	if(p===false){
		if($(window).scrollTop() >= ($(document).height() - $(window).height())-800){
			p=true;
			s++;
			activateLoadingState();
			$.ajax({
				url: "'.HOME.'contest/submissions?orderBy='.$_REQUEST['orderBy'].'",
				data:{"ajax":1,"datatype":"json","gethtml":true,"s":s},
				dataType: "json",
				success: function(data){
					deactivateLoadingState();
					if(data.error != null){alert(data.error);}
					else if(data[0] != null && data[0].error != null){alert(data[0].error);}
					if(data.msg != null){notify(data.msg);}
					if(data.script != null){jQuery.globalEval(data.script);}
					$("#mainColumn").append(data.html);
					activate($("#entries_"+s));
					$("#entries_"+s).find(".entry").each(function(){
						var e = $(this);
						e.addClass("e_"+entryId);
						entryId++;
					});
					p=false;
					return true;
				}
			});
		}
	}
}
setInterval("infinity()",500);
';
		}
		return $r;
	}
	
	public function directory(){
		$c = new Contest();
		return $c->general();
	}
	
	public function general(){
		$r = '';
		T::$page['title'] = translate('Photo contest');
		$r .= dv('d750 center lightgrey');
		$r .= dv('notavailable').'The contest has now ended.'.xdv();
		$r .= '<h1 class="blue centerText">'.translate('Pixyt photo contest').'</h1>';
		//$r .= translate('Welcome to our launch contest page! Here you can enter your submissions by simply dragging the file to the <i>get started</i> box below. Simple! You can submit up to three photos on the theme Point of View and you will be in with the chance of winning some amazing prizes and even getting feedback on your photography from some pros in the industry!').'<br/>'.translate('Expose your work, win great stuff and most importantly, have some fun! You have got until the 15th February 2013 to enter.').'<br/>'.translate('Good Luck!').'<br/><br/>'.'<p class="centerText padder">'.lnk(translate('get started!'),'contest/upload',array(),array('data-type'=>'popup','class'=>'btn getStarted')).'</p>'.xdv();
		$r .= '<p>'.translate('We would like to thank all our participants for all these amazing images. The winners will be announced by March 1st.').'</p>';
		$r .= '<p class="padder">'.lnk(translate('See the winners'),'contest/winners',array(),array('class'=>'btn')).'</p>';
		T::$jsfoot[] = '$(".contestGeneral").css({"opacity":0}).delay(200).animate({"opacity":1},1000);';
		$r .= dv('contestGeneral center');
		
		switch($_SESSION['lang']){
			case 'fr_FR':
				$l='fr';
			break;
			default:
				$l='en';
			break;
		}
		$r .= dv('general').'<img src="/img/contest/prizes-table-'.$l.'.png" alt="Contest prizes" title="Contest prizes"/>';
		$r .= '<br/><br/>';
		$r .= $this->jury();
		$r .= '<br/><br/>';
		$r .= $this->sponsors();
		$r .= '<br/><br/>'.dv('padder').'<p class="grey">Featured on: </p> <a href="http://www.photographycompetitions.net/2013/01/pixyt-launch-photo-contest/" rel="external nofollow" target="_blank"><img src="http://www.photographycompetitions.net/img/linkbacklogo.gif" border="0" alt="Photography Competitions Network"/></a>'.xdv();
		$r .= xdv();
		//$r .= '<br/><p class="centerText padder">'.lnk(translate('See all entries'),'contest/submissions',array(),array('class'=>'btn grey')).lnk(translate('get started!'),'contest/upload',array(),array('data-type'=>'popup','class'=>'btn getStarted')).'</p>';
		$r .= $this->rules();
		$r .= xdv();
		return $r;
	}
	
	public function prizes(){
		$r = '';
		$r .= dv('prizes');
		$r .= '<h2>'.translate('Prizes').'</h2>';
		$r .= dv('grey big padded').translate('Although you only submit your photos once, you can win 3 prizes!').xdv();
		foreach(self::$prizes as $prize){
			$r .= dv('prize col d3').dv('padded');
			$r .= dv('name').'<h2>'.translate($prize['name']).'</h2>'.xdv();
			$r .= dv('description').translate($prize['description']).xdv();
			$r .= dv('image').'<img src="/img/contest/prizes/'.$prize['img'].'" width="100px" alt="'.$prize['name'].'" title="'.$prize['name'].'" class="nocopy lazy"/>'.xdv();
			$r .= xdv().xdv();
		}
		$r .= '<br class="clearfloat"/>'.xdv();
		return $r;
	}
	
	public function jury(){
		$r = '';
		$r .= dv('jury padder');
		$r .= '<h2>'.translate('The jury').'</h2>';
		$r .= dv('grey big padded').translate('The jury is composed of professional photographers and teachers throughout Europe.').xdv();
		foreach(self::$jury as $member){
			$r .= dv('member col d3');
			$r .= dv('name').'<h2>'.$member['name'].'</h2>'.xdv();
			$r .= dv('function').'<h3>'.translate($member['function']).'</h3>'.xdv();
			$r .= dv('location').'<h4>'.$member['location'].'</h4>'.xdv();
			if(empty($member['img'])){
				$member['img'] = 'profile.png';
			}
			if(!empty($member['site'])){$r .= dv('site').xtlnk($member['site'],'<img src="/img/contest/jury/'.$member['img'].'" width="100px" alt="'.$member['name'].'" title="'.$member['name'].'" class="nocopy"/>').xdv();}
			else{$r .= dv('image padded').'<img src="/img/contest/jury/'.$member['img'].'" width="100px" alt="'.$member['name'].'" title="'.$member['name'].'" class="nocopy"/>'.xdv();}
			$r .= xdv();
		}
		$r .= '<br class="clearfloat"/>'.xdv();
		return $r;
	}
	
	public function sponsors(){
		$r = '';
		$r .= dv('sponsors padder');
		$r .= '<h2>'.translate('The sponsors').'</h2>';
		$r .= dv('grey big padded').translate('This event is made possible thanks to the following compagnies which have granted great prizes to the winners.').xdv();
		foreach(self::$sponsors as $sponsor){
			$r .= dv('clearfloat').dv('d2 col').'<br/><br/><h2>'.$sponsor['name'].'</h2>';
			$r .= '<h3>'.translate($sponsor['function']).'</h3>'.xdv();
			$r.=  dv('d2 col rightText').'<br/>'.xtlnk($sponsor['site'],'<img src="http://pixyt.com/img/contest/sponsors/'.$sponsor['img'].'" width="100px" alt="'.$sponsor['name'].'" title="'.$sponsor['name'].'" class="nocopy"/>').xdv().'<br class="clearfloat"/>'.xdv();
		}
		$r .= '<br class="clearfloat"/>'.xdv();
		return $r;
	}
	
	public function upload(){
		$r = '';
		
		if(App::$user->id==0){
			$r .= dv('big grey padded centerText').translate('Please').' '.lnk(translate('Sign in'),'login',array('from'=>'pixyt'),array('class'=>'btn','data-type'=>'popup')).' '.translate('or').' '.lnk(translate('Sign up'),'signup',array('from'=>'pixyt'),array('class'=>'btn','data-type'=>'popup')).xdv();
		}
		else{
			$r .= dv('contestScreen');	
			$contest = new Project(27);
			$col = new Collection('Stack');
			$col->prid('=',27,true);
			$col->uid('=',App::$user->id);
			$col->load(0,1);
			$previews = '';
			if($col->total(true) == 0){
				$stack = new Stack();
				$stack->prid = 27;
				$stack->access = 3;
				$stack->title = 'contest submission';
				$stack->data['tags'][] = 'contest';
				$stack->data['tags'][] = '2012';
				if(!$stack->insert()){	
					$this->error('Failed to create stack!');
					Msg::notify('An error occured. Please try again later.');
					return;
				}
			}
			else{
				$stack = $col->results[0];
				$previews='';
				foreach($stack->children() as $p){
					$previews .= $p->display('editPreview');
				}
			}
			if(empty($previews)){
				$r .= '<h1>'.translate('Participate in the contest').'</h1>';
				$r .= '<h2>'.translate('Add 1 to 3 photos').'</h2>';
				$r .= Photo::uploadBtn(4,$stack->id,0,'Stack_'.$stack->id.'_content').dv('addPhotos prepend','Stack_'.$stack->id.'_content').$previews.xdv().'<br class="clearfloat"/>';
				$r .= '<h3>'.translate('The theme: Point of View').'</h3>'.dv('info').'<p>'.translate('You can add any type of photos, good luck!').'</p>'.xdv();
			}
			else{
				$r .= '<h1>'.translate('Yes! you are in!').'</h1>'.dv('addPhotos prepend','Stack_'.$stack->id.'_content').$previews.xdv().'<br class="clearfloat"/>';
				if($col->total(true) > 0 && $col->total(true) < 3){
					$r .= '<h2>'.translate('Add some more...').'</h2>';
					$r .= Photo::uploadBtn(4,$stack->id,0,'Stack_'.$stack->id.'_content');
				}
				$r .= '<h3>'.translate('Ask your friends to like your photos and increase your chances to win').'</h3>';
				$r .= dv('info').$stack->display('share').xdv();
				$r .= xdv();
			}
		}
		return $r;
	}
	
	public function monitor(){
		$r = '';
		
		return $r;
	}
}
?>