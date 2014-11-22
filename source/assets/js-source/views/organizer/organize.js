OrganizeView = Backbone.Marionette.Layout.extend({
	tagName: "div",
	id: "organize",
	template: "organizer/layout",
	action: "",
	route:"organize",
	filters: {},
	
	regions: {
		dropzone: "#dropzone",
		sidebar: "#sidebar .navigation",
	},
	
	initialize: function(d){
		this.sites = d.sites;
		this.stacks = d.stacks;
		console.log("Instantiating OrganizeView");
		this.listenTo(this.stacks, 'add', this.render);
		this.listenTo(this.stacks, 'change', this.render);
	},
		
	events:{
		"click .navigation .filter":"addfilter",
		"click .navigation .toolbox .createstack":"createstack",
	},
	
	addfilter: function(e){
		if($(e.currentTarget).data("value") == ""){this.filters = _.omit(this.filters,$(e.currentTarget).data("filter"));}
		else this.filters[$(e.currentTarget).data("filter")] = $(e.currentTarget).data("value");
		this.render();
	},
	
	createstack: function(e){
		var stack = new Stack({title:"New Stack",access:3});
		stack.save(null,{
			success:function(stack,data){
				console.log("Stack created!:",stack.toJSON());
				app.router.navigate("/stack/"+stack.id, {trigger: true});
			},
			error: function(stack,data){
				console.log("Failed to create stack!",data);
				alert("An error occured. Please try to reload the app, or contact a site admin.");
			},
		});
	},
	
    render: function(){
        var template = app.templates[this.template];
		var data = this.stacks.filters(this.filters);
        this.$el.html(template({stacks:data,genres:_.uniq(this.stacks.pluck("genre"))}));
		activate(this.$el);
    }
});