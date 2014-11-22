SideEditor = Backbone.Marionette.Layout.extend({
	tagName: "div",
	model: Site,
	template: "site_editor",
	id: "SiteEditor",
	titleEditor: null,
	
	initialize: function(){
		console.log("Instantiating SiteEditor");
		this.listenTo(this.model, 'change', this.render);
	},
	
    render: function(){
		var that = this;
        var template = app.templates[this.template];
        this.$el.html(template({site:this.model.toJSON()}));
		activate(this.$el);
		
		$('form.site',this.$el).submit(function(e){
			e.preventDefault();
			$(this).find("input,textarea").blur();
		}).change(function(){
			that.model.set($(this).serializeObject());
			that.model.save();
		});
    }
});