var isMobile = navigator.userAgent.match(/(iPhone|iPod|iPad|Android|BlackBerry)/);

$.fn.nextOrFirst = function(selector){
  var next = this.next(selector);
  return (next.length) ? next : this.prevAll(selector).last();
};
$.fn.prevOrLast = function(selector){
  var prev = this.prev(selector);
  return (prev.length) ? prev : this.nextAll(selector).last();
};

$(document).bind('drop dragover', function (e) {
	e.preventDefault();
});
$(document).bind('dragover', function (e) {
	var dropZone = $('.dropzone'),
	timeout = window.dropZoneTimeout;
	if (!timeout) {
		dropZone.addClass('in');
	}
	else {
		clearTimeout(timeout);
	}
	var found = false,
	node = e.target;
	do {
		if (node === dropZone[0]) {
			found = true;
			break;
		}
		node = node.parentNode;
	}
	while (node !== null);
		if (found) {
			dropZone.addClass('hover');
		}
		else {
			dropZone.removeClass('hover');
		}
		window.dropZoneTimeout = setTimeout(function () {
		window.dropZoneTimeout = null;
		dropZone.removeClass('in hover');
	}, 100);
});
var activate = function(el){
	console.log("Activating #"+el.attr("id"));
	if(el === null){el = $("body");}
	el.addClass("activated");
	
	//$(".feed",el).masonry({itemSelector:".item",columnWidth:250});
	/*
	$("input.files",el).each(function(){
		$(this).addClass("accepting-drop");
		var remainingQueue = 0;
		var filesleft_singular = "file left";
		var filesleft_plural = "files left";
		
		//Do some "a" stuff as well
		if(!$(this).attr("data-channel") || $(this).attr("data-channel")==null){
			notify("channel not defined in Files form!");
			return false;
		}
		var container = $(this).parent();
		var channel = $(this).attr("data-channel");
		
		var a = $(this).attr("data-a");
		
		var params = "channel="+channel+"&";
		if($(this).attr("data-fileid")!=null &&$(this).attr("data-fileid") !=0){
			params+="fileid="+$(this).attr("data-fileid")+"&";
		}
		
		$(this).fileupload({
			drop: function (e, data) {
				$.each(data.files, function (index, file) {
					remainingQueue = remainingQueue +1;
					if(remainingQueue == 1){var txt = remainingQueue+" "+filesleft_singular;}
					else{var txt = remainingQueue+" "+filesleft_plural;}
					activateLoadingState(txt);
				})
			},
			change: function (e, data) {
				$.each(data.files, function (index, file) {
					remainingQueue = remainingQueue +1;
					if(remainingQueue == 1){var txt = remainingQueue+" "+filesleft_singular;}
					else{var txt = remainingQueue+" "+filesleft_plural;}
					activateLoadingState(txt);
				})
			},
			add: function (e, data) {
				var jqXHR = data.submit().success(function (r) {
					
				}).complete(function (res){
					var r = jQuery.parseJSON(res.responseText);
					console.log(r);
					if(r.script != undefined){jQuery.globalEval(r.script);}
					if(r.error != undefined){notify(r.error);}
					if(r.msg != undefined){notify(r.msg);}
					if(r.html != undefined){container.append(r.html);}
					activate(container);
				});
			},
			url: app.HOME+"upload.php?"+params,
			complete: function (status, obj) {
				remainingQueue--;
				if(remainingQueue >= 1){
					if(remainingQueue == 1){var txt = remainingQueue+" "+filesleft_singular;}
					else{var txt = remainingQueue+" "+filesleft_plural;}
					activateLoadingState(txt);
				}
				else{
					container.removeClass("empty");
					deactivateLoadingState();
				}
				return true;
			}
		});
	});
	*/
	$(".validationBtn",el).mouseover(function(){
		var btn = $(this);
		if(btn.hasClass("validated")){
			btn.find("img").attr("src","/img/unvalidate-22-red.png");
		}
		else if(btn.hasClass("unvalidated")){
			btn.find("img").attr("src","/img/validate-22-blue.png");
		}
		else{
			btn.find("img").attr("src","/img/validate-22-blue.png");
		}
	}).mouseout(function(){
		var btn = $(this);
		if(btn.hasClass("validated")){
			btn.find("img").attr("src","/img/validate-22-blue.png");
		}
		else if(btn.hasClass("unvalidated")){
			btn.find("img").attr("src","/img/unvalidate-22-red.png");
		}
		else{
			btn.find("img").attr("src","/img/unvalidate-22-blue.png");
		}
	});
	$("[class*=follow]",el).mouseover(function(){
		var btn = $(this);
		if(btn.hasClass("active")){
			btn.html("Unfollow&nbsp;").addClass("btn-danger");
		}
	}).mouseout(function(){
		var btn = $(this);
		if(btn.hasClass("active")){
			btn.html("Following").removeClass("btn-danger");
		}
	});
	$(".viewmode a",el).each(function(){
		$(this).click(function(){$(".viewmode a",el).removeClass("active");$(this).addClass("active");});
	});
	$(".tooltip",el).mouseover(function () {
		e = $(this);
		e.data("title",$this.attr("title"));
		e.attr("title", "");
		e.append("<span class='tooltip'>"+e.data("title")+"</span>");
	}).mouseout(function () {
		e = $(this);
		e.attr("title",e.data("title"));
		e.find(".tooltip").fadeout();
	});
	$(".tab",el).each(function(){
		var tab = $(this);
		var handle = tab.find(">.denomination");
		var items = tab.find(">.el");
		handle.click(function(){
			if(!handle.hasClass("closed")){handle.addClass("closed");items.hide();}
			else{handle.removeClass("closed");items.show();}
		});
	});
	$(".nocopy",el).bind("contextmenu",function(e){return false;}).bind("mousedown",function(e){return false;});
	$("img.lazy",el).each(function(){
		$(this).removeClass("lazy").lazyload({
		  effect : "fadeIn"
		});
	});
	$(".rating",el).each(function(){
		var r = $(this);
		var c = r.find(".cur");
		c.css({"width":(r.data("cur")*30)});
		r.mousemove(function(e){
			var o = r.offset();
			var w = Math.round((e.pageX-o.left+7.5)/15)*15;
			if(w>150){w=150;}
			c.css({"width":w});
		}).mouseleave(function(){
			c.css({"width":(r.data("cur")*30)});
		}).click(function(e){
			var o = r.offset();
			var w = Math.round((e.pageX-o.left+7.5)/15)*15;
			if(w>150){w=150;}
			var d = new Object();
			d["gethtml"]=false;
			d["ajax"]=true;
			if(r.hasClass("rated")){
				d["update[Feedback]["+r.data("id")+"][content]"]=w/15;
			}
			else{
				d["insert[Feedback][content]"]=w/15;
				d["insert[Feedback][objectType]"]=r.data("objecttype");
				d["insert[Feedback][objectId]"]=r.data("objectid");
				d["insert[Feedback][type]"]=3;
			}
			c.css({"width":w});
			r.addClass("rated");
			r.data("cur",w/30);
			query(HOME+"ajax",d);
		});
	});
	$(".feed .share",el).each(function(){
		var s = $(this);
		s.css({left:"-1000px",opacity:0});
		s.parent().mouseover(function(){
			if(!s.hasClass("active"))
				s.stop(true,true).animate({left:"5px",opacity:1}).addClass("active");
		}).mouseleave(function(){
			if(s.hasClass("active"))
				s.stop(true,true).delay(200).animate({left:"-1000px",opacity:0}).removeClass("active");
		});
	});
	$(".switch",el).each(function(){
		var ms = this;
		var s = $(this);
		s.find("a").click(function(){
			s.find("a").removeClass("active btn-danger btn-success");
			var opt = $(this);
			opt.addClass("active");
			if(s.hasClass("bool")){
				if(opt.data("value")==1){var c = "btn-success";}
				else{var c = "btn-danger";}
				opt.addClass(c);
			}
		});
	});
	/*
	$(".lightbox",el).click(function(e){
		e.preventDefault();
		self = $(this);
		lightbox(this.href);
	});
	*/
	
	$("#lightbox_bgd").click(function(){
		$("body").unbind("keydown").unbind("keydown");
		$("#lightbox,#lightbox_bgd").fadeOut();
	});
	$(".ajax",el).click(function(e){
		e.preventDefault();
		self = $(this);
		if(self.hasClass("freeze")){return false;}
		self.addClass("freeze").animate({"opacity":0.7},500);
		if(self.data("type")=="confirm"){if(!confirm(self.data("matter"))){return;}}
		var a;
		if(self.data("url") != null){a=self.data("url");}
		else{a=this.href;}
		var d = new Object();
		var c = $("#"+self.attr("data-containerid"));
		if(c == null){c = $("#mainColumn");}
		if(self.attr("data-gethtml")=="1"||self.attr("data-gethtml")=="true"){d.gethtml = true;}
		else{d.gethtml=false;}
		var s = parseInt(c.data("s"));
		if(s==null){s=0;}
		d.s = s;
		if($(this).hasClass("prev")){if(c.data("s")>1){c.data("s",s-1);}}else{c.data("s",s+1);}
		self.animate({"opacity":1},1000,function(){self.removeClass("freeze")});
		query(a,d,c,true);
		var p = parseUri(this.href).path;
		if(p!="ajax"&&d.gethtml===true){window.history.pushState({},"",p);/*_history.push({"c":c,"s":s,"url":p});*/}
		return false;
	});
	$("[data-type=popup]",el).click(function(e){
		e.preventDefault();
		var s = $(this);
		if(s.attr("href") != null){
			var url = s.attr("href");
		}
		else if(s.attr("data-url") != null){
			var url = s.attr("data-url");
		}
		else{
			var url = app.HOME;
		}
		//if(url.match(/http/g).length == 0){url = app.HOME+url;}
		xbox(url,true,s.hasClass("xl"));
	});
	$(".softlink",el).each(function(i){
		$(this).click(function(e){
			if(isLoading===true){return;}
			var url = $(this).data("url");
			var islink = url.match(/http/g);
			if(!islink || islink.length == 0){url = app.HOME+url;}
			window.location.href = url;
		}).find("a").click(function(e){
			e.stopPropagation();
		});
	});
	$("a.confirm",el).click(
		function(e){
			e.preventDefault();
			var self = $(this);
			if(confirm($(this).attr("data-matter"))){
				window.location.href = $(this).attr("href");
			}
			return false;
		}
	);
	var i = Math.random();
	$("a",el).click(function(e){
		var url = $(this).attr("href");
		if($(this).hasClass("noroute") || url.match(/http/g)){console.log(url+" is noroute");return;}
		e.preventDefault();
		if($(this).hasClass("action")){return;}
		app.router.navigate($(this).attr("href"), {trigger: true});
	});
	$(".slider",el).each(function(){
		var slider = $(this);
		if(slider.hasClass("instantiated")){return;}
		slider.addClass("instantiated");
		var t;
		if(!slider.attr("data-delay")){t=5000;}else{t=slider.attr("data-delay");}
		slider.find(".slide:first").addClass("active");
		if(slider.hasClass("fullscreen")){
			slider.css({position:"absolute",top:0,left:0,bottom:0,right:0,width:"100%",height:"100%"});
		}
		slider.hide();
		slider.fadeIn();
		if(slider.find(".slide").length > 1){
			slider.find(".slide").each(function(){
				$(this).css({position:"absolute"}).hide();
			});
			var play = function (){
				if(slider.find(".active").hasClass("clicked")){return;}	
				var a = slider.find(".active");
				if(slider.hasClass("no-overlap")){
					var i = a.nextOrFirst().css({"opacity":"1","z-index":1}).show().addClass("active").index();
					a.css({"z-index":2}).hide("fade",10,function(){$(this).css("opacity",0)}).removeClass("active");
				}
				else{
					var i = a.nextOrFirst().css({"opacity":"1","z-index":1}).show().addClass("active").index();
					a.css({"z-index":2}).hide("fade",500).removeClass("active");
				}
				slider.find(".controls a").removeClass("active");
				slider.find(".controls a:eq("+i+")").addClass("active");
			}
			slider.find(".active").hide().css("right","0").fadeIn();
			setInterval(play,t);
		}
	});
	$(".suggest",el).keydown(function(){
		var s=$(this);
		var dest = $(this).parent().find(".suggestions");
		query(HOME+"message/suggest",{"gethtml":1},dest);
	}).blur(function(){
		$(this).parent().find(".suggestions").fadeOut();
	}).focus(function(){
		$(this).parent().find(".suggestions").fadeIn();
	});
	/*
	$("#siteEditor",el).find("#pages li").each(function(){
		var content = $("#siteEditor #pagelet");
		var s = $(this);
		s.click(function(){
			$("#pages li").removeClass("active");
			s.addClass("active");
			content.data("cururl",s.data("url"));
			query(HOME+content.data("relativepath")+"editcontent",{"page":s.data("url"),"gethtml":true,"format":"json"},content);
		});
	});
	*/
	$("#pageEditor",el).sortable({
		helper:"clone",
		revert:50,
		dropOnEmpty:false,
		scroll:false,
		opacity:"0.7",
		cursor:"move",
		tolerance:"pointer",
		update:function(){
			content = $(this);
			var list = new Object();
			content.find(".selectable").each(function(i){
				list[i] = $(this).attr("data-classname")+"_"+$(this).attr("data-id");
			});
			var d = new Object();
			d.gethtml = false;
			d.format = "json";
			var n = "update[Site]["+content.data("siteid")+"][page]["+content.data("u")+"][order]";
			d[n] = JSON.stringify(list);
			query(HOME+"ajax",d);
		}
	}).disableSelection();
	$("#siteEditor .tab.all",el).sortable({
		helper:"clone",
		revert:50,
		items:".el,.tab.section",
		dropOnEmpty:false,
		scroll:false,
		opacity:"0.7",
		cursor:"move",
		tolerance:"pointer",
		update:function(e,ui){
			var content = $("#siteEditor");
			if($(this).hasClass("section")){return;}
			var list = new Object();
			$(this).find(">.drag").each(function(u){
				if($(this).hasClass("section")){
					var sectionData = new Object();
					$(this).find(".drag").each(function(i){
						if(!$(this).hasClass("dragslot")){
							if($(this).data("u"))sectionData[i] = {"u":$(this).data("u")};
						}
					});
					if($(this).data("u"))list[u] = {"u":$(this).data("u"),"c":sectionData};
				}
				else{
					if($(this).data("u"))list[u] = {"u":$(this).data("u")};
				}
			});
			var d = new Object();
			d.gethtml = false;
			d.format = "json";
			var n = "update[Site]["+content.data("siteid")+"][order]";
			d[n] = JSON.stringify(list);
			query(HOME+"ajax",d);
		},
		connectWith:"#siteEditor .tab.all .el,#siteEditor .tab.section"
	}).selectable().disableSelection();
	
	$("span[id^=update_]",el).each(function(){
		if($(this).html() == ''){$(this).html('...');}
		$(this).click(function(){
			var self = $(this);
			var div = this;
			if(!self.hasClass("editing")){
				self.addClass("editing");
				var placeholder = self.html();
				var url = $(this).attr("id");
				url = url.split("_");
				var n = url[0];
				for(i=1;i<url.length;i++){
					n += "["+url[i]+"]";
				}
				self.attr("data-value",this.innerHTML);
				self.html("<form action=\"?\"><textarea type=\"text\" name=\""+n+"\" \"></textarea></form>");
				self.find("textarea").val(placeholder);
				self.find("form").submit(function(event) {
					event.preventDefault(); 
					self.find("textarea").blur();
				}).keydown(function(e) {
					if (e.which == 13){
						self.find("textarea").blur();
					}
				});
				self.bind("keydown",function(e) {
					return;
				});
				self.find("textarea").focus().select().blur(function(){
					var v = urlencode(self.find("form textarea").val());
					if(v != placeholder && v != '...'){
						self.find("form textarea").prop('disabled', true);
						self.attr("data-value",v);
						query(HOME+"ajax?"+n+"="+v,{"format":"json","gethtml":0});
						self.removeClass("editing");
						if(self.hasClass("insert")){self.html(placeholder);}
						else{self.html(urldecode(v));}
					}
					else{
						self.html(placeholder)
						self.removeClass("editing");
						return true;
					}
				});
			}
		});
	});
}

