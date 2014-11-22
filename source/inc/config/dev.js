var DevConfig = {
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
	aws : {
		user: "pixyt-store",
		accessKeyId: "AKIAJ62STRWMFR3AIYNQ",
		secretAccessKey: "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",
		region: "us-west-2"
	},
	messages_auth_timeout: 86400,
	host: '0.0.0.0',
	port: 8010,

	ioConfig: function(io, auth) {
		io.set('transports', ['websocket']);
		io.set('log level', 2);                    // reduce logging
		auth(io);
	},

	messages_reload_length: 25,
	messages_extend_length: 15
};

module.exports = DevConfig;