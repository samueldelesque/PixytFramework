ActivityView = Backbone.Marionette.Layout.extend({
	tagName: "div",
	id: "activity",
	template: "messages/activity",
	action: "",
	route:"account/activity",
	
	initialize: function(){
		console.log("Instantiating ActivityView");
		_.bindAll(this, 'render');
	},
		
	events:{
	},
	
    render: function(){
        var template = app.templates[this.template];
        this.$el.html(template({
			people:[{name:"Jeremy Cuenin",preview:"Salut comment sa v..."}],
			notifications:[{who:"Gaylord",did:"liked",what:"your photo"}]
		}));
    }
});