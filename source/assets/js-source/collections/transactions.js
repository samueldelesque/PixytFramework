app.collections.transactions = Backbone.Collection.extend({
	url: "/transactions/all",
	model: app.models.transactions,
	
	initialize: function(){
		return this;
	},
	
	filters: function(x){
		if(_.isEmpty(x)){return this;}
		return new Transactions(this.where(x));
	},
	
	orderBy: function(x){
		if(_.isEmpty(x)){return this;}
		return new Transactions(this.sortBy(x));
	},
	
	format:function(){
		var formated = [];
		return new Transactions(formated);
	},
});