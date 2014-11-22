<?php
class Rss extends Interfaces{
	public function user($uid){
		$author = new User($uid);
		
		$dom = new DOMDocument('1.0'); 
		$rss = $dom->createElement('rss'); 
		$dom->appendChild($rss); 
		$version = $dom->createAttribute('version'); 
		$rss->appendChild($version);
		$versionValue = $dom->createTextNode('2.0'); 
		$version->appendChild($versionValue);
		$channel = $dom->createElement('channel'); 
		$rss->appendChild($channel);
		
		$Stitle =  $dom->createElement('title');
		$Slink = $dom->createElement('link');
		$Sdesc = $dom->createElement('description');
		$Sauthor = $dom->createElement('author');
		$Sdate = $dom->createElement('pubDate');
		$Stitle->appendChild($dom->createTextNode($author->fullName('full').'s photos'));
		$Slink->appendChild($dom->createTextNode($author->fullName('link')));
		$Sdesc->appendChild($dom->createTextNode($author->data['profile']['about']));
		$Sauthor->appendChild($dom->createTextNode($author->fullName('full')));
		$Sdate->appendChild($dom->createTextNode(date('r',$author->modified)));
		
		$photos = new Collection('Photo');
		$photos->uid = $author->id;
		$photos->channel('>=',3,true);
		$photos->load(0,20);
		
		foreach($photos->results as $photo){
			$item = $dom->createElement("item"); 
			$title = $dom->createElement("title"); 
			$link = $dom->createElement("link"); 
			$desc = $dom->createElement("description"); 
			$author = $dom->createElement("author"); 
			$pubdate = $dom->createElement("pubDate"); 
			
			//create the text nodes for item 
			$titleText = $dom->createTextNode($photo->title); 
			$descText = $dom->createTextNode($photo->img('medium'));
			$linkText = $dom->createTextNode(HOME.'photo/'.$photo->id);
			$pubDateText = $dom->createTextNode(date('r',$photo->created));
			
			//add text to the elements 
			$title->appendChild($titleText);
			$desc->appendChild($descText);
			$link->appendChild($linkText);
			$pubdate->appendChild($pubDateText); 
			
			//add elements to the item element 
			$item->appendChild($title);
			$item->appendChild($desc);
			$item->appendChild($link);
			$item->appendChild($pubdate);
			
			//append the <item> element to the <channel> element 
			$channel->appendChild($item);
		}
		header('Content-Type: text/xml');
		$dom->documentElement->appendChild($channel); 		
		//save and display tree 
		echo $dom->saveXML();
		die();
	}
	
