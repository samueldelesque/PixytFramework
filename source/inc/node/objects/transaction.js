Transaction = Backbone.Model.extend({
	url:  function(){
		return "/transaction/"+this.id;
	},
	
	initialize: function(){
	},
	
	parse:function(data){
		data.amt = price2str(data.amt);
		return data;
	},
	
	defaults: {
		id:null,
		uid:null,
		account:0,
		paymentMethod:0,
		amt:0,
		date:"2013-01-01",
		name:"",
		created:null,
		modified:null,
	},
});