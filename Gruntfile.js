module.exports = function(grunt) {

	// Configuration of the project and plugins
	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		// watch setup
		watch:{

			sass:{
				files : ['assets/scss/*.scss'],
				tasks: ['sass:dev']
			}

		},

		// sass setup
		sass:{

			// dev
			dev:{

				options:{
					sourceMap: true,
					outputStyle: 'nested'
				},
				files: {
					'assets/css/admin.css' : 'assets/scss/admin.scss',
					'assets/css/main.css' : 'assets/scss/main.scss'
				}
			},
			deploy:{

				options:{
					outputStyle: 'compressed'
				},
				files: {
					'assets/css/admin.css' : 'assets/scss/admin.scss',
					'assets/css/main.css' : 'assets/scss/main.scss'
				}
			}
		}
	});

	// Load the plugin that provides the "sass" task.
	grunt.loadNpmTasks('grunt-sass');
	grunt.loadNpmTasks ('grunt-contrib-watch');

	// Our tasks
	grunt.registerTask('dev', ['sass:dev']);
	grunt.registerTask('deploy', ['sass:deploy']);
};