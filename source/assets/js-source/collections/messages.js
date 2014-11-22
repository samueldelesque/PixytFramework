// Collection of contact list models
Messages.ConversationsCollection = Backbone.Collection.extend({
	model: Messages.ConversationModel,
	url: '/account/messages/conversations.json',
	comparator: function(model) {
		// use the last_update int value in the model
		// sort in the descending order
		return -model.get('last_update');
	},
});

// Collection of message models
Messages.MessageCollection = Backbone.Collection.extend({
	model: Messages.MessageModel,
	comparator: function(model) {
		return model.get('message_time');
	},
});

// Collection of suggestion models
Messages.SuggestionCollection = Backbone.Collection.extend({
	model: Messages.SuggestionModel,
	url: '/account/messages/suggest.json'
});
