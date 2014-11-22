<?php
class Instants extends Interfaces{
	public function directory(){
		$r = '';
		$images = new Collection('Photo');
		$images->access=3;
		$m=10;
		$images->load($_REQUEST['s']*$m,$m);
		$r .= dv('d550 center','instants_'.$_REQUEST['s']);
		foreach($images->results as $img){
			$r .= $img->instant();
		}
		$r .= xdv();
		if(!IS_AJAX){
			T::$js[] = '
var p = false;
var s='.$_REQUEST['s'].';
function infinity(){
	if(p===false){
		if($(window).scrollTop() >= ($(document).height() - $(window).height())-800){
			p=true;
			s++;
			activateLoadingState();
			$.ajax({
				url: HOME+"instants",
				data:{"ajax":1,"datatype":"json","gethtml":true,"s":s,"loadmore":true},
				dataType: "json",
				success: function(data){
					deactivateLoadingState();
					if(data.error != null){alert(data.error);}
					else if(data[0] != null && data[0].error != null){alert(data[0].error);}
					if(data.msg != null){notify(data.msg);}
					if(data.script != null){jQuery.globalEval(data.script);}
					$("#mainColumn").append(data.html);
					activate($("#instants_"+s));
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
}
?>