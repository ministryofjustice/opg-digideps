import boto3
import os
import sys
from botocore.config import Config
import secrets


def get_session(account_id):
    # Check if the CI environment variable is set
    if 'CI' in os.environ:
        role_to_assume = f'arn:aws:iam::{account_id}:role/digideps-ci'
    else:
        role_to_assume = f'arn:aws:iam::{account_id}:role/operator'

    # Use the Boto3 STS client to assume the role and get a session
    sts_client = boto3.client('sts')
    assumed_role_object = sts_client.assume_role(
        RoleArn=role_to_assume,
        RoleSessionName='DigidepsCycleSecrets'
    )
    credentials = assumed_role_object['Credentials']
    session = boto3.Session(
        aws_access_key_id=credentials['AccessKeyId'],
        aws_secret_access_key=credentials['SecretAccessKey'],
        aws_session_token=credentials['SessionToken']
    )
    print(f"assuming session: {role_to_assume}")
    return session


def cycle_secrets(session, prefix):
    aws_config = Config(
        region_name=os.environ.get('AWS_REGION'),
    )
    secrets_list = [
        f"{prefix}/database-password"
    ]
    secret_manager = session.client('secretsmanager', config=aws_config)

    # Retrieve all secrets in the Secrets Manager service
    secrets_response = secret_manager.list_secrets()

    all_secrets = secrets_response['SecretList']

    # Filter the secrets based on the list of secrets that you specify
    filtered_secrets = [s for s in all_secrets if s['Name'] in secrets_list]

    # Loop through the filtered secrets and update each one to a random 32 character string
    for secret in filtered_secrets:
        print(f"rotating secret: {secret['Name']}")
        secret_manager.update_secret(SecretId=secret['Name'], SecretString=secrets.token_urlsafe(32))
        print(f"rotated secret: {secret['Name']}")


def main(workspace):
    accounts = {
        'development': {
            'id': '248804316466',
            'secret_prefix': 'development'
        },
        'preproduction': {
            'id': '454262938596',
            'secret_prefix': 'preproduction'
        },
        'production': {
            'id': '515688267891',
            'secret_prefix': 'production02'
        }
    }
    account_id = accounts[workspace]["id"]
    prefix = accounts[workspace]["secret_prefix"]
    session = get_session(account_id)
    cycle_secrets(session, prefix)


if __name__ == "__main__":
    # Check if the correct number of arguments are provided
    if len(sys.argv) > 2:
        print("Usage: python script.py <workspace>")
        sys.exit(1)

    # Retrieve the command-line arguments
    workspace = sys.argv[1]
    print(workspace)
    # Call the main function with the arguments
    main(workspace)
