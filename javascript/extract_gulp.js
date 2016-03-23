var gulp = require('gulp');
var gulpFile = proces.argv[2];

require(gulpFile);

console.log(gulp.tasks.eslint.fn.toString());
