$.cookie("screen",window.screen.width+"x"+window.screen.height);
var d=new Date();
$.cookie("gmtOffset",parseInt(-d.getTimezoneOffset()/60),90);
var f = $("#footer");
var b = $("#page");
var w = $(window);
function setSize(){
	var ex = new Date();
	ex.setTime(ex.getTime()+(1800000));
	$.cookie("ww",w.width());
	$.cookie("wh",w.height());
}
setSize();
activate($("body"));
w.resize(function(){setSize();});
var ex = new Date();
ex.setTime(ex.getTime()+(1800000));
if($.cookie("visit") === null){
	$.cookie("visitstart", STAT_SESSION_START, {expires:ex,path:"/"});
	$.cookie("visit",STAT_SESSION, {expires:ex, path:"/"});
	$.cookie("from",FROM,{expires:ex, path:"/"});
}
else{
	$.cookie("visitstart", $.cookie("visitstart"),{expires:ex, path:"/"});
	$.cookie("visit", STAT_SESSION, {expires:ex, path:"/"});
	$.cookie("from",FROM,{expires:ex, path:"/"});
}
var pops=0;
window.onpopstate=function(e){if(pops>0)query(window.location.href,{"gethtml":true},$("#mainColumn"));pops++};

var _gaq = _gaq || [];_gaq.push(["_setAccount","UA-31248995-1"],["_setCustomVar",1, "Age", AGE,2],["_setDomainName", "pixyt.com"],["_setAllowLinker",true],["_trackPageview"],AUX,AUXB);(function(){var ga = document.createElement("script");ga.type = "text/javascript";ga.async = true;ga.src = PROTOCOL+"google-analytics.com/ga.js";var s = document.getElementsByTagName("script")[0];s.parentNode.insertBefore(ga, s);})();