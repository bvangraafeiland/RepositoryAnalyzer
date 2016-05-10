var buildTool = process.argv[2];
var asatName = process.argv[3];
var projectDir = process.argv[4];

var formatters = {
    eslint: __dirname + '/eslint_formatter',
    jshint: __dirname + '/jshint_reporter',
    jscs: __dirname + '/jscs_reporter'
};

process.chdir(projectDir);

process.env.NODE_PATH = projectDir + '/node_modules';
require('module').Module._initPaths();

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
    require(projectDir + '/gulpfile.js');

    try {
        var asat = require(projectDir + '/node_modules/gulp-' + asatName);

        var tasks = Object.keys(gulp.tasks)
            .map(taskName => gulp.tasks[taskName])
            .filter(task => task.fn.toString().includes(asatName + '('));
        var targets = tasks.map(task => task.fn.toString().match(/src\(([^\)]+)\)/i)[1]);

        targets.forEach(target => {
            var src = eval(target);//.map(tar => projectDir + '/' + tar);
            // src = typeof src == 'string' ? [src] : src;
            // src = src.map(str => '"' + str + '"');

            var reporter = (asatName == 'eslint') ? asat.format(formatters[asatName]) : asat.reporter(formatters[asatName]);
            result = gulp.src(src).pipe(asat()).pipe(reporter);
            console.log(result._flush());
        });

    } catch (e) {
        // Gulp task not defined
        // console.log(e);
        var exec = require('child_process').exec;
        var formatFlag = (asatName == 'eslint') ? ' --format=' : ' --reporter=';
        var ignorePattern = (asatName == 'eslint') ? '--ignore-pattern node_modules' : '--exclude=node_modules';
        var cmd = asatName + formatFlag + formatters[asatName] + '.js . ' + ignorePattern;
        exec(cmd, function(error, stdout, stderr) {
            console.log(stdout);
        });
    }
}
