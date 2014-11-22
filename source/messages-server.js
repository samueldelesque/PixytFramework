/** Pixyt Messages server main executable file
  *
  */

// environment variable is NODE_ENV
// to set it: $ NODE_ENV=production

// TODO: verify MySQL charset
// fix UTF-8 problems

// get the right config module according to the right environment
var config;
if(process.env.NODE_ENV == 'production')
	config = require('./inc/config/prod.js');
else
	config = require('./inc/config/dev.js');

var mode_production = (process.env.NODE_ENV == 'production');

// library modules
var io = require('socket.io').listen(config.port),
	mysql = require('mysql'),
	validator = require('validator'),
	db = require('./inc/node/db.js'),
	utils = require('./inc/node/utils.js');
// initialize database
db.init(config, mysql);

// store ids of connected clients
var connectedClients = [];

// configure socket.io according to the environment
config.ioConfig(io, function(io) {
	// authentication function
	io.set('authorization', function (handshakeData, callback) {
		db.authorize(handshakeData.query.id, handshakeData.query.auth_key, function(response) {
			if(response == true) {
				// successful authorization
				callback(null, true);
			}
			else {
				// unsuccessful authorization
				callback(null, false);
			}
		});
	});
});

io.sockets.on('connection', function (socket) {
	console.log('new connection from {'+socket.handshake.query.id+'} '+socket.handshake.address.address);

	// manage disconnection
	socket.on('disconnect', function() {
		console.log('disconnect '+ socket.handshake.query.id);
	});

	// requests from client
	socket.on('conversations', function(data, callback) {
		// get conversations from the database
		db.conversations(socket.handshake.query, function(err, response) {
			if(err == null) {
				// successful. send conversations list
				// socket.emit('conversations', {conversations: response});
				return callback({conversations: response});
			}
			else{
				if(!mode_production) {
					console.log(err);
				}
				// error. send error
				// socket.emit('server-error', err);
				return callback({error: err});
			}
		});
	});

	socket.on('messages', function(data, callback) {
		// get a collection of messages from the database
		// check if recipient id was sent
		if(data.contact_id == null || data.type == null) {
			// socket.emit('bad-request', {});
			return callback({error: 'bad request'});
		}
		// socket owner
		data.id = socket.handshake.query.id;
		db.messages(data, function(err, messages) {
			if(err == null) {
				// successful
				// send messages query response
				// add query response to received data
				data.messages = messages;
				// send the collected data
				// socket.emit('messages', data);
				return callback(data);
			}
			else {
				if(!mode_production) {
					console.log(err);
				}
				// socket.emit('server-error', err);
				return callback({error: err});
			}
		});
	});

	socket.on('send', function(data, callback) {
		// a new message arrived to be sent to the right place
		// check if recipient id was sent
		if(data.contact_id == null || data.content == null || data.content == '') {
			// socket.emit('bad-request', {});
			return callback({error: 'bad request'});
		}
		// socket owner
		data.id = parseInt(socket.handshake.query.id);

		// sanitize message content
		data.content = data.content.trim();
		if(data.content.length == 0) {
			// cannot send empty message
			return callback({error: 'empty message'});
		}
		data.content = validator.escape(data.content);

		db.insertMessage(data, function(err, response) {
			if(err == null) {
				var responseData = { id: data.id, contact_id: data.contact_id, hash: data.hash, mid: response.insertId};
				console.log('send the sender and recipient an update');
				

				// find out if the recipient is connected (online)
				// if they are, send them the message as well
				var clients = io.sockets.clients();
				var client_id;
				var client_sess = [];
				if(clients[0] == null || clients[0].manager == null || clients[0].manager.handshaken == null) {
					// weird error
					return callback({error: 'server error'});
				}
				
				// find all recipient sessions
				for(var sessId in clients[0].manager.handshaken) {
					if(clients[0].manager.handshaken[sessId].query.id == data.contact_id) {
						client_id = clients[0].manager.handshaken[sessId].query.id;
						client_sess.push(sessId);
					}
				}

				if(client_id != null) {
					var pushObj = {
						mid: response.insertId,
						// owner this time
						contact_name: data.sender_name,
						contact_id: data.id,
						uid: client_id,
						message_time: utils.unixTimestamp(),
						message_text: data.content,
						message_from: 'contact',
						verified: true,
						successful: true
					};

					// the user might have multiple tabs open. push to each one of them
					for(var i in client_sess) {
						console.log('emitting to '+client_id + ' - '+client_sess[i]);
						io.sockets.socket(client_sess[i]).emit('messages:push', pushObj);
					}
				}
				return callback(responseData);
			}
			else {
				err.hash = data.hash;
				if(!mode_production) {
					console.log(err);
				}
				// socket.emit('server-error', err);
				return callback({error: err});
			}
		});
	});

	socket.on('read', function(data, callback) {
		if(data.mid == null || parseInt(data.mid) == 0) {
			return callback({error: 'bad request'});
		}
		// TODO: fill here
	})
});

console.log('Starting Pixyt Messages server in '+process.env.NODE_ENV+' mode on port '+config.port+'.');
