PhotoView = Backbone.Marionette.ItemView.extend({
	tagName: "div",
	model: Photo,
	template: "photo",
	
	initialize: function(){
		console.log("Instantiating PhotoView");
	},
	
	events: {
	},
	
    render: function(){
		var that = this;
        var template = app.templates[this.template];
        this.$el.html(template(this.model.toJSON()));
    }
});