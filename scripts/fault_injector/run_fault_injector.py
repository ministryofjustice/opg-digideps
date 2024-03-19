import boto3

# Initialize AWS SDK client for Fault Injection Simulator
fis_client = boto3.client("fis")

# Define experiment parameters
experiment_template = {
    "actions": [
        {
            "actionId": "action-id",  # Specify the ID of the action to perform
            "description": "action-description",  # Description of the action
            # Additional parameters for the action...
        }
    ],
    "targets": [
        {
            "resourceType": "resource-type",  # Specify the type of resource to target
            "resourceArns": ["resource-arn"],  # Specify ARNs of the resources to target
        }
    ],
    "roleArn": "role-arn",  # Specify the ARN of the IAM role to use for the experiment
}

# Send API call to start the experiment
response = fis_client.start_experiment(
    experimentTemplateId="experiment-template-id",  # Specify the ID of the experiment template
    tags={"key": "value"},  # Optional tags for the experiment
    roleArn="role-arn",  # ARN of the IAM role to use for the experiment
    clientToken="client-token",  # Unique identifier for the request (optional)
    experimentName="experiment-name",  # Name of the experiment (optional)
    description="experiment-description",  # Description of the experiment (optional)
    actions=[  # List of actions to execute (optional)
        {
            "actionId": "action-id",  # Specify the ID of the action to perform
            "description": "action-description",  # Description of the action
            # Additional parameters for the action...
        }
    ],
    targets=[  # List of targets for the experiment (optional)
        {
            "resourceType": "resource-type",  # Specify the type of resource to target
            "resourceArns": ["resource-arn"],  # Specify ARNs of the resources to target
        }
    ],
)

# Print response from AWS Fault Injection Simulator
print(response)
