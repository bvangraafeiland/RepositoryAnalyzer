module.exports.reporter = function (errors) {
    var output = errors.map(function (error) {
        return {file: error.file, line: error.error.line, column: error.error.character, message: error.error.reason, rule: error.error.code};
    });

    console.log(JSON.stringify(output));
};
