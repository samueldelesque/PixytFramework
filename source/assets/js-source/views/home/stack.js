StackView = Backbone.Marionette.Layout.extend({
	tagName: "div",
	model: Stack,
	collecton: Photos,
	template: "stack",
	className: "stack",
	titleEditor: null,
	
	initialize: function(){
		console.log("Instantiating StackView");
		this.listenTo(this.model, 'change', this.render);
		this.listenTo(this.collection, 'add', this.render);
		this.listenTo(this.collection, 'change', this.render);
	},
	
	events: {
	},
	
    render: function(){
		var that = this;
        var template = app.templates[this.template];
        this.$el.html(template({stack:this.model.toJSON(),photos:this.collection.toJSON()}));
		
		
		$('form.stack',this.$el).submit(function(e){
			e.preventDefault();
			$(this).find("input,textarea").blur();
		}).change(function(){
			that.model.set($(this).serializeObject());
			that.model.save();
		});
		
		$(".dropzone .files",that.$el).fileupload({
			dataType: 'json',
			done: function (e, data) {
				console.log("File upload succeded");
				$('.progress .bar',$(this).parent()).delay(300).fadeOut();
				if(data.result.error){alert(data.result.error);}
				else{that.collection.add(data.result);}
			},
			progressall: function (e, data) {
				console.log("File upload: "+parseInt(data.loaded / data.total * 100, 10)+"%");
				var progress = parseInt(data.loaded / data.total * 100, 10);
				$('.progress .bar',that.$el).fadeIn().css(
					'width',
					progress + '%'
				);
			},
		});
    }
});