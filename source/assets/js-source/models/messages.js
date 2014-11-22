var Messages = {};

// Model for contact list in Messages
Messages.ConversationModel = Backbone.Model.extend({
	defaults: {
		contact_id: 0,
		contact_name: '',
		contact_lastmsg:'',
		last_update: 0,
		profile_picture_url: '',
		unread: false
	}
});

// Model for message in Messages
Messages.MessageModel = Backbone.Model.extend({
	defaults: {
		mid: 0,
		contact_id: 0,
		message_text: '',
		message_time: 0,
		message_from: '',
		verified: false,
		successful: false,
		hash: '',
		unread: false
	}
});

// Model for suggestion in Messages
Messages.SuggestionModel = Backbone.Model.extend({
	defaults: {
		contact_id: 0,
		contact_name: '',
		profile_picture_url: ''
	}
});
