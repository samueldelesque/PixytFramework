SyncGraphView = Backbone.Marionette.ItemView.extend({
	tagName: "div",
	template: "elements/graphs/sync",
	
    render: function(){
        var template = app.templates[this.template];
        this.$el.html(template({}));
    }
});