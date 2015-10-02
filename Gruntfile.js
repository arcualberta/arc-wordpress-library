module.exports = function(grunt) {


  var jsFiles = [
    'src/js/main.js',
    'src/js/awl.js',
    'src/js/image-grid.js',
    'src/js/event-calendar.js'
  ];

  var cssFiles = [
    'src/css/carousel.css',
    'src/css/image-grid.css',
    'src/css/event-calendar.css'
  ];

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    copy: {
      main: {
        files: [
          {expand: true, cwd: 'src/php/', src: ['*'], dest: 'dist/'},
          {expand: true, cwd: 'vendor/js/', src: ['bootstrap.min.js', 'jquery.min.js'], dest: 'dist/js/'},
          {expand: true, cwd: 'vendor/css/', src: ['bootstrap.min.css'], dest: 'dist/css/'},
          {expand: true, cwd: 'vendor/fonts/', src: ['*'], dest: 'dist/fonts/'}
        ]
      }
    },
    concat: {
      options: {
      },
      js: {
        src: jsFiles,
        dest: 'dist/js/<%= pkg.name %>.js',
      },
      css: {
        src: cssFiles,
        dest: 'dist/css/<%= pkg.name %>.css',
      }
    },
    uglify: {
      dist_min: {
        files: {
          'dist/js/<%= pkg.name %>.min.js': ['dist/js/<%= pkg.name %>.js']
        }
      }
    },
    jshint: {
      all: jsFiles
    },
    clean: ["dist"]
  });

  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-clean');

  grunt.registerTask('default', ['copy', 'concat', 'uglify']);
  grunt.registerTask('hint', ['jshint'])

};