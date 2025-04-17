import hashlib
import json
import os
import string
from datetime import datetime

import boto3
import psycopg2
from botocore.exceptions import ClientError


def get_secret(secret_name, region_name="eu-west-1"):
    if os.getenv("ENVIRONMENT") == "local":
        client = boto3.client(
            service_name="secretsmanager",
            region_name=region_name,
            endpoint_url="http://localstack:4566",
            aws_access_key_id="fake",
            aws_secret_access_key="fake",
        )
    else:
        client = boto3.client(service_name="secretsmanager", region_name=region_name)

    try:
        get_secret_value_response = client.get_secret_value(SecretId=secret_name)
    except ClientError as e:
        raise Exception(f"Error fetching secret: {e}")

    secret = get_secret_value_response["SecretString"]
    return secret


def update_secret(secret_name, secret_data, region_name="eu-west-1"):
    if os.getenv("ENVIRONMENT") == "local":
        client = boto3.client(
            service_name="secretsmanager",
            region_name=region_name,
            endpoint_url="http://localstack:4566",
            aws_access_key_id="fake",
            aws_secret_access_key="fake",
        )
    else:
        client = boto3.client(service_name="secretsmanager", region_name=region_name)

    client.update_secret(SecretId=secret_name, SecretString=json.dumps(secret_data))


def hash_token(token, salt):
    return hashlib.pbkdf2_hmac("sha256", token.encode(), salt.encode(), 310000).hex()


def authenticate_or_store_token(secret_name, username, user_token):
    response = get_secret(secret_name)
    secret_data = json.loads(response)

    if username not in secret_data:
        return False, "User not authorised"

    if username in secret_data and secret_data[username]["token_hash"]:
        stored_hash = secret_data[username]["token_hash"]
        salt = secret_data[username]["salt"]
        computed_hash = hash_token(user_token, salt)

        # Zero out user_token from memory
        user_token = ""

        if computed_hash == stored_hash:
            return True, "Token Success"

        return False, "User not authorised"

    # If no user_token exists, enforce secure user_token policy
    if (
        len(user_token) < 12
        or not any(c.isdigit() for c in user_token)
        or not any(c.isalpha() for c in user_token)
        or not any(c in string.punctuation for c in user_token)
    ):
        return (
            False,
            "Token does not meet policy (Min 12 char length, alpha, numeric and special chars needed)",
        )

    salt = os.urandom(16).hex()
    token_hash = hash_token(user_token, salt)
    secret_data[username] = {"token_hash": token_hash, "salt": salt}
    update_secret(secret_name, secret_data)

    # Zero out user_token from memory
    user_token = ""
    return True, "Created New Token"


def run_insert_custom_query(event, conn):
    calling_user = event["calling_user"]
    custom_query = event["custom_query"]
    validation_query = event["validation_query"]
    expected_before = event["expected_before"]
    expected_after = event["expected_after"]
    try:
        cursor = conn.cursor()

        procedure_args = [
            custom_query,
            validation_query,
            calling_user,
            expected_before,
            expected_after,
            None,
        ]
        sql = "CALL audit.insert_custom_query(%s, %s, %s, %s, %s, %s);"
        cursor.execute(sql, procedure_args)
        result = cursor.fetchall()
        result_object = {}
        for row in result:
            for idx, value in enumerate(row):
                result_object["id_inserted"] = value
        conn.commit()
        cursor.close()
        conn.close()

        return {"message": "Stored procedure executed successfully", "result": result}
    except Exception as e:
        return {"message": "Stored procedure failed to execute", "result": e}


def run_sign_off_custom_query(event, conn):
    query_id = event["query_id"]
    calling_user = event["calling_user"]

    cursor = conn.cursor()
    procedure_args = [query_id, calling_user]
    sql = "CALL audit.sign_off_custom_query(%s, %s);"
    cursor.execute(sql, procedure_args)
    result = cursor.fetchall()
    conn.commit()
    cursor.close()
    conn.close()

    return {"message": "Stored procedure executed successfully", "result": result}


