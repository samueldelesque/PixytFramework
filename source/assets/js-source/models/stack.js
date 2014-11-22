Stack = Backbone.Model.extend({
	url:  function(){
		return "/stack/"+this.id;
	},
	
	initialize: function(){
	},
	
	defaults: {
		id:null,
		uid:null,
		title:"",
		genre:"",
		description:"",
		access:3,
		accessCode:"",
		tags:[],
		rating:null,
		cover:null,
		created:null,
		modified:null,
	},
});