var isLoading = false;
function t(str){
	return str;
}
function activateLoadingState(m){
	isLoading=true;
	if($("#uploading").length==0){
		$("body").append("<div id='uploading'></div><span id='loadingStateInfo' style='display:none;'></span>");
	}
	var s = $("#uploading");
	s.css({display:"block",height:"0"}).animate({height:"5px"});
	var i = $("#loadingStateInfo");
	if(m!=null){
		i.html(m).css({display:"block",height:"0"}).animate({height:"35px"});
	}
}
function deactivateLoadingState(){
	isLoading=false;
	var s = $("#uploading");
	s.delay(200).animate({height:"0"},500,function(){$(this).css({display:"none"})});
	var i = $("#loadingStateInfo");
	i.html("");
	i.delay(500).animate({height:"0"},500,function(){$(this).css({display:"none"})});
}
function callAction(m){
	var a = $("#actionbar");
	if(a.is(":visible")){
		a.slideUp(200,a.html(m).slideDown());
	}
	else{
		a.html(m).slideDown();
	}
}
function lightbox(c){
	if($("#lightbox").length==0){
		$("body").append("<div id='lightbox_bgd'></div><div id='lightbox'></div>");
		$("#lightbox_bgd").click(function(){$("#lightbox_bgd").fadeOut(function(){$("#lightbox_bgd").remove();});$("#lightbox").fadeOut(function(){$("#lightbox").remove();});})
	}
	var l = $("#lightbox");
	if(l.is(":hidden")){l.fadeIn();}
	if($("#lightbox_bgd").is(":hidden")){$("#lightbox_bgd").fadeIn();}
	query(c,{"gethtml":1},l,true,function(){
		var l = $("#lightbox");
		im = l.find("img");
		im.css({opacity:0});
		$("body").unbind("keydown").bind("keydown",function(e) {
			if(e.keyCode == 37){
				im.fadeOut(50,function(){lightbox(HOME+"photo/"+im.data("prev")+"/lightbox")});
			}
			else if(e.keyCode == 39){
				im.fadeOut(50,function(){lightbox(HOME+"photo/"+im.data("next")+"/lightbox")});
			}
			else if(e.keyCode == 27){
				l.fadeOut(50);
				im.fadeOut(50);
				$("#lightbox_bgd").fadeOut();
			}
		});
		im.load(function(){
		var w,h,l;
		var wh = $(window).height()-45;
		if(im.height() < $(window).height()){h=im.height();w=im.width();l=($(window).width() - im.width())/2;}
		else{
			h=wh;w=h * im.width() / im.height();
			l = ($(window).width() - w) / 2;
		}
		im.css({width:w,height:h,"left":l,position:"absolute"}).animate({opacity:1});
		});
	});
}
var n_c = 0;
function notify(m,d){
	if(!d){var d = 5000;}
	else{d=parseInt(d)*1000;}
	n_c++;
	if($("#notification").length==0){
		$("#primary").prepend("<div id='notification'></div>");
	}
	var n = $("#notification");
	if(n.is(":visible")){
		n.append("<div>"+m+"</div>");
	}
	else{
		n.html(m).slideToggle();
	}
	n.click(function(){hideNotifications(n_c);})
	if(d!=0) setTimeout("hideNotifications("+n_c+");",d);
}
function hideNotifications(c){
	if(c<n_c){return;}
	else{
		var n = $("#notification");
		n.slideUp(200,function(){n.html("")})
	}
}
function xbox(content,isUrl,xl){
	if(!isUrl){isUrl=false;}
	var id = "xBox_"+(new Date).getTime();
	$("body").append("<div id=\""+id+"\" class=\"xbox\" style=\"display:none;\"><a class=\"close\" href=\"#\"><img src=\"/img/delete-grey.png\" height=\"25px\"/></a><div class=\"content\"></div></div>");
	var box = $("#"+id);
	if(xl){box.addClass("xl");}
	box.css("top","-100%");
	$("body").unbind("keydown").bind("keydown",function(e) {
		if(e.keyCode == 27){
			box.fadeOut(200,function(){box.remove();});
		}
	});
	box.find(".close").click(function(e){e.preventDefault();box.fadeOut(200,function(){box.remove();});return false;});
	var c = box.find(".content");
	if(isUrl){
		c.html("Loading...").addClass("loading");
		query(content,{"format":"html","gethtml":true},c);
	}
	else{
		c.find(".content").html(content);
	}
	box.animate({top:($(window).scrollTop()+55)+"px"});
	box.fadeIn();
	return true;
}
function keepConnection(u){
	if(u==undefined){u="ajax";}
	query(HOME+u,{"gethtml":0},null,false);
}
function query(u,d,c,l,p){
	if(typeof(d)!="object"){d=new Object();d["s"]=0;}
	if(l==undefined){l=true;}
	if(d.format==undefined)d["format"]="json";
	d["ajax"]=true;
	if(l===true){activateLoadingState();}
	$.ajax({url: u,data:d}).done(function(r){
		if(d.format == "json"){
			if(r.error != null){notify(r.error,0);}
			else if(r[0] != null && r[0].error != null){notify(r[0].error,0);}
			if(r.msg != null){notify(r.msg,4);}
			if(r.script != null){jQuery.globalEval(r.script);}
			if(c instanceof jQuery && r.html != null){
				if(c.hasClass("append")){c.append(r.html);}
				else if(c.hasClass("prepend")){c.prepend(r.html);}
				else{c.html(r.html);}
				activate(c);
			}
			else{
				if(c!=undefined){activate(c);}
			}
		}
		else{
			c.html(r);
			activate(c);
		}
		if(p!=undefined){p();}
		if(l===true){deactivateLoadingState();}
		return true;
	});
}
function randStr(l){
	if(l==null){l=10;}
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < l; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}
function searchRequest(str,outputId){
	var o = $("#"+outputId);
	if(str.length < 2){var u = CURRENT;infinite=true;}else{var u = app.HOME+"search";infinite=false;}
	$.ajax({
		url: u,
		data:{"ajax":1,"format":"json","s":s,"x":str},
		dataType: "json",
		success: function(data){
			deactivateLoadingState();	
			$(".loading").fadeOut();
			if(data.error != null){alert(data.error);}
			else if(data[0] != null && data[0].error != null){alert(data[0].error);}
			if(data.msg != null){notify(data.msg);}
			if(data.script != null){jQuery.globalEval(data.script);}
			o.html(data.html);
			activate(o);
			p=false;
			return true;
		}
	});
}
function hashChange(b){
	var u = location.hash;
	u = u.substring(1,u.length);
	query(HOME+u,{"gethtml":true},b);
}
function parseUri(str){
	var	o   = parseUri.options,
		m   = o.parser[o.strictMode ? "strict" : "loose"].exec(str),
		uri = {},
		i   = 14;

	while (i--) uri[o.key[i]] = m[i] || "";

	uri[o.q.name] = {};
	uri[o.key[12]].replace(o.q.parser, function ($0, $1, $2) {
		if ($1) uri[o.q.name][$1] = $2;
	});
	return uri;
};
parseUri.options = {
	strictMode: false,
	key: ["source","protocol","authority","userInfo","user","password","host","port","relative","path","directory","file","query","anchor"],
	q:   {
		name:   "queryKey",
		parser: /(?:^|&)([^&=]*)=?([^&]*)/g
	},
	parser: {
		strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
		loose:  /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
	}
};
function submitForm(form){
	var counter;
	var formElements = form.elements;
	var u = form.action+"?";
	var i=0;
	var gethtml = false;
	if($(form).hasClass("gethtml")){gethtml=true;}
	$(form).find("input[type=hidden],input[type=text],input[type=radio]:checked,input[type=checkbox]:checked,option:selected,textarea").each(function(){
		if(i!=0){u+="&";}
		var n=this.name;
		var v = this.value;
		$(this).prop('disabled', true);
		v = urlencode(v);
		if(n==null||n=='undefined'){n=$(this).data("name");}
		u += n+"="+v;
		i++;
	});
	$.ajax({
		url:  u,
		data:{"format":"json","gethtml":gethtml,"ajax":true},
		dataType: "json",
		success: function(data){
			deactivateLoadingState();
			if(data.error != null){alert(data.error);return false;}
			if(data.msg != null){notify(data.msg);}
			if(data.script != null){jQuery.globalEval(data.script);}
			$(form).find("input[type=hidden],input[type=text],input[type=radio]:checked,input[type=checkbox]:checked,option:selected,textarea").each(function(){
				$(this).prop('disabled', false);
				if(!$(this).hasClass("update")){
					$(this).data("placeholder",$(this).val());
					$(this).val("");
				}
				else{
					$(this).data("placeholder",$(this).val());
				}
				$(this).blur();
			});
			return true;
		}
	});
}
function typeIn(text,containerId) {
	var time = 0;
	var l = text.length;
	for (var i=0; i<l; i++) {
		time += (Math.random() * 120);
		setTimeout("document.getElementById('"+containerId+"').innerHTML += '"+text.substring(i,i+1)+"'",time);
	}
}
var javascript_countdown = function () {
	var time_left = 10;
	var output_element_id = 'javascript_countdown_time';
	var keep_counting = 1;
	var no_time_left_message = 'Now!';
 
	function countdown() {
		if(time_left < 2) {
			keep_counting = 0;
		}
 
		time_left = time_left - 1;
	}
 
	function add_leading_zero(n) {
		if(n.toString().length < 2) {
			return '0' + n;
		} else {
			return n;
		}
	}
 
	function format_output() {
		var days,hours, minutes, seconds;
		var t = time_left;
		var secondsPerDay = 60 * 60 * 24;
		days = Math.floor(t / secondsPerDay);
		
		t = t - secondsPerDay * days;
		seconds = t % 60;
		minutes = Math.floor(t / 60) % 60;
		hours = Math.floor(t / 3600);
 
		seconds = add_leading_zero( seconds );
		minutes = add_leading_zero( minutes );
		hours = add_leading_zero( hours );
		return days + " days " + hours + ':' + minutes + ':' + seconds;
	}
 
	function show_time_left() {
		document.getElementById(output_element_id).innerHTML = format_output();
	}
 
	function no_time_left() {
		document.getElementById(output_element_id).innerHTML = no_time_left_message;
	}
 
	return {
		count: function () {
			countdown();
			show_time_left();
		},
		timer: function () {
			javascript_countdown.count();
 
			if(keep_counting) {
				setTimeout("javascript_countdown.timer();", 1000);
			} else {
				no_time_left();
			}
		},
		setTimeLeft: function (t) {
			time_left = t;
			if(keep_counting == 0) {
				javascript_countdown.timer();
			}
		},
		init: function (t, element_id,doneMsg) {
			time_left = t;
			output_element_id = element_id;
			no_time_left_message = doneMsg;
			javascript_countdown.timer();
		}
	};
}();
function setCookie(n,v,e){
	var ed=new Date();
	ed.setDate(ed.getDate()+e);
	document.cookie=n+ "=" +escape(v)+
	((e==null) ? "" : ";expires="+ed.toUTCString());
}
function makeDate(yearId,monthId,dayId){
	var cDate = new Date();
	var y = parseInt(document.getElementById(yearId).value);
	if(y > cDate.getFullYear()+10 || y < 1){y = cDate.getFullYear();}
	var m = parseInt(document.getElementById(monthId).value);
	if(m < 1 || m == '-1'){m = 1;}
	if(m > 12){m = 12;}
	var d = parseInt(document.getElementById(dayId).value);
	if(d == undefined || d < 1 || d > 31){d = cDate.getDate();}
	return y+'-'+m.padZero()+'-'+d.padZero();
}
function initScroll(e){
	$(e).css({overflow:"hidden"});
	setTimeout('$("'+e+'").jScrollPane();',300);
}
function urldecode(str){
	if(typeof str == 'undefined'){return null;}
	var hash_map = {}, ret = str.toString(), unicodeStr='', hexEscStr='';
	var replacer = function(search, replace, str) {
		var tmp_arr = [];
		tmp_arr = str.split(search);
		return tmp_arr.join(replace);
	};
	hash_map["'"]   = '%27';
	hash_map['(']   = '%28';
	hash_map[')']   = '%29';
	hash_map['*']   = '%2A';
	hash_map['~']   = '%7E';
	hash_map['!']   = '%21';
	hash_map['%20'] = '+';
	hash_map['\u00DC'] = '%DC';
	hash_map['\u00FC'] = '%FC';
	hash_map['\u00C4'] = '%D4';
	hash_map['\u00E4'] = '%E4';
	hash_map['\u00D6'] = '%D6';
	hash_map['\u00F6'] = '%F6';
	hash_map['\u00DF'] = '%DF';
	hash_map['\u20AC'] = '%80';
	hash_map['\u0081'] = '%81';
	hash_map['\u201A'] = '%82';
	hash_map['\u0192'] = '%83';
	hash_map['\u201E'] = '%84';
	hash_map['\u2026'] = '%85';
	hash_map['\u2020'] = '%86';
	hash_map['\u2021'] = '%87';
	hash_map['\u02C6'] = '%88';
	hash_map['\u2030'] = '%89';
	hash_map['\u0160'] = '%8A';
	hash_map['\u2039'] = '%8B';
	hash_map['\u0152'] = '%8C';
	hash_map['\u008D'] = '%8D';
	hash_map['\u017D'] = '%8E';
	hash_map['\u008F'] = '%8F';
	hash_map['\u0090'] = '%90';
	hash_map['\u2018'] = '%91';
	hash_map['\u2019'] = '%92';
	hash_map['\u201C'] = '%93';
	hash_map['\u201D'] = '%94';
	hash_map['\u2022'] = '%95';
	hash_map['\u2013'] = '%96';
	hash_map['\u2014'] = '%97';
	hash_map['\u02DC'] = '%98';
	hash_map['\u2122'] = '%99';
	hash_map['\u0161'] = '%9A';
	hash_map['\u203A'] = '%9B';
	hash_map['\u0153'] = '%9C';
	hash_map['\u009D'] = '%9D';
	hash_map['\u017E'] = '%9E';
	hash_map['\u0178'] = '%9F';
	hash_map['<'] 	   = '%3C';
	hash_map['>'] 	   = '%3E';
	hash_map['/'] 	   = '%2F';
	hash_map['@']	   = '%40';
	hash_map['e']	   = '%E9';
	hash_map[' ']	   = '%20';
	for (unicodeStr in hash_map) {
		hexEscStr = hash_map[unicodeStr];
		ret = replacer(hexEscStr, unicodeStr, ret);
	}
	ret = decodeURIComponent(ret);
	
	return ret;
}
function urlencode(str){
	str = (str + '').toString();
    return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').
    replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+').replace("&","%26").replace("#","%23");
}
function SHA256(s){
	var chrsz   = 8;
	var hexcase = 0;
	function safe_add (x, y) {
		var lsw = (x & 0xFFFF) + (y & 0xFFFF);
		var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
		return (msw << 16) | (lsw & 0xFFFF);
	}
	function S (X,n){return (X >>> n ) | (X << (32 - n));}
	function R (X,n){return (X >>> n );}
	function Ch(x,y,z){return ((x & y) ^ ((~x) & z));}
	function Maj(x,y,z){return ((x & y) ^ (x & z) ^ (y & z));}
	function Sigma0256(x){return (S(x, 2) ^ S(x, 13) ^ S(x, 22));}
	function Sigma1256(x){return (S(x, 6) ^ S(x, 11) ^ S(x, 25));}
	function Gamma0256(x){return (S(x, 7) ^ S(x, 18) ^ R(x, 3));}
	function Gamma1256(x){return (S(x, 17) ^ S(x, 19) ^ R(x, 10));}
 
	function core_sha256 (m, l) {
		var K = new Array(0x428A2F98, 0x71374491, 0xB5C0FBCF, 0xE9B5DBA5, 0x3956C25B, 0x59F111F1, 0x923F82A4, 0xAB1C5ED5, 0xD807AA98, 0x12835B01, 0x243185BE, 0x550C7DC3, 0x72BE5D74, 0x80DEB1FE, 0x9BDC06A7, 0xC19BF174, 0xE49B69C1, 0xEFBE4786, 0xFC19DC6, 0x240CA1CC, 0x2DE92C6F, 0x4A7484AA, 0x5CB0A9DC, 0x76F988DA, 0x983E5152, 0xA831C66D, 0xB00327C8, 0xBF597FC7, 0xC6E00BF3, 0xD5A79147, 0x6CA6351, 0x14292967, 0x27B70A85, 0x2E1B2138, 0x4D2C6DFC, 0x53380D13, 0x650A7354, 0x766A0ABB, 0x81C2C92E, 0x92722C85, 0xA2BFE8A1, 0xA81A664B, 0xC24B8B70, 0xC76C51A3, 0xD192E819, 0xD6990624, 0xF40E3585, 0x106AA070, 0x19A4C116, 0x1E376C08, 0x2748774C, 0x34B0BCB5, 0x391C0CB3, 0x4ED8AA4A, 0x5B9CCA4F, 0x682E6FF3, 0x748F82EE, 0x78A5636F, 0x84C87814, 0x8CC70208, 0x90BEFFFA, 0xA4506CEB, 0xBEF9A3F7, 0xC67178F2);
		var HASH = new Array(0x6A09E667, 0xBB67AE85, 0x3C6EF372, 0xA54FF53A, 0x510E527F, 0x9B05688C, 0x1F83D9AB, 0x5BE0CD19);
		var W = new Array(64);
		var a, b, c, d, e, f, g, h, i, j;
		var T1, T2;
		m[l >> 5] |= 0x80 << (24 - l % 32);
		m[((l + 64 >> 9) << 4) + 15] = l;
		for ( var i = 0; i<m.length; i+=16 ) {
			a = HASH[0];
			b = HASH[1];
			c = HASH[2];
			d = HASH[3];
			e = HASH[4];
			f = HASH[5];
			g = HASH[6];
			h = HASH[7];
 
			for ( var j = 0; j<64; j++) {
				if (j < 16) W[j] = m[j + i];
				else W[j] = safe_add(safe_add(safe_add(Gamma1256(W[j - 2]), W[j - 7]), Gamma0256(W[j - 15])), W[j - 16]);
 
				T1 = safe_add(safe_add(safe_add(safe_add(h, Sigma1256(e)), Ch(e, f, g)), K[j]), W[j]);
				T2 = safe_add(Sigma0256(a), Maj(a, b, c));
 
				h = g;
				g = f;
				f = e;
				e = safe_add(d, T1);
				d = c;
				c = b;
				b = a;
				a = safe_add(T1, T2);
			}
 
			HASH[0] = safe_add(a, HASH[0]);
			HASH[1] = safe_add(b, HASH[1]);
			HASH[2] = safe_add(c, HASH[2]);
			HASH[3] = safe_add(d, HASH[3]);
			HASH[4] = safe_add(e, HASH[4]);
			HASH[5] = safe_add(f, HASH[5]);
			HASH[6] = safe_add(g, HASH[6]);
			HASH[7] = safe_add(h, HASH[7]);
		}
		return HASH;
	}
 
	function str2binb (str) {
		var bin = Array();
		var mask = (1 << chrsz) - 1;
		for(var i = 0; i < str.length * chrsz; i += chrsz) {
			bin[i>>5] |= (str.charCodeAt(i / chrsz) & mask) << (24 - i%32);
		}
		return bin;
	}
	function binb2hex (binarray) {
		var hex_tab = hexcase ? "0123456789ABCDEF" : "0123456789abcdef";
		var str = "";
		for(var i = 0; i < binarray.length * 4; i++) {
			str += hex_tab.charAt((binarray[i>>2] >> ((3 - i%4)*8+4)) & 0xF) +
			hex_tab.charAt((binarray[i>>2] >> ((3 - i%4)*8  )) & 0xF);
		}
		return str;
	}
	s = utf8_encode(s);
	return binb2hex(core_sha256(str2binb(s), s.length * chrsz));
}
function price2str(price){
	price /= 100;
	return priceStr = price.toFixed(2).replace(/\./,",") + " €";
}
function str2price(str){
	var price = parseFloat(str.replace(',','.').replace('€',''));
	if(!isNaN(price)){
		return Math.round(price*100);
	}else{
		return NaN;
	}
}
function prettyBytes(b,f){
	if(b >= 1099511627776){
		return Math.round(b/1099511627776,f)+"TB";
	}
	else if(b >= 1073741824){
		return Math.round(b/1073741824,f)+"GB";
	}
	else if(b >= 1048576){
		return Math.round(b/1048576,f)+"MB";
	}
	else if(b >= 1024){
		return Math.round(b/1024,f)+"KB";
	}
	else{
		return Math.round(b,2)+"B";
	}
}
function prettyNumber(b,f){
	if(f==undefined){f=1;}
	if(b >= 1000000){
		return Math.round(b/1000000,f)+"M";
	}
	else if(b >= 1000){
		return Math.round(b/1000,f)+"K";
	}
	else{
		return b;
	}
}
Number.prototype.padZero = function(len){
	var s= String(this), c= '0';
	len= len || 2;
	while(s.length < len) s= c + s;
	return s;
};