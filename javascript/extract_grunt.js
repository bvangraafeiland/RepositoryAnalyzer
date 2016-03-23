var grunt = require('grunt');
// replace
var gruntFile = require('./Gruntfile.js');

gruntFile(grunt);

console.log(grunt.config.get('eslint'));
