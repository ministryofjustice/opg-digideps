import time

import boto3
import os
import sys
from botocore.config import Config
import secrets

db_password_suffix = "database-password"

base_secrets_list = [
    "api-secret",
    "admin-api-client-secret",
    "admin-frontend-secret",
    "front-frontend-secret",
    "front-api-client-secret",
]


def get_session(account_id):
    # Check if the CI environment variable is set
    if "CI" in os.environ:
        role_to_assume = f"arn:aws:iam::{account_id}:role/digideps-ci"
    else:
        role_to_assume = f"arn:aws:iam::{account_id}:role/operator"

    # Use the Boto3 STS client to assume the role and get a session
    sts_client = boto3.client("sts")
    assumed_role_object = sts_client.assume_role(
        RoleArn=role_to_assume, RoleSessionName="DigidepsCycleSecrets"
    )
    credentials = assumed_role_object["Credentials"]
    session = boto3.Session(
        aws_access_key_id=credentials["AccessKeyId"],
        aws_secret_access_key=credentials["SecretAccessKey"],
        aws_session_token=credentials["SessionToken"],
    )
    print(f"assuming session: {role_to_assume}")
    return session


def cycle_secrets(session, workspaces, aws_config):
    secret_manager = session.client("secretsmanager", config=aws_config)

    # Retrieve all secrets in the Secrets Manager service
    all_secrets = []

    paginator = secret_manager.get_paginator("list_secrets")
    for page in paginator.paginate():
        all_secrets.extend(page["SecretList"])

    # Rewrite using list of secret names when we are rotating more than one type
    secrets_list = []
    for base_secret in base_secrets_list:
        for workspace in workspaces:
            secrets_list.append(f"{workspace}/{base_secret}")

    print(f"Attempting to find the following secrets: {secrets_list}")

    # Filter the secrets based on the list of secrets that you specify
    filtered_secrets = [s for s in all_secrets if s["Name"] in secrets_list]

    if len(filtered_secrets) == 0:
        print("No matching secrets found.. Exiting")
        exit(1)

    # Loop through the filtered secrets and update each one to a random 43 character string (yes it is 43 not 32)
    for secret in filtered_secrets:
        print(f"rotating secret: {secret['Name']}")
        secret_manager.update_secret(
            SecretId=secret["Name"], SecretString=secrets.token_urlsafe(32)
        )
        print(f"rotated secret: {secret['Name']}")


def wait_for_cluster_update(client, cluster_identifier):
    # takes a while for it to start the update!
    time.sleep(30)
    sleep_time = 30
    total_time = 0
    while True:
        response = client.describe_db_clusters(DBClusterIdentifier=cluster_identifier)
        db_cluster = response["DBClusters"][0]
        db_cluster_status = db_cluster["Status"]
        if db_cluster_status == "available":
            print(f"RDS cluster {cluster_identifier} update complete.")
            break
        elif total_time > 900:
            print(f"Update timing out for {cluster_identifier}. Exiting")
            exit(1)
        else:
            print(
                f"RDS cluster {cluster_identifier} is {db_cluster_status}. Waiting..."
            )
            time.sleep(sleep_time)  # Wait for 30 seconds before checking again
            total_time += sleep_time


def modify_db_instances_password(session, workspaces, aws_config):
    rds_client = session.client("rds", config=aws_config)
    secrets_client = session.client("secretsmanager", config=aws_config)

    for workspace in workspaces:
        secret_name = f"{workspace}/{db_password_suffix}"
        cluster_identifier = f"api-{workspace}"

        try:
            # Fetch the secret value from AWS Secrets Manager
            secret_response = secrets_client.get_secret_value(SecretId=secret_name)
            secret_string = secret_response["SecretString"]

            if len(secret_string) != 43:
                print(f"Secret string not 43 characters. It is {len(secret_string)}")

            # Apply the RDS modification with the fetched password
            rds_client.modify_db_cluster(
                DBClusterIdentifier=cluster_identifier,
                MasterUserPassword=secret_string,
                ApplyImmediately=True,
            )
            print(f"RDS modification initiated successfully for {cluster_identifier}.")
            wait_for_cluster_update(rds_client, cluster_identifier)
        except Exception as e:
            print("Error occurred:")
            print(str(e))
            exit(1)


def main(environment):
    accounts = {
        "development": {"id": "248804316466", "workspaces": ["development"]},
        "preproduction": {
            "id": "454262938596",
            "workspaces": ["integration", "training", "preproduction"],
        },
        "production": {"id": "515688267891", "workspaces": ["production02"]},
    }
    region_name = os.environ.get("AWS_REGION", "eu-west-1")
    aws_config = Config(region_name=region_name)
    try:
        account_id = accounts[environment]["id"]
        workspaces = accounts[environment]["workspaces"]
    except KeyError as e:
        print(f"Key error: {e}")
        exit(1)
    session = get_session(account_id)
    cycle_secrets(session, workspaces, aws_config)
    modify_db_instances_password(session, workspaces, aws_config)


if __name__ == "__main__":
    # Check if the correct number of arguments are provided
    if len(sys.argv) > 2:
        print("Usage: python script.py <environment>")
        sys.exit(1)

    # Retrieve the command-line arguments
    environment = sys.argv[1]
    print(f"Running in environment: {environment}")
    # Call the main function with the arguments
    main(environment)
