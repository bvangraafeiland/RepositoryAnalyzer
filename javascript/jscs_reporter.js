module.exports = function (collection) {
    var output = [];
    collection.forEach(function (errors) {
        errors.getErrorList().forEach(function (error) {
            output.push({file: error.filename, message: error.message, rule: error.rule});
        });
    });

    console.log(JSON.stringify(output));
};