def run_execute_custom_query(event, conn):
    query_id = event["query_id"]
    try:
        cursor = conn.cursor()
        procedure_args = [query_id]
        sql = "CALL audit.execute_custom_query(%s);"
        cursor.execute(sql, procedure_args)
        result = cursor.fetchall()
    except psycopg2.DatabaseError as e:
        result = {"Error": f"Database error: {str(e)}"}
        print(f"Database error: {e}")

    conn.commit()
    cursor.close()
    conn.close()

    return {"message": "Stored procedure executed successfully", "result": result}


def run_revoke_custom_query(event, conn):
    query_id = event["query_id"]
    try:
        cursor = conn.cursor()
        procedure_args = [query_id]
        sql = "CALL audit.revoke_custom_query(%s);"
        cursor.execute(sql, procedure_args)
        result = cursor.fetchall()
        conn.commit()
        cursor.close()
        conn.close()

        return {"message": "Stored procedure executed successfully", "result": result}
    except Exception as e:
        return {"message": "Stored procedure failed to execute", "result": e}


def run_get_custom_query(event, conn):
    query_id = event["query_id"]
    try:
        cursor = conn.cursor()
        procedure_args = [query_id]
        sql = "CALL audit.get_custom_query(%s);"
        cursor.execute(sql, procedure_args)
        result = cursor.fetchall()
        fields = [
            "id",
            "query",
            "confirmation_query",
            "created_by",
            "created_on",
            "signed_off_by",
            "signed_off_on",
            "run_on",
            "expected_before",
            "expected_after",
            "passed",
            "result_message",
        ]
        result_object = {}
        for row in result:
            for idx, value in enumerate(row):
                if isinstance(value, datetime):
                    result_object[fields[idx]] = value.strftime("%Y-%m-%d %H:%M")
                else:
                    result_object[fields[idx]] = value
        conn.commit()
        cursor.close()
        conn.close()

        return {
            "message": "Stored procedure executed successfully",
            "result": result_object,
        }
    except Exception as e:
        return {"message": "Stored procedure failed to execute", "result": e}


def get_rds_connection_info(instance_id):
    rds = boto3.client("rds")
    try:
        response = rds.describe_db_instances(DBInstanceIdentifier=instance_id)
        db_instance = response["DBInstances"][0]
        endpoint = db_instance["Endpoint"]["Address"]
        port = db_instance["Endpoint"]["Port"]
        return endpoint, port
    except Exception as e:
        raise Exception(f"Failed to retrieve RDS instance info: {str(e)}")


def connect_to_db(db_password, workspace):
    instance_id = f"api-{workspace}-0"

    try:
        host, port = get_rds_connection_info(instance_id)

        conn = psycopg2.connect(
            host=host,
            port=port,
            database="api",
            user="custom_sql_user",
            password=db_password,
        )
        db_password = ""
        return conn
    except Exception as e:
        raise Exception(f"Error connecting to the database: {str(e)}")


def get_secret_names(workspace):
    secret_prefix = (
        workspace
        if workspace
        in [
            "local",
            "development",
            "integration",
            "training",
            "preproduction",
            "production",
            "production02",
        ]
        else "default"
    )
    db_secret_name = f"{secret_prefix}/custom-sql-db-password"
    users_sql_users = f"{secret_prefix}/custom-sql-users"

    return db_secret_name, users_sql_users


def lambda_handler(event, context):
    calling_user = event["calling_user"]
    user_token = event["user_token"]
    workspace = event["workspace"]
    db_secret_name, users_sql_users = get_secret_names(workspace)
    authenticated, msg = authenticate_or_store_token(
        users_sql_users, calling_user, user_token
    )
    if not authenticated:
        return {"statusCode": 401, "body": msg}

    procedure_to_call = event["procedure"]
    print(procedure_to_call)
    db_password = get_secret(db_secret_name)
    conn = connect_to_db(db_password, workspace)
    db_password = ""

    if procedure_to_call == "insert_custom_query":
        response = run_insert_custom_query(event, conn)
    elif procedure_to_call == "sign_off_custom_query":
        response = run_sign_off_custom_query(event, conn)
    elif procedure_to_call == "execute_custom_query":
        response = run_execute_custom_query(event, conn)
    elif procedure_to_call == "revoke_custom_query":
        response = run_revoke_custom_query(event, conn)
    elif procedure_to_call == "get_custom_query":
        response = run_get_custom_query(event, conn)
    else:
        response = "Unknown procedure selected"
    conn = ""
    return {"statusCode": 200, "body": response}
