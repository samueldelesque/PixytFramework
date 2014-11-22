SitesView = Backbone.Marionette.Layout.extend({
	tagName: "div",
	id: "sites",
	template: "shop/sites",
	tab: "create",
	
	initialize: function(tab){
		console.log("Instantiating SitesView");
		if(!app.TLDS){
			$.ajax({url:"/sites/tlds.json",success: function(data){app.TLDS = data;app.events.trigger("tlds:loaded")}});
		}
	},
	
	events: {
		"submit form.domain":"checkavailability",
		"click .trial":"trial",
	},
	
	trial:function(e){
		e.preventDefault();
		$.ajax({url:"/sites/trial.json",success: function(data){
			if(data.error){alert(data.error);return;}
			if(!data.id){alert("An error occured. Please reload the page, or contact an admin.");return;}
			app.router.navigate("/site/"+data.id+"/edit", {trigger: true});
		}});
	},
	
	checkavailability: function(e){
		var that = this;
		e.preventDefault();
		$("form.domain",this.$el).animate({"margin":"0 0 40px 0"},500);
		var domain = $(".domain input[name='url']",this.$el).val();
		if(!app.TLDS){app.events.on("tlds:loaded",function(){that.showalloptions(domain)});}
		else{this.showalloptions(domain);}
	},
	
	payment: function(){
		Stripe.setPublishableKey('pk_test_eF2N4JlS0uMC91vUG61o40IU');
		var stripeResponseHandler = function(status, response) {
		  var $form = $('#payment-form');
		
		  if (response.error) {
			// Show the errors on the form
			$form.find('.payment-errors').text(response.error.message);
			$form.find('button').prop('disabled', false);
		  } else {
			// token contains id, last4, and card type
			var token = response.id;
			// Insert the token into the form so it gets submitted to the server
			$form.append($('<input type="hidden" name="stripeToken" />').val(token));
			// and submit
			$form.get(0).submit();
		  }
		};
		jQuery(function($) {
		  $('#payment-form').submit(function(event) {
			var $form = $(this);
		
			// Disable the submit button to prevent repeated clicks
			$form.find('button').prop('disabled', true);
		
			Stripe.card.createToken($form, stripeResponseHandler);
		
			// Prevent the form from submitting with the default action
			return false;
		  });
		});
	},
	
	showalloptions: function(domain){
		domain = domain.replace(/^(https?|ftp):\/\/(www\.)?/,"");
		var domain_parts = domain.split(".");
		var name = _.first(domain_parts);
		domain_parts = _.without(domain_parts,name);
		var tld = domain_parts.join(".");
		var tlds = _.pluck(app.TLDS,"tld");
		var html = "<div class='header'>Domain<span class='right'>Price per year</span></div>";;
		function resultLine(name,tld,price,c){
			return "<div class='item loading "+c+"' data-domain='"+name+"."+tld+"' data-price='"+price+"'>"+name+"."+tld+" <span class='price right'><img src='/img/loading/loading.gif' alt='loading...'/></span><span class='remove right'>remove</span></div>";
		}
		var exists = _.indexOf(tlds,tld);
		if(exists >= 0){
			console.log("TLD found at",exists);
			html+=resultLine(name,tld,app.TLDS[exists].price,"highlight");
			var addTlds = _.without(app.TLDS,app.TLDS[exists]);
		}
		else{
			console.log("Unknown TLD",tld);
			var addTlds = app.TLDS;
		}
		var i = 0;
		_.each(addTlds,function(v){
			//if(i<2)
			html+=resultLine(name,v.tld,v.price);
			i++;
		});
		
		
		activate($(".options",this.$el).html(html));
		$(".options .loading",this.$el).each(function(){
			var opt = $(this);
			$.ajax({url:"/sites/isavailable.json",data:{domain:$(this).data("domain")},success: function(data){
				$(this).removeClass("loading");
				if(data.error){
					opt.find(".price").html(data.error);
				}
				else if(data.available!==true){
					opt.find(".price").html("Not available!");
				}
				else{
					opt.find(".price").html(price2str(opt.data("price")));
					opt.click(function(e){
						$(this).toggleClass("selected");
						if($(this).hasClass("selected"))$(this).appendTo($(".checkout .items"));
						else $(this).appendTo($(".options"));
						var total;
						var items = "";
						total = 0;
						$(".checkout .items .item").each(function(i,e){
							total += $(e).data("price");
						});
						
						$(".checkout .total").html(price2str(total));
						if($(".checkout .items").size() > 0){
							$(".checkout .next").attr("disabled",false);
						}
						else{
							$(".checkout .next").attr("disabled",true);
						}
					});
				}
			}});
		});
		
		$(document).scroll(function(){
			var offset = $("#domain .hero").outerHeight(true)+92;
			if($(document).scrollTop() > offset-92){
				$(".checkout").css({"position":"fixed","top":"92px"});
			}
			else{
				$(".checkout").css({"position":"absolute","top":offset});
			}
		});
		
		return;
		
		$.ajax({url:app.HOME+"sites/domain.json",dataType:"json",data:{domain:domain},error:function(){
			alert("An error occured, please try to reload the app, or otherwise contact the admins.");
		},success:function(data){
			if(data.error){
				$(".error",that.$el).html(data.error).fadeIn();
			}
			else if(data.renew){
				$(".options",that.$el).html("<ul><li>"+data.renew.url+" <a class='btn' href='/sites/renew/"+data.renew.id+"'>renew</a></li></ul>");
			}
			else{
				var html = "<ul>";
				_.each(data.options,function(v){
					html+="<li>"+v.domain+" ";
					html+=(v.available==1)?"<a class='btn btn-green' href='/sites/order?url="+v.domain+"'>add - "+v.price+"</a>":"<a href='javascript:void(0)' class='btn btn-red'>not available</a>";
					html+="</li>";
				});
				html+="</ul>";
				$(".options",that.$el).html(html);
				activate($(".options",that.$el));
			}
		}});
	},
	
	hosting: function(){
		var that = this;
		$(".plans h2",this.$el).click(function(){$(this).parent().parent().find(".selected").removeClass("selected");$(this).parent().toggleClass("selected");});
	},
	
    render: function(){
        var template = app.templates[this.template];
        this.$el.html(template({tab:this.tab,me:app.ME}));
		if(this.tab=="domain"){
			setTimeout(function(){$(".domain",this.$el).find("input").focus();},500);
		}
		else if(this.tab=="hosting"){
			this.hosting();
		}
		else if(this.tab=="payment"){
			this.payment();
		}
		
		$(this.$el).find("#"+this.tab).fadeIn("fast");
    }
});