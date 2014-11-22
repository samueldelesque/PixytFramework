LoginView = Backbone.Marionette.Layout.extend({
	tagName: "div",
	id: "login",
	template: "elements/login",
	initialize: function(){
		console.log("Instantiating LoginView");
	},
	
	events: {
		"submit form":"login",
		"click #recoverpassword":"recoverpassword",
	},
	
	recoverpassword: function(){
		this.$el.find(".login").fadeOut();
		this.$el.find(".recoverpassword").fadeIn();
	},
	
	login: function(e){
		var that = this;
		e.preventDefault();
		var email = this.$(e.currentTarget).find("[name=email]").val();
		var password = this.$(e.currentTarget).find("[name=password]").val();
		$.ajax({type:"POST",url:"/home/login.json",data:{password:password,email:email},success:function(d){
			console.log(d);
			if(!d.id){alert("An error occured. Please reload the page and try again.");return;}
			app.user = new User(d);
			app.CONNECTED = true;
			app.layout.menu.show(new MenuView());
			app.router.navigate("", {trigger: true});
		},
		error: function(){
			that.$(".message").addClass("warning").html("Wrong email or password.").show("fast");
		}});
	},
	
    render: function(){
        var template = app.templates[this.template];
        this.$el.html(template({}));
    }
});