// socket.io objects
var sio, socket;

var contactsCollection;
var contactsView;

var messagesCollection;
var messagesView;

var suggestionsCollection;
var suggestionsView;

// specific keyCodes
var keyc_up = 38,
	keyc_down = 40,
	keyc_left = 37,
	keyc_right = 39,
	keyc_shift = 16,
	keyc_tab = 9,
	keyc_esc = 27,
	keyc_ctrl = 17,
	keyc_alt = 18,
	keyc_enter = 13,
	keyc_space = 32,
	keyc_backspace = 8,
	keyc_clock = 20,
	keyc_nlock = 144;
var cmd_keys = [91, 92, 93, 224];

// auth data
var authdata = {};
// socket.io connection flag
var connected = false;
var arbitraryDisconnect = false;
var sendTimoutSecs = 20; // message send timeout in seconds

// TODO: move this away
var default_profile_picture = '/img/profile/smallsquare.png';


/*
	TODO
	- more beautiful error dialogs
	- load error dialogs from template

	- support new lines in MessageItemView render
*/

function unixTimestamp(date) {
	return Math.round((date == null ? new Date() : date).getTime() / 1000);
}

function displayConnectionError(title, message) {
	// to be filled
	// incomplete modal window created with jQueryUI.dialog
	var $messagesError = $('#messagesError');
	if($messagesError.length == 0)
	{
		// create new modal dialog
		$('body').append('<div id="messagesError">messagesError</div>');
		$messagesError = $('#messagesError');
	}
	// update the message
	$messagesError.attr('title',title);
	$messagesError.html('<p>'+message+'</p>');
	$messagesError.dialog();
}

function hideConnectionError() {
	$('#messagesError').dialog('close');
}

function lastMessagePrepare(msg) {
	if(msg == null)
		return '';
	else if(msg.length >= 16 && msg.substring(msg.length-4,msg.length-1) != '...')
		return msg.substring(0,16)+'...';
	else 
		return msg;
}

function prepareSocket(sio, socket) {
	arbitraryDisconnect = false;
	// socket events
	socket.on('error', function(reason) {
		connected = false;
		console.error(reason);
		if(reason == 'handshake unauthorized'){
			displayConnectionError('Authentication Error', 'Could not authenticate. <br> <a href="/">Return home</a>');
		}
		else {
			displayConnectionError('Connection Error', 'Could not connect to Pixyt. <br> <a href="/">Return home</a>');
		}
	});

	socket.on('disconnect', function() {
		connected = false;
		console.info('$$$____ disconnect');
		if(!arbitraryDisconnect)
			displayConnectionError('Connection Error', 'Could not connect to Pixyt. <br> <a href="/">Return home</a>');
	});

	sio.on('connect', function(){
		connected = true;
		hideConnectionError();
		console.info('$$$____ connect');
	});

	sio.on('connect_timeout', function(){
		connected = false;
		console.info('$$$____ connect_timeout');
	});

	sio.on('reconnect', function(){
		connected = true;
		hideConnectionError();
		console.info('$$$____ reconnect');
	});

	sio.on('reconnect_error', function(){
		connected = false;
		console.info('$$$____ reconnect_error');
	});

	sio.on('reconnect_failed', function(){
		connected = false;
		console.info('$$$____ reconnect_failed');
	});

	sio.on('server-error', function(data) {
		console.error(data);
	});

	// responses
	sio.on('messages:push', function(data) {
		console.info('messages:push');
		console.log(data);
		if(data == null || data.contact_id == null || data.contact_id == 0 || data.contact_name == null) {
			console.error('Invalid messages:push from server.');
			return;
		}

		if(!contactsView.conversationExists(data.contact_id)) {
			// Conversation does not exist. Build a new conversation model
			var conversationObject = {
				contact_id: data.contact_id,
				contact_name: data.contact_name,
				contact_lastmsg: data.message_text,
				last_update: data.message_time,
				profile_picture_url: '',
				unread: true
			};
			// add the new conversation object to the contacts collection
			contactsCollection.add(new Messages.ConversationModel(conversationObject));
		}
		else {
			// Update last message
			// if this conversation isn't currently active, set it as unread
			var unread = !(app.messages.contact.contact_id == data.contact_id);
			contactsView.updateItem(data.contact_id, data.message_text, data.message_time, unread);
		}
		// sort the contacts list
			contactsCollection.sort();
			// render the contactsView with new changes
			contactsView.render();
		
		// if this conversation is currently active, add the new message!
		if(app.messages.contact.contact_id == data.contact_id) {
			// add and display the message
			messagesCollection.add(new Messages.MessageModel(data));

			// tell the view to stick to the bottom
			messagesView.scrollBottom();
		}

	});
}