	public function sitemap(){
		$dom = new DOMDocument('1.0');
		$sitemap = $dom->createElement('urlset');
		$xmlns = $dom->createAttribute('xmlns'); 
		$sitemap->appendChild($xmlns);
		$xmlnsVal = $dom->createTextNode('http://www.sitemaps.org/schemas/sitemap/0.9'); 
		$xmlns->appendChild($xmlnsVal);
		
		if(!in_array(HOST,Site::$pixytSites)){
			foreach(Site::$current->content as $p){
				$item = $dom->createElement("url");
				$url = $dom->createElement("loc");
				$changefreq = $dom->createElement("changefreq");
				$priority = $dom->createElement("priority");
				$urlText = $dom->createTextNode('http://'.Site::$current->url.'/'.$p['u']);
				$changefreqText = $dom->createTextNode('daily');
				$priorityText = $dom->createTextNode(0.8);
				$url->appendChild($urlText);
				$changefreq->appendChild($changefreqText);
				$priority->appendChild($priorityText);
				$item->appendChild($url);
				$item->appendChild($changefreq);
				$item->appendChild($priority);
				$sitemap->appendChild($item);
			}
		}
		else{
			//Top level sections
			//Manualy add all important pages (directories and such)
			$pages = array(
				0=>array(
					'url'=>'',
					'changefreq'=>'daily',
					'priority'=>1
				),
				1=>array(
					'url'=>'login',
					'changefreq'=>'never',
					'priority'=>0.67
				),
				2=>array(
					'url'=>'creatives/photographer',
					'changefreq'=>'monthly',
					'priority'=>0.90
				),
				3=>array(
					'url'=>'creatives',
					'changefreq'=>'monthly',
					'priority'=>0.64
				),
				4=>array(
					'url'=>'termsofuse',
					'changefreq'=>'never',
					'priority'=>0.61
				),
				5=>array(
					'url'=>'privacy',
					'changefreq'=>'never',
					'priority'=>0.61
				),
				6=>array(
					'url'=>'contactus',
					'changefreq'=>'never',
					'priority'=>0.70
				),
				7=>array(
					'url'=>'contest',
					'changefreq'=>'weekly',
					'priority'=>0.8
				),
				8=>array(
					'url'=>'contest/submissions',
					'changefreq'=>'daily',
					'priority'=>0.72
				),
			);
			
			foreach($pages as $page){
				$item = $dom->createElement("url");
				$url = $dom->createElement("loc");
				$changefreq = $dom->createElement("changefreq");
				$priority = $dom->createElement("priority");
				$urlText = $dom->createTextNode('http://pixyt.com/'.$page['url']);
				$changefreqText = $dom->createTextNode($page['changefreq']);
				$priorityText = $dom->createTextNode($page['priority']);
				$url->appendChild($urlText);
				$changefreq->appendChild($changefreqText);
				$priority->appendChild($priorityText);
				$item->appendChild($url);
				$item->appendChild($changefreq);
				$item->appendChild($priority);
				$sitemap->appendChild($item);
			}
			
			//Section Users
			$users = new Collection('User');
			$users->load(0,1000,true,'modified');
			foreach($users->results as $user){
				$item = $dom->createElement("url");
				$url = $dom->createElement("loc");
				$changefreq = $dom->createElement("changefreq");
				$priority = $dom->createElement("priority");
				$lastmod = $dom->createElement("lastmod");
				$urlText = $dom->createTextNode('http://pixyt.com/user/'.$user->id);
				$lastmodText = $dom->createTextNode(date('Y-m-d',$user->modified));
				$changefreqText = $dom->createTextNode('daily');
				$priorityText = $dom->createTextNode(min(0.60,number_format(0.5+($user->validated/1000),2)));
				$url->appendChild($urlText);
				$lastmod->appendChild($lastmodText);
				$changefreq->appendChild($changefreqText);
				$priority->appendChild($priorityText);
				$item->appendChild($url);
				$item->appendChild($lastmod);
				$item->appendChild($changefreq);
				$item->appendChild($priority);
				$sitemap->appendChild($item);
			}
			
			//Section Stacks
			$stacks = new Collection('Stack');
			$stacks->access('>=',3,true);
			$stacks->load(0,1000,true,'modified');
			foreach($stacks->results as $stack){
				$item = $dom->createElement("url");
				$url = $dom->createElement("loc");
				$changefreq = $dom->createElement("changefreq");
				$priority = $dom->createElement("priority");
				$lastmod = $dom->createElement("lastmod");
				$urlText = $dom->createTextNode('http://pixyt.com/stack/'.$stack->id);
				$lastmodText = $dom->createTextNode(date('Y-m-d',$stack->modified));
				$changefreqText = $dom->createTextNode('daily');
				$priorityText = $dom->createTextNode(0.40);
				$url->appendChild($urlText);
				$lastmod->appendChild($lastmodText);
				$changefreq->appendChild($changefreqText);
				$priority->appendChild($priorityText);
				$item->appendChild($url);
				$item->appendChild($lastmod);
				$item->appendChild($changefreq);
				$item->appendChild($priority);
				$sitemap->appendChild($item);
			}
			
			//Section Photos
			$photos = new Collection('Photo');
			$photos->access('>=',3,true);
			$photos->load(0,1000,true,'modified');
			foreach($photos->results as $photo){
				$item = $dom->createElement("url");
				$url = $dom->createElement("loc");
				$changefreq = $dom->createElement("changefreq");
				$priority = $dom->createElement("priority");
				$lastmod = $dom->createElement("lastmod");
				$urlText = $dom->createTextNode('http://pixyt.com/photo/'.$photo->id);
				$lastmodText = $dom->createTextNode(date('Y-m-d',$photo->modified));
				$changefreqText = $dom->createTextNode('monthly');
				$priorityText = $dom->createTextNode(0.30);
				$url->appendChild($urlText);
				$lastmod->appendChild($lastmodText);
				$changefreq->appendChild($changefreqText);
				$priority->appendChild($priorityText);
				$item->appendChild($url);
				$item->appendChild($lastmod);
				$item->appendChild($changefreq);
				$item->appendChild($priority);
				$sitemap->appendChild($item);
			}
		}
		header('Content-Type: text/xml');
		$dom->appendChild($sitemap);	
		//save and display tree 
		echo $dom->saveXML();
		die();
	}
}
?>