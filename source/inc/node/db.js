var utils = require('./utils.js');

var Db = {
	config: null,
	mysql: null,
	connection: null,

	init: function(config, mysql) {
		this.config = config;
		this.mysql = mysql;
		// establish a mysql connection
		this.connection = mysql.createConnection({
			host: config.db.server,
			database: config.db.name,
			user: config.db.user,
			password: config.db.password,
			socketPath: '/var/run/mysqld/mysqld.sock'
		});

		this.connection.connect();

		// doesn't work:
		this.connection.query("SET NAMES 'UTF8'");
	},

	end: function() {
		if(this.connection != null)
			this.connection.end();
	},

	authorize: function(id, auth_key, callback) {
		// Get the user with specified id
		this.connection.query('SELECT settings FROM User WHERE id = '+
			this.connection.escape(id), function(err, rows, fields) {
			// invalid id or other error
			if (err || rows == null || rows.length != 1) {
				if(err) console.log(err);
				return false;
			}
			else {
				// get the settings object if exists
				var settings = rows[0].settings;
				if(settings == null) {
					callback(false);
				}
				else {
					var settings_obj = JSON.parse(settings);
					if(settings_obj == null) {
						callback(false);
					}
					else {
						// do the final comparison
						if(settings_obj.messagesAuth == auth_key){
							callback(true);
						}
						else {
							callback(false);
						}
					}
				}
			}
		});
	},

	conversations: function(data, callback) {
		// socket owner
		var id = this.connection.escape(data.id);
		// var conversations_sql = "SELECT m.uid, m.from, m.content, "+
		// 	"MAX(m.created) as created, u.id, u.firstname, u.lastname "+
		// 	"FROM Message as m, User as u WHERE (m.uid = "+id+" OR "+
		// 	" m.from = "+id+") AND ((m.uid != "+id+" AND u.id = m.uid) OR"+
		// 	" (m.uid = "+id+" AND u.id = m.from)) GROUP BY u.id "+
		// 	"ORDER BY created DESC";
		var conversations_sql = "SELECT sub.*, COUNT(case when sub.unread = 1 and sub.uid = "+id+" then sub.unread end) as ucount FROM (SELECT m.id, m.uid, m.from, m.content, m.created as created, m.unread as unread, u.id as contact_id, u.firstname, u.lastname FROM Message as m, User as u WHERE ((m.uid = "+id+" AND m.from=u.id ) OR (m.from="+id+" AND m.uid=u.id)) AND ((m.uid = ? AND m.archived_receiver = 0) OR (m.from = ? AND m.archived_sender = 0)) ORDER BY m.id DESC) as sub GROUP BY sub.contact_id ORDER BY MAX(sub.created) DESC";
		this.connection.query(conversations_sql, function(err, rows, fields) {
			if(err) {
				return callback(err, []);
			}
			if(rows == null) {
				return callback('no data', []);
			}
			var conversations = [];

			for(var i=0; i<rows.length; i++) {
				conversations.push({
					contact_id: rows[i].contact_id,
					contact_name: utils.stripslashes(rows[i].firstname + ' ' + rows[i].lastname),
					contact_lastmsg: utils.stripslashes(rows[i].content),
					last_update: rows[i].created,
					profile_picture_url: '',
					unread: rows[i].ucount > 0 ? true : false
				});
			}
			return callback(null, conversations);
		});
	},

	messages: function(data, callback) {
		// recipient in the query
		var contact_id = this.connection.escape(data.contact_id);
		// socket owner
		var id = this.connection.escape(data.id);
		var messages_sql;
		// the conditions that are needed to specifiy the conversation
		var conversationConditions = " (via='form' "+
			"AND (uid="+id+" AND `from`="+contact_id+") OR "+
			"(uid="+contact_id+" AND `from`="+id+") AND format='text') AND "+
			"((uid = "+id+" AND archived_receiver = 0) OR "+
			"(`from` = "+id+" AND archived_sender = 0)) ";
		// limit string, if exists
		var limitStr = '';
		if(data.limit != null)
			limitStr = 'LIMIT '+parseInt(data.limit);
		else
			limitStr = 'LIMIT '+this.config.messages_extend_length;
		// retrieve the most recent messages
		if(data.type == 'reload') {
			limitStr = 'LIMIT '+this.config.messages_reload_length;
			messages_sql = "SELECT * FROM Message WHERE "+conversationConditions+
			"ORDER BY created DESC "+limitStr;
		}
		// retrieve messages coming after a specific message id
		else if(data.type == 'after:id' && data.mid != null) {
			var mid = this.connection.escape(data.mid);
			messages_sql = "SELECT * FROM Message WHERE "+conversationConditions+
			"AND id > "+mid+" "+
			"ORDER BY created DESC "+limitStr;
		}
		// retrieve messages coming after a specific timestamp
		else if(data.type == 'after:time' && data.time != null) {
			var time = this.connection.escape(data.time);
			messages_sql = "SELECT * FROM Message WHERE "+conversationConditions+
			"AND creted > "+time+" "+
			"ORDER BY created DESC "+limitStr;
		}
		// retrieve messages coming before a specific message id
		else if(data.type == 'before:id' && data.mid != null) {
			var mid = this.connection.escape(data.mid);
			messages_sql = "SELECT * FROM Message WHERE "+conversationConditions+
			"AND id < "+mid+" "+
			"ORDER BY created DESC "+limitStr;
		}
		// retrieve messages coming before a specific timestamp
		else if(data.type == 'before:time' && data.time != null) {
			var time = this.connection.escape(data.time);
			messages_sql = "SELECT * FROM Message WHERE "+conversationConditions+
			"AND creted < "+time+" "+
			"ORDER BY created DESC "+limitStr;
		}

		this.connection.query(messages_sql, function(err, rows, fields) {
			if(err) {
				return callback(err, []);
			}
			if(rows == null) {
				return callback('no data', []);
			}
			var messages = [];

			for(var i=rows.length-1; i>=0; i--) {
				messages.push({
					mid: rows[i].id,
					contact_id: rows[i].uid,
					message_time: rows[i].created,
					message_text: utils.stripslashes(rows[i].content),
					message_from: rows[i].uid == data.id ? 'contact' : 'user',
					verified: true,
					successful: true,
					unread: false
				});
			}

			return callback(null, messages);
		});

		// Update messages that are sent to our user to make them read. Timing is not important.
		var read_sql = "UPDATE Message SET unread = 0 WHERE uid = "+id+" AND `from` = "+contact_id;
		this.connection.query(read_sql, function(err) {
			if(err) {
				console.error('Could not make messages read: '+err);
			}
		});
	},

	insertMessage: function(data, callback) {
		// recipient in the query
		var contact_id = this.connection.escape(data.contact_id);
		// socket owner
		var id = this.connection.escape(data.id);

		var via = this.connection.escape('form');
		if(data.via != null)
			via = this.connection.escape(data.via);

		var format = this.connection.escape('text');
		if(data.format != null  && (data.format == 'text' /* or something else in the future */) )
			format = this.connection.escape(data.format);

		var content = this.connection.escape(data.content);

		// created date
		var created = this.connection.escape(utils.unixTimestamp());

		var insert_query = "INSERT INTO Message (`uid`,`from`,`content`,`via`,"+
			"`format`,`created`,`unread`) VALUES("+contact_id+","+id+","+
			content+","+via+","+format+","+created+","+"'1')";

		this.connection.query(insert_query, function(err, result) {
			if(err) {
				return callback(err, {});
			}
			if(result == null) {
				return callback('no result', {});
			}
			return callback(null, result);
		});

	}
};

module.exports = Db;