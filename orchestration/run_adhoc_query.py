import json
import boto3

# Initialize a boto3 client for Lambda
lambda_client = boto3.client("lambda")

# Specify your Lambda function name
function_name = "your_lambda_function_name"

# Define the payload to pass to the Lambda function
payload = {"key1": "value1", "key2": "value2", "key3": "value3"}

# Convert the payload to JSON string format
payload_json = json.dumps(payload)

# Invoke the Lambda function
response = lambda_client.invoke(
    FunctionName=function_name,
    InvocationType="RequestResponse",  # This waits for the response, use 'Event' for async invocation
    Payload=payload_json,
)

# Read the response from Lambda
response_payload = response["Payload"].read()

# Decode the JSON response
response_json = json.loads(response_payload)

# Print the response from Lambda
print("Response from Lambda:")
print(json.dumps(response_json, indent=2))