function linkify(inputText) {
    var replacedText, replacePattern1, replacePattern2, replacePattern3;

    //URLs starting with http://, https://, or ftp://
    replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
    replacedText = inputText.replace(replacePattern1, '<a href="$1" target="_blank">$1</a>');

    //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
    replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
    replacedText = replacedText.replace(replacePattern2, '$1<a href="http://$2" target="_blank">$2</a>');

    //Change email addresses to mailto:: links.
    replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
    replacedText = replacedText.replace(replacePattern3, '<a href="mailto:$1">$1</a>');

    return replacedText;
}

// Contact List Item
Messages.ContactItemView = Backbone.Marionette.ItemView.extend({
	tagName: 'li',
	className: 'contact-item',
	template: "messages/contact-item",
	ui: {
		name: ".contact-name",
		lastmsg: ".message-last"
	},
	events: {
		'click' : 'openConversation'
	},
	collectionEvents: {
      "all": "render"
    },
	openConversation: function() {
		// // set the current conversation in ContactsView as this one
		// contactsView.setCurrent(this.model.attributes.contact_id);
		// open the conversation
		app.messages.openConversation(this.model.attributes.contact_id,
									this.model.attributes.contact_name);
	},
	render: function() {
		console.log("rendering ContactItemView "+this.model.attributes.contact_name);
		if(this.model.attributes.profile_picture_url == '')
			this.model.attributes.profile_picture_url = default_profile_picture;
        var template = app.templates[this.template];

        // shorten the last message
		this.model.attributes.contact_lastmsg = lastMessagePrepare(this.model.attributes.contact_lastmsg);

		// set as active if this is the current contact
		if(this.model.attributes.contact_id == app.messages.contact.contact_id) {
			this.$el.addClass('active');
			// this is the active one. not unread anymore
			this.model.attributes.unread = false;
		}
		else {
			this.$el.removeClass('active');
		}

		// unread status
		if(this.model.attributes.unread == true) {
			this.$el.addClass('unread');
		}
		else {
			this.$el.removeClass('unread');
		}

		this.$el.html(template(this.model.attributes));
	}
});
// Contact List View
Messages.ContactsView = Backbone.Marionette.CollectionView.extend({
	tagName: 'ul',
	className : 'conversations',
	itemView : Messages.ContactItemView,
	count: 0,

	initialize: function() {
		console.log("Instantiating ConversationsView");
		_.bindAll(this, 'render');

		// Load conversations for the first time
		// if there is a particular contact id (conversation) requested in the URL,
		// it will open that conversation
		this.refreshConversations(app.messages.contactToOpen);
	},
	replaceConversations: function(data) {
		this.collection.reset(data.conversations);
		this.count = data.conversations.length;
	},
	refreshConversations: function(contactToOpen) {
		// if socket.io connection has not been established yet, try an xmlhttprequest
		var view = this;
		if(!connected) {
			console.log('fetch contacts via xhr');
			this.collection.fetch({
				success: function(event, data) {
					if(data.conversations != null) {
						// replace the conversations list view with new arrived data
						view.replaceConversations(data);
						// it will be automatically rendered

						// if there is a conversation defined to be opened, open it
						if(contactToOpen != null && contactToOpen != 0) {
							// find the user name
							var contactNameToOpen = view.findContactNameByContactId(contactToOpen);
							if(contactNameToOpen != null) {
								// valid contact. open the conversation
								app.messages.openConversation(contactToOpen,
										contactNameToOpen);
							}
							else {
								// the contact might not always exist in the list
								// navigate to home
								app.router.navigate(app.messages.route);
							}
						}
					}
				},
				error: function(e) {
					console.error(e);
				}
			});
		}
		// otherwise, use socket.io
		else {
			console.log('fetch contacts via socket');
			sio.emit('conversations', {}, function(data) {
				if(data.conversations != null) {
					// replace the conversations list view with new arrived data
					view.replaceConversations(data);
					// it will be automatically rendered

					// TODO: Fix MySQL UTF-8
				}
				else {
					console.error('Could not refresh conversations.');
					if(data.error != null) {
						console.error(data.error);
					}
				}
			});
		}
	},
	conversationExists: function(contact_id) {
		// traverse in the collection and look for the contact id
		for(var i=0;i<contactsCollection.length;i++) {
			if(contactsCollection.at(i).attributes.contact_id == contact_id)
				return true;
		}
		return false;
	},
	updateItem: function(contact_id, message, time, unread) {
		// traverse in the collection and update the last message when the right conversation is found
		for(var i=0; i<contactsCollection.length; i++) {
			if(contactsCollection.at(i).attributes.contact_id == contact_id) {
				if(message != null) {
					contactsCollection.at(i).attributes.contact_lastmsg = message;
				}
				if(time != null) {
					contactsCollection.at(i).attributes.last_update = time;
				}
				if(unread != null) {
					contactsCollection.at(i).attributes.unread = unread;
				}
				return true;
			}
		}
		return false;
	},
	findContactNameByContactId: function(contact_id) {
    	for(var i=0; i<contactsCollection.length; i++) {
			if(contactsCollection.at(i).attributes.contact_id == contact_id) {
				return contactsCollection.at(i).attributes.contact_name;
			}
		}
		return null;
    }
});

