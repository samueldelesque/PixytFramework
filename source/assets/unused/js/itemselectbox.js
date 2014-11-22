// JavaScript Document

var currentMode = ''; // '', 'series' or 'photos'
var selectedPhotos = new Array();
var lastPhotoClicked = -1;
var isb_strNbrSelectedImages;
$(function() {
	$(window).resize(function() {
		itemSelectBoxFixContentHeight();
	});
	
	itemSelectBoxUpdateSelectedPhotos();

	$("#itemselectboxopenlink").click(function(){
		selectedPhotos = new Array();
		itemSelectBoxUpdateSelectedPhotos();
		itemSelectBoxLoadSeries();
		$("#boxoverlay").fadeIn("fast",function(){
			$("#selectbox").show();
			$("#selectbox").animate({"right":"0"},500);
			itemSelectBoxFixContentHeight();
		});
	});
	$("#selectboxclose").click(function(){
		if(selectedPhotos.length > 0){
			if(!confirm(isb_strSureToQuitImagesSelected))
				return;
		}
		itemSelectBoxClose();
	});
			 
});

function itemSelectBoxClose(){
	$("#selectbox").animate({"right":"-50%"},500,function(){
		$("#boxoverlay").fadeOut("fast");
		$("#selectbox").hide();
	});
}

// Change la hauteur de la box dynamiquement (car pas de css valide encore)
function itemSelectBoxFixContentHeight(){
	var one = $('#selectboxclose').height();
	var two = $('#selectboxtitle').height(); 
	var remaining_height = parseInt($("#selectbox").height() - one - two - 10); 
	$('#selectboxcontent').height(remaining_height);
}

function itemSelectBoxLoadSeries(genre){
	if(genre == null){
		genre = "any";
	}
	
	var ajaxData = cloneObj(isb_ajaxVariables);
	ajaxData['showSeries'] = true;
	ajaxData['genre'] = genre;
	
	$.ajax({
	  url: isb_url,
	  data: ajaxData,
	  cache: false,
	  error: function(jqXHR, textStatus, errorThrown){
		  LPalert('Ajax connexion error :'+textStatus);
	  },
	  success: function(data) {
		currentMode = 'series';
		$("#selectboxcontent").html(data);
		activate($("#selectboxcontent"));
	  }
	});
}

function itemSelectBoxLoadSerie(serieId){
	if(serieId==null){alert("StackId not defined.");}
	var ajaxData = cloneObj(isb_ajaxVariables);
	ajaxData['showSerie'] = true;
	ajaxData['kid'] = serieId;
	
	$.ajax({
		url: isb_url+"/"+serieId,
		type: "POST",
		data: ajaxData,
		cache: false,
		error: function(jqXHR, textStatus, errorThrown){
		  LPalert('Ajax connexion error :'+textStatus);
		},
		success: function(data) {
			lastPhotoClicked = -1;
			$("#selectboxcontent").html(data);
			currentMode = 'photos';
			$("#selectbox_backbutton").html(isb_strBack);
			itemSelectBox_markSelectedPhotos();
			activate($("#selectboxcontent"));
		}
	});
}

function itemSelectBoxBackClicked(){
		itemSelectBoxLoadSeries();
		$("#selectbox_backbutton").html(isb_strRefresh);
}

function itemSelectBoxPhotoClicked(e){
	var pid = e.data["pid"];
	
	if(e.shiftKey){
		var lastIndex = sortedPhotos.indexOf(pid.toString());
		var firstIndex = sortedPhotos.indexOf(lastPhotoClicked.toString());
		
		if(lastIndex == -1 || firstIndex == -1 || firstIndex == lastIndex)
			return;
		
		if(lastIndex < firstIndex){
			var t = firstIndex;
			firstIndex = lastIndex-1;
			lastIndex = t-1;
		}
		
		for(var i=firstIndex+1 ; i <= lastIndex ; i++){
			pid2 = parseInt(sortedPhotos[i.toString()],10);
			if($.inArray(pid2, selectedPhotos) != -1){
				selectedPhotos.splice(selectedPhotos.indexOf(pid2), 1);
				$("#previewBox_pid_"+pid2).removeClass("previewBoxSelected");
			}else{
				selectedPhotos.push(pid2);
				$("#previewBox_pid_"+pid2).addClass("previewBoxSelected");
			}
		}
		
		
	}else{	
		if($.inArray(pid, selectedPhotos) != -1){
			selectedPhotos.splice(selectedPhotos.indexOf(pid), 1);
			$("#previewBox_pid_"+pid).removeClass("previewBoxSelected");
		}else{
			selectedPhotos.push(pid);
			$("#previewBox_pid_"+pid).addClass("previewBoxSelected");
		}
	}
	itemSelectBoxUpdateSelectedPhotos();
	
	lastPhotoClicked = pid;
}

function itemSelectBoxUpdateSelectedPhotos(){
	$("#selectedcounttext").html("<b>"+selectedPhotos.length + "</b>" + isb_strNbrSelectedImages);
}

function itemSelectBox_markSelectedPhotos(){
	for(var i=0;i<selectedPhotos.length;i++){
		if($("#previewBox_pid_"+selectedPhotos[i]).length == 1){
			$("#previewBox_pid_"+selectedPhotos[i]).addClass("previewBoxSelected");
		}
	}
}

function itemSelectBoxRefresh() {
	location.reload();
};

function itemSelectBoxAddClicked(){
	if(selectedPhotos.length == 0){
		alert(isb_strNoPhotosSelected);
	}else{
		var jsonData = JSON.stringify(selectedPhotos);
		
		var ajaxData = cloneObj(isb_ajaxVariables);
		ajaxData['addPhotos'] = true;
		ajaxData['selectedPhotos'] = jsonData;
		
		$.ajax({
		  url: isb_url,
		  type: "POST",
		  data: ajaxData,
		  cache: false,
		  error: function(jqXHR, textStatus, errorThrown){
			  LPalert('Ajax connexion error :'+textStatus);
		  },
		  success: function(data) {
			if(data == "OK"){
				itemSelectBoxClose();
				LPalert(isb_strPhotosUploaded);
				setTimeout("itemSelectBoxRefresh()", 500);
			}else{
				selectedPhotos = new Array();
				$("#selectboxcontent").html("Upload error:<br />"+data);
			}
	 	  }
		});
	}
}
