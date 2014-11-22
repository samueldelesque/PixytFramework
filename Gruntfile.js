module.exports = function(grunt) {
	var s,server,servers;
	servers = grunt.file.readJSON("server-config.json");
	if(grunt.option("server")) s = grunt.option("server");
	else s = "dev";
	if(!servers[s]){
		grunt.fail.warn("Wrong deployment server!");
		return;
	}
	server = servers[s];

	grunt.initConfig({
	concat: {
		options: {
			separator: "\n",
		},
		libs: {
			src: [
				//libs
				'source/assets/js-libs/jquery.js',
				'source/assets/js-libs/jquery.ui.widget.js',
				'source/assets/js-libs/jquery.*.js',

				'source/assets/js-libs/underscore.js',
				'source/assets/js-libs/backbone.js',
				'source/assets/js-libs/backbone.marionette.min.js',
				'source/assets/js-libs/backbone.historytracker.js',
				'source/assets/js-libs/backbone.marionette.handlebars.js',
				'source/assets/js-libs/handlebars-v1.3.0.js',
				// 'source/assets/js-libs/socket.io.js',
				'source/assets/js-libs/stripe.js',
			],
			dest: 'build/assets/js/libs.js',
		},
		app: {
			src: [
				//app
				'source/assets/js-source/app.js',
				// 'source/assets/js-source/functions.js',

				'source/assets/js-source/models/**/*.js',
				'source/assets/js-source/collections/**/*.js',

				//models, collections and views --> in THEME
				//'source/assets/js-source/views/**/*.js',
			],
			dest: 'build/assets/js/app.js',
		},

		//@TODO: make router dynamic so it can be included in App :)
		router: {
			src: [
				//router
				'source/assets/js-source/router.js',
			],
			dest: 'build/assets/js/router.js',
		},
	},

	uglify: {
		app: {
			options: {
				mangle: false,
				beautify: server.isdev,
			},
			files: [{
				'build/assets/js/app.min.js': ['build/assets/js/app.js'],
				'build/assets/js/router.min.js': ['build/assets/js/router.js']
			}]
		},
		libs: {
		options: {
			mangle: false,
			beautify: server.isdev,
		},
		files: [{
			'build/assets/js/libs.min.js': [
			'build/assets/js/libs.js'
			]
		}]
		}
	},

	clean : {
		pre : {
		options: {
			force: true
		},
		src : [
			"build/**",
			"source/**/.DS_Store",
			"source/**/_notes"
		]
		},

		post : {
			src : [
			//clean OS/software files
			"build/**/.DS_Store",
			"build/**/_notes",

			//remove source files
			"build/assets/js-source",
			"build/assets/js-libs",

			//remove concat files
			"build/assets/js/app.js",
			"build/assets/js/libs.js",
			"build/assets/js/router.js",
			]
		}
	},

	rsync: {
		options: {
			args: ["-avz","--progress"],
		},
		build: {
			options: {
				src: "source/",
				dest: "build/",
				exclude: [".git*","*.scss",".svn",".gitignore","**/unused","node_modules"],
			}
		},
		remote: {
			options: {
				src: "build/",
				dest: server.path,
				host: server.user+"@"+server.host,
				exclude: [".git*","*.scss",".svn",".gitignore","**/unused","node_modules"],
			}
		}
	},

	watch: {
		json: {
			files: ["source/assets/json/**/*.json"],
			tasks: ["sync_json"],
			options: {
				nospawn: true
			}
		},

		jslibs: {
			files: ["source/assets/js-libs/**/*.js"],
			tasks: ["sync_jslibs"],
			options: {
				nospawn: true
			}
		},

		scripts: {
			files: ["source/assets/js-source/**/*.js"],
			tasks: ["sync_scripts"],
			options: {
				nospawn: true
			}
		},

		node_files: {
			files: ["source/inc/**/*.js","messages-server.js"],
			tasks: ["sync_backend"],
			options: {
				nospawn: true
			}
		},

		php: {
			files: ["source/**/*.php"],
			tasks: ["sync_backend"],
			options: {
				nospawn: true
			}
		},
	},

	notify: {
		sync: {
			options: {
				message: 'Full sync complete!'
			}
		},
		json: {
			options: {
				message: 'JSON synced.'
			}
		},
		js: {
			options: {
				message: 'JS synced.'
			}
		},
		backend: {
			options: {
				message: 'Backend files synced.'
			}
		},
	}

	});

	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks("grunt-contrib-uglify");
	grunt.loadNpmTasks("grunt-contrib-clean");
	grunt.loadNpmTasks('grunt-contrib-handlebars');
	grunt.loadNpmTasks("grunt-rsync");
	grunt.loadNpmTasks("grunt-contrib-watch");
	grunt.loadNpmTasks('grunt-notify');

	var sync = ["rsync:build", "concat", "uglify", "rsync:remote","clean:post","notify:sync"];
	var json = ["clean:post","rsync:remote","notify:json"];
	var js = ["concat:app", "concat:router", "uglify:app","clean:post","rsync:remote","notify:js"];
	var jslibs = ["concat:libs", "uglify:libs","clean:post","rsync:remote","notify:js"];
	var backend = ["rsync:build","rsync:remote","notify:backend"];

	grunt.registerTask("sync",sync);
	grunt.registerTask("sync_json",json);
	grunt.registerTask("sync_scripts",js);
	grunt.registerTask("sync_jslibs",jslibs);
	grunt.registerTask("sync_backend",backend);

	//"clean:pre", 
	grunt.registerTask("default", ["sync", "watch"]);
};