// A message item
Messages.MessageItemView = Backbone.Marionette.ItemView.extend({
	tagName: 'li',
	className: 'conversation-message message-left',
	template: "messages/message-item",
	ui: {
		content: ".message-content",
		meta: ".message-meta"
	},
	events: {
		'click' : 'openMessage'
	},
	collectionEvents : {
      "all": "render"
    },
	openMessage : function() {
		console.info("open message "+this.model.attributes.mid);
	},
	render : function() {
		// 
		if(this.model.attributes != null && this.model.attributes.message_from == 'user') {
			if(this.model.attributes.verified == true && this.model.attributes.successful == false) {
				this.$el.removeClass('message-left').addClass('message-right').addClass('fail');
			}
			else {
				this.$el.removeClass('message-left').addClass('message-right');
			}
		}
		var messageDate = new Date(parseInt(this.model.attributes.message_time)*1000);
		this.model.attributes.message_time_relative = messageDate.getDate()+'/'+(messageDate.getMonth()+1)+'/'+messageDate.getFullYear()+' '+messageDate.getHours()+':'+messageDate.getMinutes();
		// this.model.attributes.message_text = linkify(this.model.attributes.message_text);
        var template = app.templates[this.template];
		this.$el.html(template(this.model.attributes));
		this.$el.html(linkify(this.$el.html()));
	}
});

