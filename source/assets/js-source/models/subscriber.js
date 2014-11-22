app.models.subscriber = Backbone.Model.extend({
	url: function(){
		if(!this.id){return "subscriber"}
		return "/subscriber/"+this.id;
	},

	initialize: function(){
		console.log("Loading Subscriber::"+this.id);
	},
	
	defaults: {
		id:null,
	},
});