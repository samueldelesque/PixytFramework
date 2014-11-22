AccountingView = Backbone.Marionette.Layout.extend({
	tagName: "div",
	id: "activity",
	template: "admin/accounting/report",
	action: "",
	route:"admin/accounting",
	
	initialize: function(d){
		this.transactions = d.transactions;
		console.log("Instantiating AccountingView");
		this.listenTo(this.transactions, 'add', this.render);
		this.listenTo(this.transactions, 'change', this.render);
	},
	
    render: function(){
        var template = Handlebars.compile($(this.template).html());
        this.$el.html(template({transactions:this.transactions.orderBy("date").toJSON()}));
    }
});