// Conversation panel view
Messages.MessagesView = Backbone.Marionette.CollectionView.extend({
	tagName: 'ul',
	className: 'messages-list',
	itemView: Messages.MessageItemView,
	oldestId: 0,
	loadingPrevious: false,
	loadingLock: true,
	loadedAll: false,

	initialize: function(){
		console.log("Instantiating MessageView");
		// _.bindAll(this, 'render');
	},
	display: function (id) {
		this.loadingPrevious = false;
		this.loadedAll = false;
		app.messages.showSpinner();
		var view = this;
		// send a reload request to the server
		sio.emit('messages', {contact_id: id, type: 'reload'}, function(data) {
			app.messages.hideSpinner();
			if(data.messages != null) {
				// for the reload action, all downloaded messages has to belong to the active conversation
				if(data.contact_id != app.messages.contact.contact_id) {
					console.log('invalid response (unmatching id) from server');
					return;
				}

				// replace messages with new ones
				view.collection.reset(data.messages);
				// tell the view to stick to the bottom
				view.scrollBottom();

				view.loadingLock = false;
				view.oldestId = data.messages[0].mid;

				// if the user scrolled to top, load older messages
				var prevScrollPos = $(window).scrollTop();
				$(window).scroll(function() {
					var top = $(window).scrollTop();
					// if(top < 200)
					// 	console.log(top+' '+prevScrollPos);
					// only consider from bottom to up scrolls
					if(top > prevScrollPos && top < 50) {
						view.displayPrevious(id);
					}
					prevScrollPos = top;
				});
			}
			else {
				console.error('Could not reload messages.');
				if(data.error != null) {
					console.log(data.error);
				}
			}
		});
	},

	scrollBottom: function() {
		// scroll to the bottom to display the most latest messages
		if(messagesView.$el.children() != null && messagesView.$el.children() != null)
			$('body').scrollTop(messagesView.$el.children().last().offset().top-messagesView.$el.offset().top);
	},

	displayPrevious: function(id) {
		// console.log('dp '+this.loadingPrevious+' '+this.loadingLock+' '+this.loadedAll);
		if(this.loadingPrevious || this.loadingLock || this.loadedAll) {
			return;
		}
		console.log('displaying prev');
		var view = this;
		this.loadingLock = true;
		this.loadingPrevious = true;
		// do not allow consecutive loads
		setTimeout(function() {
			console.log('falsify '+view.loadingLock);
			view.loadingLock = false;
		}, 4000);

		console.log('displayPrevious');
		app.messages.showSpinner();

		// $('body').scrollTop($('body').scrollTop()+50);

		sio.emit('messages', {contact_id: id, type: 'before:id', mid:view.oldestId}, function(data) {
			if(data.messages != null && data.messages.length > 0) {
				// for the reload action, all downloaded messages has to belong to the active conversation
				if(data.contact_id != app.messages.contact.contact_id) {
					console.log('invalid response (unmatching id) from server');
					return;
				}
				console.log('add more '+data.messages[0].mid);

				// remember the scroll position
				var distanceFromBottom;
				if(messagesView.$el.children() != null && messagesView.$el.children() != null)
					distanceFromBottom = messagesView.$el.children().last().offset().top - messagesView.$el.children().first().offset().top;
				else
					distanceFromBottom = 0;
				console.log('distanceFromBottom '+distanceFromBottom);
				// $('body').scrollTop($('body').scrollTop()+50);
				view.collection.add(data.messages, {at: 0});

				 // retain position
				 // setTimeout(function() {
					// $('body').scrollTop(messagesView.$el.children().last().offset().top - messagesView.$el.offset().top - distanceFromBottom);
					// console.log(distanceFromBottom);
					// console.log(messagesView.$el.children().last().offset().top - messagesView.$el.offset().top - distanceFromBottom);
				 // }, 100);
				view.oldestId = data.messages[0].mid;
			}
			else if(data.messages.length > 0) {
				view.loadedAll = true;
			}
			else {
				console.error('Could not load previous messages.');
				if(data.error != null) {
					console.log(data.error);
				}
			}
			
			// just wait till scroll is set
			setTimeout(function() {
				view.loadingPrevious = false;
			}, 500);
			app.messages.hideSpinner();
		});
	},
	appendHtml: function(collectionView, itemView, index){
		var childrenContainer = collectionView.itemViewContainer ? collectionView.$(collectionView.itemViewContainer) : collectionView.$el;
		var children = childrenContainer.children();
		if (children.size() <= index) {
			childrenContainer.append(itemView.el);
		} else {
			// $('body').scrollTop(parseInt($('body').scrollTop()) + itemView.$el.height() + parseInt(itemView.$el.css('margin-top').replace("px", ""))+parseInt(itemView.$el.css('margin-bottom').replace("px", "")));
			children.eq(index).before(itemView.el);
			// console.log('#0 '+$('body').scrollTop());
			// console.log('#1 '+itemView.$el.height());
			// console.log('#2 '+itemView.$el.css('margin-top'));
			// console.log('#3 '+itemView.$el.css('margin-bottom'));
			// $('body').scrollTop($('body').scrollTop() + itemView.$el.height() + 20);
			// console.error(($('body').scrollTop() + itemView.$el.height() + 20));
			// console.info('& ' + parseInt($('body').scrollTop()) + itemView.$el.height() + parseInt(itemView.$el.css('margin-top').replace("px", ""))+parseInt(itemView.$el.css('margin-bottom').replace("px", "")));
			// console.log('- '+itemView.$el.height());
		}
	}

});

