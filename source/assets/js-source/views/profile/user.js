UserView = Backbone.Marionette.ItemView.extend({
	tagName: "div",
	model: User,
	template: "profile/user",
	
	initialize: function(data){
		if(data.model)this.model=data.model;
		console.log("Instantiating UserView");
	},
	
	events: {
	},
	
    render: function(){
		var that = this;
		console.log(this.model);
		console.log("UserView model: ",this.model instanceof Backbone.Model);
        var template = app.templates[this.template];
        this.$el.html(template(this.model.toJSON()));
    }
});