const _ = require('underscore');
const message = _.reduce(
	PHP.data.words,
	function (message, word) { return message ? message+' '+word : word; },
	''
);
PHP.output({ message });