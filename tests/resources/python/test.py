import json
import sys

try:
	data = "".join(sys.stdin)
	request = json.loads(data)
	response = {
		"message": "hello "+request.get('name')
	}

except json.JSONDecodeError:
	response = {
		"error": "Invalid JSON format in body"
	}
	
except Exception as e:
	response = {
		"error": f"Error: {str(e)}"
	}
    
print(json.dumps(response))