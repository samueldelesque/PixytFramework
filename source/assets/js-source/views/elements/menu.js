MenuView = Backbone.Marionette.ItemView.extend({
	className: "inner-menu",
	
	initialize: function(){
		console.log("Instantiating MenuView");
	},
	
	events: {
	},
	
    render: function(){
        var template = app.templates["menu"];
        this.$el.html(template({}));
        this.$el.html(template({me:app.ME,connected:app.CONNECTED}));
    }
});