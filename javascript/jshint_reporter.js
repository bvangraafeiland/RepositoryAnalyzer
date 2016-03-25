module.exports.reporter = function (errors) {
    var output = errors.map(function (error) {
        return {file: error.file,  message: error.error.reason, rule: error.error.code};
    });
    
    console.log(JSON.stringify(output));
};
