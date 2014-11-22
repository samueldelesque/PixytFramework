app.models.photo = Backbone.Model.extend({
	url: function(){
		if(!this.id){return "/photo";}
		return "/photo/"+this.id;
	},
	
	initialize: function(){
		console.log("Loading Photo::"+this.id);
	},
	
	defaults: {
		id:0,
		uid: 0,
		kid: 0,
		prid: 0,
		fileid: 0,
		access: 0,
		channel: 0,
		title: "",
		genre: "",
		description: "",
		filedata: "",
		filename: "",
		filepath: "",
		width: 0,
		height: 0,
		color: 0,
		exif: [],
		tags: [],
		customOrder: 0,
		dateShot: 0,
		make: "",
		model: "",
		aperture: 0,
		sensitivity: 0,
		exposure: 0,
		focal: 0,
		software: "",
		subjectDistance: 0,
		R: 0,
		G: 0,
		B: 0,
		z: 0,
	},
});