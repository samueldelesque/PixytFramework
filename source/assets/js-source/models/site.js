app.models.site = Backbone.Model.extend({
	url: function(){
		return "/site/"+this.id;
	},
	
	initialize: function(){
		console.log("Loading Site::"+this.id);
	},
	
	defaults: {
		id:null,
		uid: null,
		adminNIC: "",
		client: "",
		title: "",
		description: "",
		url: "",
		channel: 1,
		style: {},
		settings: {},
		alias: "",
		cover: null,
		content: [],
		modules: [],
		expirationDate: "",
		created: 0,
		modified: 0,
		deleted: 0,
	},
});