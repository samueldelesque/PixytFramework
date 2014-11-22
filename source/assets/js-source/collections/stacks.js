app.collections.stacks = Backbone.Collection.extend({
	url: "/stacks/all",
	model: Stack,
	
	initialize: function(){
		return this;
	},
	
	filters: function(x){
		if(_.isEmpty(x)){return this.toJSON();}
		return new Stacks(this.where(x)).toJSON();
	}
});