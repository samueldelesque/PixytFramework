WelcomeView = Backbone.Marionette.Layout.extend({
	tagName: "div",
	id: "welcome",
	template: "home/welcome",
	mapColors: ["#454545","#454545","#454545","#454545","#454545","#454545","#454545","#454545","#454545","#3C9DD0"],
	mapDots: [],
	windowProperties: {},
	animation: {
		"initiated": false
	},
	w: null,

	selectors: {
		animation: "#animation",
		capture: "#capture",
		overlay: "#featured .overlay",
		cta: "#cta",
		shutter: "#shutter",
		flash: "#flash",
		store: "#store",
		memoryCard: "#store .memory-card",
		laptopTop: "#store .top",
		laptopFront: "#store .front",
		featured: "#featured .full",
		overview: "#overview",
		publish: "#publish",
		map: "#map",
		interactiveMap: "#interactiveMap",
	},

	events: {
		"submit #cta form": "createSubscriberTop",
		"submit #footer_cta form": "createSubscriberBottom",
		"click #learn_how": "demoAnimation",
		"click .box.sync": "showSyncGraph",
		"click .box.publish": "showPublishGraph",
		"click .box.connect": "showConnectGraph",
		"scroll window": "scrollActions",
	},

	initialize: function(){
		console.log("Instantiating WelcomeView");
		this.w = $(window);
		this.w.scroll(this.scrollActions);
	},

	scrollActions: function(e){
		var t = this.w.scrollTop();
		console.log(e);
	},

	showSyncGraph: function(){
		console.log("Showing Sync graph");
		if(!app.sync_graph)app.sync_graph = new SyncGraphView({});
		app.layout.lightbox.show(app.sync_graph);
		// $(".graphics .active").fadeTo(0).removeClass("active");
		// $(".graphics .network").fadeIn().addClass("active");
	},

	showPublishGraph: function(){
		// $(".graphics .active").fadeTo(0).removeClass("active");
		// $(".graphics .digital").fadeIn().addClass("active");
	},

	showConnectGraph: function(){
		
	},

	createSubscriberTop: function(e){
		e.preventDefault();
		this.createSubscriber($("#cta form"),this.$el);
	},

	createSubscriberBottom: function(e){
		e.preventDefault();
		this.createSubscriber($("#footer_cta form"),this.$el);
	},

	createSubscriber: function(form){
		console.log("Trying to create subscriber...");
		var subscriber = new Subscriber();
		subscriber.set(form.serializeObject());
		subscriber.save(null,{
			success:function(model,response){
				if(!model.id){
					$(".message",form).removeClass("success").addClass("error").html("An error occured. Please reload the page and try again.").fadeIn();
					return;
				}
				$(".message",form).removeClass("error warning").addClass("success").html("Thank you!").fadeIn();
			},
			error:function(model,request){
				var response = request.responseJSON;
				if(response.errors){
					response.error = response.errors.join("<br/>");
				}
				else if(!response.error){
					response.error = "Something went wrong!";
				}
				$(".message",form).removeClass("sucess").addClass("error").html(response.error).fadeIn();
			}
		});
	},

	demoAnimation: function(e){
		e.preventDefault();
		var anim = this.animation;
		if(!anim.initiated){
			anim.initiated = true;
			anim.animation = $(this.selectors.animation,this.$el);
			anim.cta = $(this.selectors.cta,this.$el);
			anim.featured = $(this.selectors.featured,this.$el);
			anim.overlay = $(this.selectors.overlay,this.$el);
			anim.memoryCard = $(this.selectors.memoryCard,this.$el);
			anim.laptopPictures = $(this.selectors.laptopPictures,this.$el);
			anim.capture = $(this.selectors.capture,this.$el);
			anim.shutter = $(this.selectors.shutter,this.$el);
			anim.flash = $(this.selectors.flash,this.$el);
			anim.store = $(this.selectors.store,this.$el);
			anim.laptopTop = $(this.selectors.laptopTop,this.$el);
			anim.laptopFront = $(this.selectors.laptopFront,this.$el);
			anim.overview = $(this.selectors.overview,this.$el);
			anim.publish = $(this.selectors.publish,this.$el);
			anim.map = $(this.selectors.map,this.$el);
			anim.hide = function(){
				anim.animation.fadeOut("fast");
				anim.featured.css({
					opacity:0
				}).css({
					transform: "none",
					top:0,
					left:0,
					width:"100%",
					marginTop:0,
					marginLeft:0
				}).animate({
					opacity:1
				},400);
				anim.overlay.fadeIn(500,function(){
					$("#cta").delay(500).animate({
						top:"0"
					},400);
				});
			}
		}
		var self = this;
		anim.store.hide();
		anim.capture.hide();
		anim.laptopFront.find(".site-background").hide();
		anim.laptopTop.css({opacity:1});
		anim.laptopFront.css({opacity:0,display:"none"});
		anim.laptopFront.find(".img1").css({
			top:"11%",
			left:"17%",
		}).hide();
		anim.laptopFront.find(".img2").css({
			top:"11%",
			left:"34%",
		}).hide();
		anim.laptopFront.find(".img3").css({
			top:"30%",
			left:"17%",
		}).hide();
		anim.laptopFront.find(".img4").css({
			top:"30%",
			left:"34%",
		}).hide();
		anim.animation.show();
		
		$("#cta").animate({
			top:"-700px"
		},400,function(){
			anim.capture.delay(500).fadeIn("fast",function(){
				setTimeout(function(){
					anim.shutter[0].play();
					anim.flash.fadeIn("fast",function(){
						$(this).fadeOut("fast");
					});
				},500);

				anim.capture.delay(1000).fadeOut();
				anim.overlay.delay(1000).fadeOut();
				
				anim.featured.delay(1400).animate({
					marginTop:"140px",
					marginLeft:"140px",
					width:"40px"
				},1600);
				$({deg: 0}).delay(1400).animate({deg: 120}, {
					duration: 1600,
					step: function(d) {
						anim.featured.css({
							transform: 'rotate(' + d + 'deg)'
						});
					}
				});

				setTimeout(function(){
					anim.store.find("h3").css({opacity:0});
					anim.laptopFront.find(".sync").hide();
					anim.laptopFront.find(".folder").show();

					anim.store.fadeIn(300,function(){
						anim.featured.animate({
							marginLeft:"700px",
							opacity:0
						},500,function(){
							anim.laptopTop.animate({opacity:0},400);
							anim.laptopFront.css({
								opacity:0,
								display:"block"
							}).animate({opacity:1},400,function(){
								anim.laptopFront.find(".img1").delay(100).fadeIn("fast").delay(1500).animate({
									top: "63%",
									left: "66%"
								},300);
								anim.laptopFront.find(".img2").delay(200).fadeIn("fast").delay(1450).animate({
									top: "63%",
									left: "66%"
								},350);
								anim.laptopFront.find(".img3").delay(300).fadeIn("fast").delay(1400).animate({
									top: "63%",
									left: "66%"
								},400);
								anim.laptopFront.find(".img4").delay(400).fadeIn("fast").delay(1350).animate({
									top: "60%",
									left: "66%"
								},400,function(){
									$(this).css({
										transform: "rotate(-15deg)"
									});
									anim.laptopFront.find(".sync").fadeIn();
									setTimeout(function(){
										anim.laptopFront.find(".sync").fadeOut(300,function(){
											anim.laptopFront.find(".folder,.thumbnail").delay(100).fadeOut("fast");
											anim.laptopFront.find(".site-background").delay(200).fadeIn(300,function(){
												anim.store.find("h3").delay(200).animate({opacity:1},300);
											});
										});
									},2000);
								});
							});
						});
					});


					setTimeout(function(){
						anim.hide();
					},11000);
				},2900);
			});
		});
	},

	animateMap: function(){
		var self = this;
		//Let's load Snap async to save a few ressources
		$.getScript("/assets/js/snap-svg.min.js", function(){
			self.paper = Snap("#interactiveMap");
			Snap.load("/assets/img/welcome/map.svg", addMap);
			app.mapDots = [];
			app.interactiveMap = $("#map");

			function addMap(d){ 
				var drawing = self.paper.append(d);
				interactiveMap = $("#interactiveMap",self.$el);
				interactiveMap.find("rect").attr("fill","#454545");

				interactiveMap.find("rect").each(function(i){
					var dot = Snap(this);
					var oldColor = dot.attr("fill");
					var x = dot.attr("x");
					var y = dot.attr("y");
					dot.hover(function(){
						this.animate({
							fill: "#3c9dd0",
							width: 12.4,
							height: 12.4,
							x:x-1.5,
							y:y-1.5

						},200);

						setTimeout(function(){
							dot.animate({
								fill: oldColor,
								width: 9.4,
								height: 9.4,
								x:x,
								y:y
							},50);
						},600);
					},function(){
						//
					});
					app.mapDots.push(dot);
				});
				var l = app.mapDots.length;
				console.log("Map has ",l,"dots!");

				setInterval(function(){
					var dot = app.mapDots[Math.floor(Math.random() * l)];
					var c = self.mapColors[Math.floor(Math.random() * 10)];
					var attr = {fill:c};
					var x = dot.attr("x");
					var y = dot.attr("y");
					if(c=="#3C9DD0"){
						attr.width=12.4;
						attr.height=12.4;
						attr.x=x-1.5;
						attr.y=y-1.5;
					}
					dot.animate(attr,100,mina.elastic,function(){
						dot.animate({
							width:9.4,
							height:9.4,
							x:x,
							y:y
						},50);
					});
				},18);
			}
		});
	},
	
	render: function(){
		console.log("Rendering Welcome page");
		var template = app.templates[this.template];
		this.$el.html(template({}));

		this.windowProperties.width = $(window).width();
		this.windowProperties.height = $(window).height();
		$("#page").hide().delay(400).fadeIn();
		this.animateMap();
	},
});