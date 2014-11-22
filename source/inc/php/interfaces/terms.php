<?php
class Terms extends Interfaces{
	public function directory(){
		return $this->service();
	}
	
	public function service(){
		T::$page['title'] = t('Terms of service');
		$r = '';
		$this->header('<h1 style="margin-left:40px;">'.t('Terms of service').'</h1>');
		$r .= '<ol class="terms">';
		$r .= '<li><h3>'.t('Creating an account').'</h3>';
		$r .= '<p>'.t('Anyone over 18 is eligible for creating an account. if you are over 13, you may join as well under responsability of your parents or legal guardian, please have them review the terms of services.').'</p>';
		$r .= '<p>'.t('To create an account you will need to provide a valid email, your name and your age.').'</p></li>';
		
		$r .= '<li><h3>'.t('Cookies, IP, etc').'</h2>';
		$r .= '<p>'.t('Cookies and other technologies are used to recognize you.').'</p>';
		$r .= '<p>'.t('IP, browser and system environments, connection time and type might be recorded for developing purposes and to maximize your experience.').'</p></li>';
		
		$r .= '<li><h3>'.t('Submissions').'</h3>';
		$r .= '<p>'.t('Any submissions you make to the website must not:').'</p><ol class="numbered">';
		$r .= '<li>'.t('Be unlawful, threatening, harmful, abusive, harassing, defamatory, obscene, hateful, racially or ethnically objectionable,').'</li>';
		$r .= '<li>'.t('Unlawfully exploit minors in any way,').'</li>';
		$r .= '<li>'.t('Depict animal cruelty,').'</li>';
		$r .= '<li>'.t('Infringes any patent, trademark, trade secret, copyright or other proprietary rights of any third-party,').'</li>';
		$r .= '<li>'.t('Unsolicited advertising, promotional materials, spam or any other form of solicitation, except in those areas that are designated for such purpose,').'</li>';
		$r .= '<li>'.t('Contain viruses or any other computer code, files or programs designed to interrupt, destroy or limit the functionality of any computer software or hardware or telecommunications equipment.').'</li>';
		$r .= '</ol></li>';
		
		$r .= '<li><h3>'.t('Warranties').'</h3>';
		$r .= '<p>'.t('Any service we provide is provided as is.').'</p>';
		$r .= '<p>'.t('Although we do our best to keep the services running unninterrupted and error free, we do not warrant any of this.').'</p>';
		$r .= '<p>'.t('Any use you make of our website is under your own responsability.').'</p>';
		$r .= '<p>'.t('Any harm caused to your equipment or to you following a submission to your account or the use of our services is at your own risks.').'</p></li>';
		
		$r .= '<li><h3>'.t('Changes to our terms of use').'</h3>';
		$r .= '<p>'.t('We may occasionally update our terms of use to reflect changes in our practices and services. If we make material changes in the way we collect, use, or share your personal information, we will notify you by sending you an email.').'</p></li>';
		$r .= '</ol>';
		$this->sidebar('<h3>Light Architect SAS</h3><p>'.xtlnk('http://lightarchitect.com','lightarchitect.com').'</p>');
		return $r;
	}
	
	public function sales(){
		T::$page['title'] = t('Terms of Sales');
		$r = dv('termsofsales');
		$r .= dv('private sales').'<h1>I. '.t('Private photos').'</h1>';
		$r .= dv('contentBox').'<h2>1. '.t('Definition').'</h2>';
		$r .= '<p>'.t('Private photos are images that depict families or portraits and have been taken during a photo shooting organized by the photographer or his clients.').'</p>';
		$r .= '<p>'.t('These photos will commonly have been taken in the photographers own studio or in a public place such as on the beach, in a park or other such. They are showcased on our website only for the use of the represented people, their relatives or friends that they would decide to invite.').'</p>';
		
		$r .= '<br/>';
		
		$r .= '<h2>2. '.t('Copyright notice').'</h2>';
		$r .= '<p>'.t('When buying a print of a private photo from our store, you only buy the medium which contains the photo and not ownership over its copyright except otherwise specified. This means you cannot reproduce the photograph or sell it to a third party. Neither can you exhibit it in public places without the authors written permission.').'</p>';
		
		$r .= '<br/>';
		
		$r .= '<h2>3. '.t('Prices').'</h2>';
		$r .= '<p>'.t('The prices are defined by the global costs and the added value of the photographer. The prices are in Euros inclusive of VAT. All the  prices mentioned on the checkout page are valid for the time of order.').'</p>';
		
		$r .= '<br/>';
		
		$r .= '<h2>4. '.t('Prints').'</h2>';
		$r .= '<p>'.t('The prints you order on our website are produced by professionnal labs. The photos are retouched by the photographer and printed on high quality photo paper.').'</p>';
		
		$r .= '<br/>';
		
		$r .= '<h2>5. '.t('Order').'</h2>';
		$r .= '<p>'.t('When ordering private photos on our website you must supply true, exact, updated and complete information about yourself as requested in the registration form, notably concerning the identity of the order recipient, the delivery address, the email address where an order confirmation can be sent to. Once you finish the order process the production chain is launched and you cannot retract and abandon it.').'</p>';
		
		$r .= '<br/>';
		
		$r .= '<h2>6. '.t('Payment').'</h2>';
		$r .= '<p>'.t('The payment is done online by Paypal, which accepts most credit cards as well as user balance for users who already have an account. Paypal is an organization indenpendent of Light Architect.').'</p>';
		
		$r .= '<br/>';
		
		$r .= '<h2>7. '.t('Delay').'</h2>';
		$r .= '<p>'.t('After a photo session the photographer will usually post the photos within 24 hours. After you order your prints, the photographer will usually require another 48 to 72 hours to edit the photos and send them to us. We will send forward the photos to the lab which will also require about 48 hours to produce the requested print. Finally you will have to expect up to 7 days for delivery. If you haven\'t received your prints after 3 weeks, please address us a message specifying the order number which will be issued after you finish the checkout process.').'</p>';
		$r .= xdv();
		$r .= xdv();
		
		$r .= xdv();
		return $r;
	}
}
?>