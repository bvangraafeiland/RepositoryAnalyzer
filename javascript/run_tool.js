var buildTool = process.argv[2];
var asatName = process.argv[3];
var projectDir = process.argv[4];

process.chdir(projectDir);

if (buildTool == 'grunt') {
    var grunt = require(projectDir + '/node_modules/grunt');
    var gruntFile = require(projectDir + '/Gruntfile.js');
    gruntFile(grunt);

    grunt.config.set('eslint.options.format', __dirname + '/eslint_formatter');
    grunt.config.set('jscs.options.reporter', __dirname + '/jscs_reporter');
    grunt.config.set('jshint.options.reporter', __dirname + '/jshint_reporter');

    grunt.task.run(asatName).start();
}

else if (buildTool == 'gulp') {
    var gulp = require(projectDir + '/node_modules/gulp');
    var gulpFile = projectDir + '/gulpfile.js';

    var jshint = require(projectDir + '/node_modules/gulp-jshint');

    require(gulpFile);

    var tasks = Object.keys(gulp.tasks)
        .map(taskName => gulp.tasks[taskName])
        .filter(task => task.fn.toString().includes(asatName + '('));
    var targets = tasks.map(task => task.fn.toString().match(/src\(['"]([^\)]+)["']\)/i)[1]);

    // TODO directly run tool using the extracted targets
    console.log(targets);
}
