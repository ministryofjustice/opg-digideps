import argparse
import json
import sys
import getpass
from io import BytesIO
from botocore.response import StreamingBody

import requests
import boto3


def lambda_invoke(lambda_client, function_name, payload):
    payload_json = json.dumps(payload)
    response = lambda_client.invoke(FunctionName=function_name, Payload=payload_json)
    # Extract the payload from the response
    payload = response["Payload"].read().decode("utf-8")
    parsed_payload = json.loads(payload)
    return parsed_payload


def assume_custom_sql_role(environment):
    environments = {
        "development": "248804316466",
        "training": "454262938596",
        "integration": "454262938596",
        "preproduction": "454262938596",
        "production": "515688267891",
    }
    environment_account_names = {
        "development": "development",
        "training": "preproduction",
        "integration": "preproduction",
        "preproduction": "preproduction",
        "production": "production",
    }
    account = environments.get(environment, environments["development"])
    account_name = environment_account_names.get(
        environment, environment_account_names["development"]
    )

    sts_client = boto3.client("sts")
    assumed_role = sts_client.assume_role(
        RoleArn=f"arn:aws:iam::{account}:role/custom-sql-role-{account_name}",
        RoleSessionName="CustomSqlRole",
    )

    credentials = assumed_role["Credentials"]
    session = boto3.Session(
        aws_access_key_id=credentials["AccessKeyId"],
        aws_secret_access_key=credentials["SecretAccessKey"],
        aws_session_token=credentials["SessionToken"],
    )
    return session


def get_account_name(environment):
    environment_account_names = {
        "development": "development",
        "training": "preproduction",
        "integration": "preproduction",
        "preproduction": "preproduction",
        "production": "production",
    }
    account_name = environment_account_names.get(
        environment, environment_account_names["development"]
    )

    return account_name


def get_user_token():
    user_input = getpass.getpass("Enter your token string: ")
    return user_input


def get_lambda_client(environment):
    if environment == "local":
        # Return a wrapper client for local Lambda invocation via HTTP POST
        return LocalLambdaClient()
    else:
        session = assume_custom_sql_role(environment)
        return session.client("lambda", region_name="eu-west-1")


class LocalLambdaClient:
    def __init__(
        self, base_url="http://localhost:9070/2015-03-31/functions/function/invocations"
    ):
        self.base_url = base_url

    def invoke(self, FunctionName, Payload):
        response = requests.post(self.base_url, data=Payload)
        realistic_response = {}
        encoded_message = json.dumps(response.json()).encode("utf-8")
        payload_stream = BytesIO(encoded_message)
        realistic_response["Payload"] = StreamingBody(
            payload_stream, len(encoded_message)
        )

        return realistic_response


def run_insert(
    lambda_client,
    function_name,
    calling_user,
    sql_file,
    verification_sql_file,
    expected_before,
    expected_after,
    maximum_rows_affected,
    workspace,
    db_endpoint,
):
    if sql_file:
        with open(sql_file, "r") as f:
            sql = f.read()
            sql_cleaned = (
                sql.replace("\n", " ").replace("\r", "").replace("\t", " ").strip()
            )
    else:
        print("Supply the sql_file path argument")
        sys.exit(1)

    if verification_sql_file:
        with open(verification_sql_file, "r") as f:
            sql_verification = f.read()
            sql_verification_cleaned = (
                sql_verification.replace("\n", " ")
                .replace("\r", "")
                .replace("\t", " ")
                .strip()
            )
    else:
        print("Supply the verification_sql_file path argument")
        sys.exit(1)

    if expected_before is None:
        print("Supply the expected_before argument")
        sys.exit(1)

    if expected_after is None:
        print("Supply the expected_after argument")
        sys.exit(1)

    if maximum_rows_affected is None:
        print("Supply the maximum_rows_affected argument")

    payload = {
        "procedure": "insert_custom_query",
        "calling_user": calling_user,
        "custom_query": sql_cleaned,
        "validation_query": sql_verification_cleaned,
        "expected_before": expected_before,
        "expected_after": expected_after,
        "maximum_rows_affected": maximum_rows_affected,
        "user_token": get_user_token(),
        "workspace": workspace,
        "db_endpoint": db_endpoint,
    }
    return lambda_invoke(lambda_client, function_name, payload)


def run_get(
    lambda_client, function_name, query_id, calling_user, workspace, db_endpoint
):
    if not query_id:
        print("Supply the query_id argument")
        sys.exit(1)

    payload = {
        "procedure": "get_custom_query",
        "query_id": query_id,
        "calling_user": calling_user,
        "user_token": get_user_token(),
        "workspace": workspace,
        "db_endpoint": db_endpoint,
    }

    return lambda_invoke(lambda_client, function_name, payload)


