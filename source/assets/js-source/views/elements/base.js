BaseView = Backbone.Marionette.Layout.extend({
	template: "elements/base",
	initialize: function(){
		console.log("Instantiating BaseView");
	},
	
	regions: {
		menu:"#menu",
		lightbox:"#lightbox",
		notifications:"#notifications",
		page:"#page",
	},
	
	events: {
	},
	
    render: function(){
        var template = app.templates[this.template];
        this.$el.html(template({}));
		this.menu.show(new MenuView());
    }
});