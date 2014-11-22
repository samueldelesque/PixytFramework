SettingsView = Backbone.Marionette.Layout.extend({
	tagName: "div",
	id: "settings",
	className:"d1000 center",
	template: "settings/layout",
	tab: "profile",
	route:"account/settings",
	
	initialize: function(d){
		var that = this;
		this.sites = d.sites;
		console.log("Instantiating WelcomeView");
		if(!app.USAGE){
			app.USAGE = {used:0,allocated:0,percentage:0};
			$.ajax({url:"/account/usage.json",success: function(data){app.USAGE = data;app.events.trigger("usage:loaded");}});
			app.events.on("usage:loaded",function(){
				$("#usage_used",that.$el).html(prettyBytes(app.USAGE.used));
				$("#usage_allocated",that.$el).html(prettyBytes(app.USAGE.allocated));
				$("#usage_percentage",that.$el).animate({width:app.USAGE.percentage+"%"});
			});
		}
		this.listenTo(this.sites, 'add', this.render);
		this.listenTo(this.sites, 'change', this.render);
	},
	
    render: function(){
		var that = this;
        var template = app.templates[this.template];
		this.$el.html(template({tab:this.tab,user:app.user.toJSON(),sites:this.sites,usage:app.USAGE})).find("select#language option[value='"+app.user.get("settings").language+"']").prop('selected', true);
		activate(this.$el);
        $("#usage_percentage",that.$el).css({width:0});
		app.events.trigger("usage:loaded");
		
		$('form.user',this.$el).submit(function(e){
			console.log("Saving User");
			e.preventDefault();
			app.user.set($(this).serializeObject());
			app.user.save(null,{
				success:function(model,response){
					console.log(model,response);
					$(".message",that.$el).html("Settings saved!").removeClass("error").addClass("success").fadeIn(200,function(){setTimeout(function(){$(".message",that.$el).fadeOut();},2000);});
				},
				error:function(model,request){
					var response = request.responseJSON;
					if(response.errors){
						response.error = response.errors.join("<br/>");
					}
					else if(!response.error){
						response.error = "Failed to create user!";
					}
					$(".message",that.$el).html(response.error).removeClass("success").addClass("error").fadeIn();
				}
			});
		});
    }
});