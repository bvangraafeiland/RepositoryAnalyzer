var buildTool = process.argv[2];
var asatName = process.argv[3];
var projectDir = process.argv[4];

var formatters = {
    eslint: __dirname + '/eslint_formatter',
    jshint: __dirname + '/jshint_reporter',
    jscs: __dirname + '/jscs_reporter'
};

process.chdir(projectDir);

function runDirectly() {
    console.log('Task not defined, running ASAT directly on entire project');
    var exec = require('child_process').exec;
    var formatFlag = (asatName == 'eslint') ? ' --format=' : ' --reporter=';
    var ignorePattern = (asatName == 'eslint') ? '--ignore-pattern node_modules' : '--exclude=node_modules';
    var asatBin = 'node_modules/.bin/' + asatName;
    try {
        require('fs').accessSync(asatBin);
    } catch (e) {
        asatBin = asatName;
    }
    var cmd = asatBin + formatFlag + formatters[asatName] + '.js . ' + ignorePattern;
    exec(cmd, function(error, stdout, stderr) {
        console.log(stdout);
    });
}

if (buildTool == 'grunt') {
    var grunt = require(projectDir + '/node_modules/grunt');
    var gruntFile = require(projectDir + '/Gruntfile.js');
    gruntFile(grunt);

    var configSet = !! grunt.config.get(asatName);

    grunt.config.set('eslint.options.format', formatters.eslint);
    grunt.config.set('jscs.options.reporter', formatters.jscs);
    grunt.config.set('jshint.options.reporter', formatters.jshint);

    // Necessary in case of time-grunt
    grunt.registerTask('exit-when-done', function() {
        process.exit();
    });

    if(configSet) {
        grunt.task.run(asatName).run('exit-when-done').start();
    }
    else {
        runDirectly();
    }
}

else if (buildTool == 'gulp') {
    var gulp = require(projectDir + '/node_modules/gulp');
    require(projectDir + '/gulpfile.js');

    var tasks = Object.keys(gulp.tasks)
        .map(taskName => gulp.tasks[taskName])
        .filter(task => task.fn.toString().includes(asatName + '('));
    var targets = tasks.map(task => task.fn.toString().match(/src\(([^\)]+)\)/i)[1]);

    targets.forEach(target => {
        var src = eval(target);
        var asat = require('gulp-' + asatName);
        var reporter = (asatName == 'eslint') ? asat.format(formatters[asatName]) : asat.reporter(formatters[asatName]);
        gulp.task('run-asat', function() {
            return gulp.src(src).pipe(asat()).pipe(reporter);
        });
        gulp.start('run-asat');
    });

    if (targets.length == 0) {
        runDirectly();
    }
}
