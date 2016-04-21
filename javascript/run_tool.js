var buildTool = process.argv[2];
var asatName = process.argv[3];
var projectDir = process.argv[4];

var formatters = {
    eslint: __dirname + '/eslint_formatter',
    jshint: __dirname + '/jshint_reporter',
    jscs: __dirname + '/jscs_reporter'
};

process.chdir(projectDir);

if (buildTool == 'grunt') {
    var grunt = require(projectDir + '/node_modules/grunt');
    var gruntFile = require(projectDir + '/Gruntfile.js');
    gruntFile(grunt);

    grunt.config.set('eslint.options.format', formatters.eslint);
    grunt.config.set('jscs.options.reporter', formatters.jscs);
    grunt.config.set('jshint.options.reporter', formatters.jshint);

    // Necessary in case of time-grunt
    grunt.registerTask('exit-when-done', function() {
        process.exit();
    });

    grunt.task.run(asatName).run('exit-when-done').start();
}

else if (buildTool == 'gulp') {
    var gulp = require(projectDir + '/node_modules/gulp');
    var gulpFile = projectDir + '/gulpfile.js';
    var asat = require(projectDir + '/node_modules/gulp-' + asatName);

    require(gulpFile);

    var tasks = Object.keys(gulp.tasks)
        .map(taskName => gulp.tasks[taskName])
        .filter(task => task.fn.toString().includes(asatName + '('));
    var targets = tasks.map(task => task.fn.toString().match(/src\(['"]([^\)]+)["']\)/i)[1]);

    targets.forEach(target => {
        var reporter =  (asatName == 'eslint') ? asat.format(formatters[asatName]) : asat.reporter(formatters[asatName]);
        gulp.src(target).pipe(asat()).pipe(reporter);
    });
}