// A Suggestion item
Messages.SuggestionItem = Backbone.Marionette.ItemView.extend({
	tagName: 'li',
	className: 'contact-suggestion-item cf',
	template: 'messages/suggestion-item',

	render : function() {
		if(this.model.attributes == null || this.model.attributes.length == 0)
			return;
		if(this.model.attributes.profile_picture_url == '')
			this.model.attributes.profile_picture_url = default_profile_picture;

		var template = app.templates[this.template];
		this.$el.html(template(this.model.attributes));
	}
});

// Suggestion view
Messages.SuggestionsView = Backbone.Marionette.CollectionView.extend({
	tagName: 'ul',
	className: 'contact-suggestion-list cf',
	itemView: Messages.SuggestionItem,
	xhr: null,
	count: 0,
	current: 0,

	initialize: function() {
		console.log("Initializing SuggestionsView");
	},
	display: function (callback, keyword) {
		console.log('keyword '+keyword);
		if(keyword != null && keyword != "")
		{
			if(this.xhr != null) {
				console.log('abort while '+keyword+'@');
				this.xhr.abort();
			}
			var view = this;
			this.xhr = $.post(this.collection.url, {keyword: keyword}, function(msg) {
				// prevent late responses 
				if($('#messages-content-new-recipient').val() == '')
				{
					console.log('late empty');
					view.xhr.abort();
					// hide the suggestion box
					view.reset();
					callback();
					return;
				}
				
				console.log('done '+keyword+'#'+$('#messages-content-new-recipient').val());
				console.log(msg);

				// update suggestions count
				view.count = msg.length;
				// select the first suggestion as default
				view.current = 0;
				console.log('set '+keyword+'*'+view.count);
				if(view.count > 0)
					view.collection.reset(msg);
				else
					view.collection.reset();
				view.render();
				view.highlightCurrent();

				callback(keyword);
			});
		}
		else
		{
			this.reset();
			callback();
		}
	},
	reset: function() {
		suggestionsCollection.reset();
		this.count = 0;
		this.current = 0;
		this.render();
	},
	scrollDown: function() {
		if(this.current < this.count - 1) {
			this.current++;
			return true;
		}
		return false;
	},
	scrollUp: function() {
		if(this.current > 0) {
			this.current--;
			return true;
		}
		return false;
	},
	highlightCurrent: function() {
		// only try to highlight if suggestion count > 0
		if(this.count == 0 || this.children == null)
			return;
		console.log('highlight'+this.current+' '+this.count);

		var that = this;

		var doHighlight = function() {
			// highlight only the current item
			for(var i=0;i<that.count;i++) {
				if(that.children.findByIndex(i) == null) {
					continue;
				}
				if(i==that.current)
					that.children.findByIndex(i).$el.addClass('active');
				else
					that.children.findByIndex(i).$el.removeClass('active');
			}
		};

		if(this.children.findByIndex(0) == null) {
			// sometimes Marionette is so slow that children views do not exist yet
			// wait for a while and try highlighting
			setTimeout(doHighlight, 200);
		}
		else {
			// do it right away
			doHighlight();
		}
	},
	onCollectionRendered: function() {
		console.log('onCollectionRendered');
		this.highlightCurrent();
	}
});

