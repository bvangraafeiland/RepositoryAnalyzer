module.exports = function (results) {
    var warnings = [];
    results.forEach(function (file) {
        file.messages.forEach(function (message) {
            warnings.push({file: file.filePath, line: message.line, column: message.column, message: message.message, rule: message.ruleId});
        });
    });

    console.log(JSON.stringify(warnings));
};
