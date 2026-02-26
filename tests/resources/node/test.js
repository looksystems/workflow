const fs = require('fs');
const stdin = fs.readFileSync(0, 'utf-8');

let response;

try {
	const request = JSON.parse(stdin);
	response = {
		message: 'hello '+request.name
	};
} catch (error) {
	response = {
		error
	};
}

console.log(JSON.stringify(response));