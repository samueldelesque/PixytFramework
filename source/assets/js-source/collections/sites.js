app.collections.sites = Backbone.Collection.extend({
	url: "/sites/all",
	model: app.models.site,
	
	initialize: function(){
		return this;
	},
	
	filters: function(x){
		if(_.isEmpty(x)){return this.toJSON();}
		return new Sites(this.where(x)).toJSON();
	}
});