// Main View
MessagesView = Backbone.Marionette.Layout.extend({
	tagName: "div",
	id: "messages",
	className:"",
	template: "messages/layout",
	route: "account/messages",
	contactToOpen: 0,
	quickSend: true,

	contact: {
		contact_id: 0,
		contact_name: ''
	},
	
	initialize: function(){
		console.log("Instantiating MessagesView.");
		_.bindAll(this, 'render');
		hideConnectionError();

		// disconnect from socket.io server whenever user moves away from the messages page
		this.on('close', function() {
			console.info('MessagesView close');
			arbitraryDisconnect = true;
			sio.disconnect();
		});
	},

	regions: {
		conversations: '#conversations-list',
		messages: '#conversation',
		newMessage: '#messages-content-new',
		suggestions: '#messages-suggestions'
	},
		
	events:{
		'click #new-conversation-button': 'newConversation',
		'keyup #messages-content-new-recipient': 'suggest',
		'keydown #messages-content-new-recipient': 'preSuggest',
		'focusout #messages-content-new-recipient': 'focusOutRecipient',
		'focusin #messages-content-new-recipient': 'focusInRecipient',
		'click #conversation-submit': 'sendMessage',
		'click #messages-content-remove-recipient': 'clearRecipient',
		'click #messages-title': 'refreshConversations',
		'keydown #conversation-reply-input': 'responseChange',
		'click #conversation-reply-preference-enter': 'quickSendChange'
	},
	
    render: function() {
        var template = app.templates[this.template];
		this.$el.html(template(this.model));
		this.hideSpinner();
		console.log("rendering MessagesView");

		var view = this;

		// check for authentication
		$.get('/account/messages/authorize.json', function(data) {

			if(data == null || data.auth_key == null) {
				console.error('Authentication error');
				// authentication error
				displayConnectionError('Authentication Error', 'Could not authenticate. <br> <a href="/">Return home</a>');
				return;
			}
			console.log('Successfully authenticated.');
			// copy related data
			authdata.id = data.id;
			authdata.auth_key = data.auth_key;
			// TODO: extract port variable
			console.log('connect '+app.HOME.substring(0,app.HOME.length-1)+':8010');
			// connect to messages server
			sio = io.connect(app.HOME.substring(0,app.HOME.length-1)+':8010', {'force new connection':true, query:'id='+data.id+'&auth_key='+data.auth_key});
			socket = sio.socket;
			// prepare the socket
			prepareSocket(sio, socket);

			// prepare and show contacts region
			contactsCollection = new Messages.ConversationsCollection();
			contactsView = new Messages.ContactsView({collection: contactsCollection});

			// show the conversations (aka contacts)
			if(view.conversations != null && contactsView != null)
				view.conversations.show(contactsView);

			// prepare messages region
			messagesCollection = new Messages.MessageCollection();
			messagesView = new Messages.MessagesView({model: Messages.MessageModel, collection: messagesCollection});

			// prepare and show suggestions region
			suggestionsCollection = new Messages.SuggestionCollection();
			suggestionsView = new Messages.SuggestionsView({collection: suggestionsCollection});

			// show empty suggestions (invisible)
			if(view.suggestions != null && suggestionsView != null)
				view.suggestions.show(suggestionsView);

			if(view.quickSend) {
				$('#conversation-reply-preference-enter').prop('checked', true);
			}
			else {
				$('#conversation-reply-preference-enter').prop('checked', false);
			}

		});

    },
    newConversation: function(event) {
		event.preventDefault();
		// hide the conversation container
		$('#conversation').hide();
		// reset current contact
		this.resetContact();
		// reset suggestions box
		this.suggestions.reset();
		// revert the recipient input to its initial state
		$('#messages-content-new-recipient').val('').prop('disabled', false);
		$('#messages-content-remove-recipient').hide();
		// show the new message block
		$('#messages-content-new').show();
		// change the section title
		$('.conversation-meta h2').text('New Conversation');
		// remove the active state of all conversation list items
		contactsView.render();
    },
    openConversation: function(contact_id, contact_name, callback) {
		console.log("open conversation "+contact_name+" ");

		this.setContact(contact_id,contact_name);

		// render the contactsView again
		contactsView.render();
		
		app.messages.messages.show(messagesView);
		messagesView.display(contact_id);
		
		$('#conversation').show();
		$('#messages-content-new').hide();
		$('.conversation-meta h2').text(contact_name);

		// change the url
		app.router.navigate(app.messages.route+'/'+contact_id);

		// TODO: emit new message to server, telling that the conversation is all read now
    },
    // prevent the cursor from moving when user presses on up/down arrow buttonz
    preSuggest: function(event) {
		var keyCode = event.keyCode;
		if(keyCode == keyc_down || keyCode == keyc_up) {
			event.preventDefault();
		}
    },
    suggest: function(event) {
		// do not suggest anything if there is a chosen contact
		if(this.contactSeemsValid()) {
			this.suggestions.reset();
			return;
		}

		var keyword = $('#messages-content-new-recipient').val();
		var keyCode = event.keyCode;

		if(keyword == '') {
			// hide the suggestion box
			this.suggestions.reset();
			return;
		}

		// treat up/down keys different
		if(keyCode == keyc_down) {
			event.preventDefault();
			if(suggestionsView.scrollDown())
				suggestionsView.highlightCurrent();
			return;
		}
		if(keyCode == keyc_up) {
			event.preventDefault();
			if(suggestionsView.scrollUp())
				suggestionsView.highlightCurrent();
			return;
		}

		if(keyCode == keyc_left || keyCode == keyc_right || keyCode == keyc_nlock
			|| keyCode == keyc_clock || keyCode == keyc_alt || keyCode == keyc_ctrl
			|| $.inArray(keyCode, cmd_keys)!==-1) {
			// do nothing for these keys
			console.log('suggest not '+keyCode+' '+$.inArray(keyCode, cmd_keys));
			return;
		}

		// enter means select current active
		if(keyCode == keyc_enter || keyCode == keyc_tab) {
			console.log('suggest {enter}');
			this.acceptSuggestion();
			return;
		}

		// esc means clear the input and hide the suggestion
		if(keyCode == keyc_esc) {
			console.log('suggest {esc}');
			this.clearRecipient();
			return;
		}

		// update suggestions
		app.messages.suggestions.show(suggestionsView);
		suggestionsView.display(function (keyword) {
			console.log("end update suggestions "+keyword);
			// suggestionsView.render();
		}, keyword);
    },
    focusOutRecipient: function(event) {
		this.suggestions.reset();
    },
    focusInRecipient: function(event) {
		// update suggestions
		app.messages.suggestions.show(suggestionsView);
		suggestionsView.display(function (keyword) {
		}, $('#messages-content-new-recipient').val());
    },
    clearRecipient: function() {
		// revert the recipient input to its initial state
		$('#messages-content-new-recipient').val('').prop('disabled', false);
		$('#messages-content-remove-recipient').hide();
		// reset current contact
		this.resetContact();
		// reset suggestions
		this.suggestions.reset();
    },
    acceptSuggestion: function() {
		// if there is still at least one suggession
		if(suggestionsCollection.length > 0) {
			// choose the current suggestion as the contact
			var chosenContact = suggestionsCollection.at(suggestionsView.current);
			// check if a conversation is already open
			if(contactsView.conversationExists(chosenContact.attributes.contact_id)) {
				console.log('contact '+chosenContact.attributes.contact_name+' exists');

				this.openConversation(chosenContact.attributes.contact_id,
									chosenContact.attributes.contact_name);
			}
			else {
				// Show the best suggestion as the chosen contact
				$('#messages-content-new-recipient').val(chosenContact.attributes.contact_name);
				$('#messages-content-new-recipient').prop('disabled', true);
				$('#messages-content-remove-recipient').show();
				this.suggestions.reset();
				// focus on the message box
				$('#conversation-reply-input').focus();

				// set the suggested contact
				this.setContact(chosenContact.attributes.contact_id, chosenContact.attributes.contact_name);
			}
		}
		else {
			// no recipient found. do not proceed
			console.log('no results found');
		}
		
    },
    // does the actual send job
	sendMessage: function(event, msg) {
		if(this.contactSeemsValid()) {
			if(msg == null) {
				msg = $('#conversation-reply-input').val().trim();
			}
			// Do not allow empty messages
			if(msg == null || msg == '') {
				return;
			}

			// message creation time
			var msgTime = unixTimestamp();
			// create a unique hash so that we can access to this message item later
			// TODO: create a better hash, preferably with a digest algorithm
			var msgHash = msgTime+'@'+this.contact.contact_id+'@'+msg.substring(0,10);
			console.log(msgHash);

			// display an error if a response cannot be received in 
			var sendMessageTimeout = setTimeout(function() {
				console.error('Could not send message '+msg);
				// if message item is already added into the view, display a warning
				for(var i=0; i<messagesCollection.length; i++) {
					// use the hash to find the correct model
					if(messagesCollection.at(i).attributes.hash == msgHash) {
						messagesCollection.at(i).attributes.verified = true;
						messagesCollection.at(i).attributes.successful = false;
						// render this MessageItemView again
						messagesView.children.findByIndex(i).render();
						// done
						return;
					}
				}

			}, sendTimoutSecs * 1000);
			
			// send a new `send` message to socket
			sio.emit('send',{
				contact_id: this.contact.contact_id, 
				content: msg,
				hash: msgHash,
				format: 'text', 
				sender_name: app.ME.firstname+' '+
					app.ME.lastname
			}, function(result) {
				// 
				if(sendMessageTimeout != null) {
					clearTimeout(sendMessageTimeout);
				}
				// verify that the message has been sent
				// give the new message an id
				for(var i=0; i<messagesCollection.length; i++) {
					// use the hash to find the correct model
					if(messagesCollection.at(i).attributes.hash == result.hash) {
						messagesCollection.at(i).attributes.mid = result.mid;
						messagesCollection.at(i).attributes.verified = true;
						messagesCollection.at(i).attributes.successful = true;
						// render this MessageItemView again
						messagesView.children.findByIndex(i).render();
						// done
						return;
					}
				}
			});
			var messageObj = {
				mid: 0,
				contact_id: this.contact.contact_id,
				message_time: msgTime,
				message_text: msg,
				message_from: 'user',
				verified: false,
				successful: false,
				hash: msgHash
			};

			// TODO: fetch user profile picture via ajax
			var conversationObject = {
				contact_id: this.contact.contact_id,
				contact_name: this.contact.contact_name,
				contact_lastmsg: msg,
				last_update: msgTime,
				profile_picture_url: ''
			};

			// empty the text box
			$('#conversation-reply-input').val('');
			
			// the conversation might not be open yet
			if(!contactsView.conversationExists(this.contact.contact_id)) {
				console.log('convo DNE. creating it');
				// add the new conversation object to the contacts collection
				contactsCollection.add(new Messages.ConversationModel(conversationObject));
				// open the new added conversation
				app.messages.openConversation(this.contact.contact_id,
									this.contact.contact_name);
			}
			else {
				// update the last message of the open conversation
				contactsView.updateItem(this.contact.contact_id, msg, msgTime);
			}
			// add and display the message
			messagesCollection.add(new Messages.MessageModel(messageObj));

			// sort the contacts list
			contactsCollection.sort();

			// render the contactsView with new changes
			contactsView.render();

			// tell the view to stick to the bottom
			messagesView.scrollBottom();
		}
		else {
			console.log('invalid contact. cannot send');
		}
    },
    responseChange: function(event) {
    	// if quickSend is enabled and shift key is not held, send the message
    	// when the enter key is pressed
    	if(event.keyCode == keyc_enter && event.shiftKey == false && this.quickSend == true) {
    		event.preventDefault();
    		this.sendMessage();
    	}
    },
    quickSendChange: function(event) {
    	// toggle quickSend
    	this.quickSend = !this.quickSend;
    	// TODO: hide Send button if quickSend is active
    },
    // stores the latest contact info for easy access
    setContact: function(contact_id, contact_name) {
		this.contact.contact_id = contact_id;
		this.contact.contact_name = contact_name;
    },
    // resets the latest contact to the initial state
    resetContact: function() {
		this.setContact(0, '');
    },
	contactSeemsValid: function() {
		if(this.contact.contact_id != 0 && this.contact.contact_name != '')
			return true;
		return false;
    },
    refreshConversations: function() {
    	contactsView.refreshConversations();
    },
    showSpinner: function() {
    	$('.conversation-spinner').show();
    },
    hideSpinner: function() {
    	$('.conversation-spinner').hide();
    }
});