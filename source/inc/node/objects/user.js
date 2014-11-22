User = Backbone.Model.extend({
	url: function(){
		if(!this.id){return "user"}
		return "/user/"+this.id;
	},
	
	initialize: function(){
		console.log("Loading User::"+this.id);
	},
	
	defaults: {
		id:null,
	},
});