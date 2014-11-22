app.module("connected",{
	startWithParent: false,
	
	define: function(){
		this.on("before:start", function(options){
			console.log("Starting Connected module");
			//views
			
			app.backend = Backbone.Router.extend({
				routes: {
					"": "home",
				},
				
				initialize: function () {
				},
				
			});
		});
	}
});