module.exports = function (results) {
    var output = results.map(function (result) {
        return result.messages.map(function (message) {
            return {file: result.filePath, message: message.message, rule: message.ruleId};
        });
    });

    return JSON.stringify(output);
};
