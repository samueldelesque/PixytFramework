app.events = _.extend({}, Backbone.Events);
app.addInitializer(function(options){
	app.user = new app.models.user();
	if(!app.ME){console.error("app.ME not defined!");}
	else{
		app.user.set(app.ME);
	}

	if(!app.PROTOCOL){console.error("PROTOCOL NOT DEFINED!");}
	$.cookie("screen",window.screen.width+"x"+window.screen.height);
	var d=new Date();
	$.cookie("gmtOffset",parseInt(-d.getTimezoneOffset()/60),90);
	var f = $("#footer");
	var b = $("#page");
	var w = $(window);
	function setSize(){
		$.cookie("ww",w.width());
		$.cookie("wh",w.height());
	}
	setSize();
	w.resize(function(){setSize();});
	var ex = new Date();
	ex.setTime(ex.getTime()+(1800000));
	if($.cookie("visit") === null){
		$.cookie("visitstart", app.STAT_SESSION_START, {expires:ex,path:"/"});
		$.cookie("visit",app.STAT_SESSION, {expires:ex, path:"/"});
		$.cookie("from",app.FROM,{expires:ex, path:"/"});
	}
	else{
		$.cookie("visitstart", $.cookie("visitstart"),{expires:ex, path:"/"});
		$.cookie("visit", app.STAT_SESSION, {expires:ex, path:"/"});
		$.cookie("from",app.FROM,{expires:ex, path:"/"});
	}

	app.render = function(el){
		$("a",el).click(function(e){
			var url = $(this).attr("href");
			if($(this).hasClass("noroute") || url.match(/http/g)  || url.match(/mailto/g)){return;}
			e.preventDefault();
			app.router.navigate($(this).attr("href"), {trigger: true});
		});
	}
	
	//Launch Google Analytics
	if(app.GAQ){
		(function(i,s,o,g,r,a,m){
			i['GoogleAnalyticsObject']=r;
			i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();
			a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];
			a.async=1;
			a.src=g;
			m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', app.GA, 'pixyt.com');
		ga('set', 'campaignSource', app.FROM);
		ga('set', 'campaignName', app.CAMPAIGN);
		ga('set', 'campaignMedium', app.MEDIUM);
		ga('set', 'campaignKeyword', app.KEYWORD);
		//ga('send', 'pageview'); // MOVED TO ROUTER


		// var _gaq = _gaq || [];
		// _gaq.push(["_setAccount","UA-31248995-1"],["_setCustomVar",1, "Age", app.AGE,2],["_setDomainName", "pixyt.com"],["_setAllowLinker",true],["_trackPageview"],app.AUX,app.AUXB);
		// (function(){var ga = document.createElement("script");
		// 	ga.type = "text/javascript";
		// 	ga.async = true;
		// 	ga.src = app.PROTOCOL+"google-analytics.com/ga.js";
		// 	var s = document.getElementsByTagName("script")[0];
		// 	s.parentNode.insertBefore(ga, s);
		// })();
	}
	
	//Launch UserVoice
	if(app.USERVOICE)(function(){var uv=document.createElement("script");uv.type="text/javascript";uv.async=true;uv.src="//widget.uservoice.com/oeAJrC75DdDNyXNa8Qh4Q.js";var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(uv,s)})();
	UserVoice = window.UserVoice || [];
	UserVoice.push(["showTab", "classic_widget", {
	  mode: "full",
	  primary_color: "#cc6d00",
	  link_color: "#007dbf",
	  default_mode: "support",
	  forum_id: 218820,
	  tab_label: "Help!",
	  tab_color: "#b8b8b8",
	  tab_position: "middle-right",
	  tab_inverted: false
	}]);
	
	
	//Perfect Audience
	if(app.PERFECTAUDIENCE){
		window._pa = window._pa || {};
		// _pa.orderId = "myCustomer@email.com"; // OPTIONAL: attach user email or order ID to conversions
		// _pa.revenue = "19.99"; // OPTIONAL: attach dynamic purchase values to conversions
		var pa = document.createElement('script'); pa.type = 'text/javascript'; pa.async = true;
		pa.src = ('https:' == document.location.protocol ? 'https:' : 'http:') + "//tag.perfectaudience.com/serve/51f7cf6941bc843f0b0000ea.js";
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(pa, s);
	}
	
	//Launch Facebook
	if(app.FB){
		console.log('Facebook loading.');
		(function(d, s, id){var js, fjs = d.getElementsByTagName(s)[0];if (d.getElementById(id)) return;js = d.createElement(s); js.id = id;js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId="+app.FB;fjs.parentNode.insertBefore(js, fjs);}(document, "script","facebook-jssdk"));
	}

	//Launch Twitter
	if(app.TWITTER){
		!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
	}

	//render the page
	app.render($("body"));
});

$(document).ready(function(){
	app.start();
});