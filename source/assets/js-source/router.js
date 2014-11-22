/*
 *
 *	Pixyt Site Router
 *
 *
 */

//something to call after a successful login or action within a view
app.then = function(){};

//create a default itemview
app.views["default"] = Backbone.Marionette.ItemView.extend({
	data:{},
	initialize: function(data,obj){
		this.data = data;
		this.data.obj = obj;
	},
	
    render: function(){
    	console.log("Trying to fetch template",this.data.template);
        var template = (app.templates[this.data.template])?app.templates[this.data.template]:app.templates["404"];
        this.$el.html(template(this.data));
        this.$el.addClass(this.data.template.replace(".","-").replace("404","notfound"));
    }
});

app.addInitializer(function(options){

	// The #container is the page HTML. The first page is rendered server side.
	app.layout = {
		container: $("#container"),
		page: new Backbone.Marionette.Region({el: "#page"}),
		menu: new Backbone.Marionette.Region({el: "#menu"}),
		// notifications: new Backbone.Marionette.Region({el: "#notifications"}),
	}

	//render the menu
	if(app.views["menu"])app.layout.menu.show(new app.views["menu"]());
	app.render(app.layout.menu.$el);

	// Render each element to add specific behaviours
	app.layout.page.on("show",function(){
		app.render(app.layout.page.$el);
	});
	app.layout.menu.on("show",function(){app.render(this.$el);});

	if(app.pages){
		//Now lets build the Router.
		var router = Backbone.Router.extend();
		app.router = new router();

		app.createRoutes = function(pages,parent,path){
			if(!path)path="";
			_.each(pages,function(page,target,pages){
				if(target == "index"){
					page.url = path;
					page.handle = "index";
				}
				else{
					page.url = path+target;
					//in case a template has not been specified, try detecting using the formated url;
					if(!page.template)page.template = page.url.replace("/",".");
				}
				// if(parent)console.log("child page: ",page,"| path = "+page.url);
				if(page.content){
					app.createRoutes(page.content,target,page.url+"/");
					//console.log("Making child routes into ["+target+"]["+page.url+"]",page.content);
				}
				if(page.dynamic)page.url+="(/:d)";
				
				// console.log(page);

				app.router.route(page.url, page.template, function(d){
			    	// alert(page.template);
					//change current page target
					app.layout.container.removeClass(app.TARGET).addClass(target);
					app.TARGET = target;

					if(page.requireLogin && !app.user.id){
						app.then = function(){app.router.navigate(page.url, {trigger: true});};
						app.router.navigate("/login", {trigger: true});
						return;
					}

					//call view
					if(app.views[page.template]){
						console.log("initializing "+page.template);
						app.currentView = new app.views[page.template](page,d);
					}
					else if(page.url == ""){
						console.log("No Index route found, using first page",pages[0]);
						app.currentView = new app.views[pages[0].template](pages[0],d);
					}
					else{
						console.log(page.template+" view not found, using default.");
						if(!app.templates[page.template] && app.templates["404"]){page.template = "404";}
						app.currentView = new app.views["default"](page,d);
					}

							 

					//render the view
					app.layout.page.show(app.currentView);

					//set page title
					document.title = page.title;

					//log stats
					ga('send', {
						"page":page.url,
						"title":document.title,
						"hitType":"pageview",
						"hitCallback": function() {
		 					console.log("Page traced ("+page.url+")");
						}
					});
				});
			});
		};

		app.createRoutes(app.pages);
	}
	else{
		app.router.bind("route",function(route, router) {
			var l = "/"+route +"/"+ router.join("/");
			ga('send', {
				"page":l,
				"title":document.title,
				"hitType":"pageview",
				"hitCallback": function() {
 					console.log("Page traced ("+l+")");
				}
			});
		});
		console.error("app.pages undefined! Cannot build routes.");
	}
	
	Backbone.history.start({pushState: true,trackDirection: true});
});
