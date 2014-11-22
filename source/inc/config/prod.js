var ProdConfig = {
	db : {
		name: 'dev',
		user: 'dev',
		password: '97aet3g3iu',
		server: 'localhost'
	},
	db_stat : {
		name: 'dev_stats',
		user: 'dev_stats',
		password: 'aoey937uf3a',
		server: 'localhost'
	},
	messages_auth_timeout: 86400,
	host: '0.0.0.0',
	port: 8010,

	ioConfig: function(io, auth) {
		// Recommended production settings by LearnBoost
		io.enable('browser client minification');  // send minified client
		io.enable('browser client etag');          // apply etag caching logic based on version number
		io.enable('browser client gzip');          // gzip the file
		io.set('log level', 1);                    // reduce logging

		// enable all transports (optional if you want flashsocket support, please note that some hosting
		// providers do not allow you to create servers that listen on a port different than 80 or their
		// default port)
		io.set('transports', [
			'websocket'
		  , 'flashsocket'
		  , 'htmlfile'
		  , 'xhr-polling'
		  , 'jsonp-polling'
		]);
		auth(io);	
	},

	messages_reload_length: 25,
	messages_extend_length: 15
};

module.exports = ProdConfig;