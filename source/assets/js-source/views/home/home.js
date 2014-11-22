HomeView = Backbone.Marionette.Layout.extend({
	tagName: "div",
	id: "home",
	template: "home/feed",
	route:"",
	data:{},
	
	initialize: function(d){
		console.log("Instantiating HomeView");
		this.data = d;
	},
	
	events: {
	},
	
    render: function(){
		var that = this;
        var template = app.templates[this.template];
        this.$el.html(template(this.data/*{items:[
			{title:"Library",id:1,uid:1,size:"w2"},
			{title:"Paysage",id:2,uid:2,size:"w1"},
			{title:"Library",id:3,uid:1,size:"w2"},
			{title:"Flower",id:4,uid:2,size:"w1"},
			{title:"Library",id:5,uid:1,size:"w3"},
			{title:"Flower",id:4,uid:1,size:"w1"},
			{title:"Flower",id:2,uid:2,size:"w1"},
		]}*/));
		this.$el.find('img').on('load', function() {that.$el.find(".feed").freetile({animate: true,elementDelay: 10});});
    }
});