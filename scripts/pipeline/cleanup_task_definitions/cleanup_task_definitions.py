import boto3
import logging
import os
import sys


def get_session(account_id):
    # Check if the CI environment variable is set
    if "CI" in os.environ:
        role_to_assume = f"arn:aws:iam::{account_id}:role/digideps-ci"
    else:
        role_to_assume = f"arn:aws:iam::{account_id}:role/operator"

    # Use the Boto3 STS client to assume the role and get a session
    sts_client = boto3.client("sts")
    assumed_role_object = sts_client.assume_role(
        RoleArn=role_to_assume, RoleSessionName="DigidepsInactiveTaskCleanup"
    )
    credentials = assumed_role_object["Credentials"]
    session = boto3.Session(
        aws_access_key_id=credentials["AccessKeyId"],
        aws_secret_access_key=credentials["SecretAccessKey"],
        aws_session_token=credentials["SessionToken"],
    )
    print(f"assuming session: {role_to_assume}")
    return session


def get_inactive_task_definition_arns(client):
    response = client.list_task_definitions(status="INACTIVE", maxResults=10)
    return response


def delete_task_definition(client, arns):
    try:
        client.delete_task_definitions(taskDefinitions=arns)
    except Exception as e:
        logging.warning("Error trying to delete task definitions")
        logging.error(e)
        sys.exit(1)


def main():
    region = os.environ["REGION"]
    # Hard coding to development account
    session = get_session("248804316466")
    client = session.client("ecs", region_name=region)
    arnsToDelete = True
    try:
        while arnsToDelete:
            response = get_inactive_task_definition_arns(client)
            arns = response.get("taskDefinitionArns", [])
            arnsToDelete = True if response.get("nextToken") is not None else False
            if not arns:
                print(f"No inactive task definitions found in {region}")
            else:
                print("Deleting inactive task definitions..")
                delete_task_definition(client, arns)
    except Exception as e:
        logging.warning("Error trying to get inactive task definitions")
        logging.error(e)
        sys.exit(1)

    print("All inactive task definitions deleted")


if __name__ == "__main__":
    main()