def run_sign_off(
    lambda_client, function_name, query_id, calling_user, workspace, db_endpoint
):
    if not query_id:
        print("Supply the query_id argument")
        sys.exit(1)

    payload = {
        "procedure": "sign_off_custom_query",
        "query_id": query_id,
        "calling_user": calling_user,
        "user_token": get_user_token(),
        "workspace": workspace,
        "db_endpoint": db_endpoint,
    }

    return lambda_invoke(lambda_client, function_name, payload)


def run_revoke(
    lambda_client, function_name, query_id, calling_user, workspace, db_endpoint
):
    if not query_id:
        print("Supply the query_id argument")
        sys.exit(1)

    payload = {
        "procedure": "revoke_custom_query",
        "query_id": query_id,
        "calling_user": calling_user,
        "user_token": get_user_token(),
        "workspace": workspace,
        "db_endpoint": db_endpoint,
    }

    return lambda_invoke(lambda_client, function_name, payload)


def run_execute(
    lambda_client, function_name, query_id, calling_user, workspace, db_endpoint
):
    if not query_id:
        print("Supply the query_id argument")
        sys.exit(1)

    payload = {
        "procedure": "execute_custom_query",
        "query_id": query_id,
        "calling_user": calling_user,
        "user_token": get_user_token(),
        "workspace": workspace,
        "db_endpoint": db_endpoint,
    }

    return lambda_invoke(lambda_client, function_name, payload)


def get_current_user():
    try:
        current_user_arn = boto3.client("sts").get_caller_identity().get("Arn")
        return current_user_arn
    except Exception as e:
        print(e)
        sys.exit(1)


def get_db_endpoint(environment):
    if environment == "local":
        return "http://postgres"
    else:
        instance_environment = (
            environment if environment != "production" else "production02"
        )
        instance_id = f"api-{instance_environment}-0"
        session = assume_custom_sql_role(environment)
        rds = session.client("rds", region_name="eu-west-1")
        try:
            response = rds.describe_db_instances(DBInstanceIdentifier=instance_id)
            db_instance = response["DBInstances"][0]
            endpoint = db_instance["Endpoint"]["Address"]
            return endpoint
        except Exception as e:
            raise Exception(f"Failed to retrieve RDS instance info: {str(e)}")


def main(
    environment,
    action,
    query_id=None,
    sql_file=None,
    verification_sql_file=None,
    expected_before=None,
    expected_after=None,
    maximum_rows_affected=None,
):
    sql_file = f"/function/{sql_file}"
    verification_sql_file = f"/function/{verification_sql_file}"
    calling_user = get_current_user()
    lambda_client = get_lambda_client(environment)
    db_endpoint = get_db_endpoint(environment)

    workspace = environment if environment != "production" else "production02"
    account_name = get_account_name(environment)
    function_name = (
        "function" if environment == "local" else f"custom-sql-query-{account_name}"
    )
    if action == "insert":
        response = run_insert(
            lambda_client,
            function_name,
            calling_user,
            sql_file,
            verification_sql_file,
            expected_before,
            expected_after,
            maximum_rows_affected,
            workspace,
            db_endpoint,
        )
    elif action == "get":
        response = run_get(
            lambda_client, function_name, query_id, calling_user, workspace, db_endpoint
        )
    elif action == "sign_off":
        response = run_sign_off(
            lambda_client, function_name, query_id, calling_user, workspace, db_endpoint
        )
    elif action == "revoke":
        response = run_revoke(
            lambda_client, function_name, query_id, calling_user, workspace, db_endpoint
        )
    elif action == "execute":
        response = run_execute(
            lambda_client, function_name, query_id, calling_user, workspace, db_endpoint
        )
    else:
        print("Not a valid action")
        sys.exit(1)

    print(json.dumps(response, indent=4))


if __name__ == "__main__":
    parser = argparse.ArgumentParser(
        description="Script to execute custom SQL queries."
    )

    # Required arguments
    parser.add_argument(
        "environment",
        type=str,
        help="The environment (e.g., local, development, production).",
    )
    parser.add_argument(
        "action",
        type=str,
        choices=["insert", "get", "sign_off", "revoke", "execute"],
        help="The action to perform (insert, get, sign_off, revoke or execute).",
    )

    # Optional arguments
    parser.add_argument("--query_id", type=int, help="The ID of the query.")
    parser.add_argument("--sql_file", type=str, help="Path to the SQL file.")
    parser.add_argument(
        "--verification_sql_file", type=str, help="Path to the verification SQL file."
    )
    parser.add_argument(
        "--expected_before", type=int, help="Expected result before running the query."
    )
    parser.add_argument(
        "--expected_after", type=int, help="Expected result after running the query."
    )
    parser.add_argument(
        "--maximum_rows_affected",
        type=int,
        help="Maximum rows affected after running the query.",
    )

    args = parser.parse_args()

    # Call the main function with parsed arguments
    main(
        environment=args.environment,
        action=args.action,
        query_id=args.query_id,
        sql_file=args.sql_file,
        verification_sql_file=args.verification_sql_file,
        expected_before=args.expected_before,
        expected_after=args.expected_after,
        maximum_rows_affected=args.maximum_rows_affected,
    )
