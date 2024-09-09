import json
import boto3
import psycopg2
from botocore.exceptions import ClientError


secret_name = "SOME SECRET NAME TBC"


def get_db_credentials(secret_name, region_name="eu-west-1"):
    session = boto3.session.Session()
    client = session.client(service_name="secretsmanager", region_name=region_name)

    try:
        get_secret_value_response = client.get_secret_value(SecretId=secret_name)
    except ClientError as e:
        raise Exception(f"Error fetching secret: {e}")

    secret = get_secret_value_response["SecretString"]
    return json.loads(secret)


def get_calling_user_arn():
    sts_client = boto3.client("sts")

    # Get the caller's identity
    caller_identity = sts_client.get_caller_identity()

    return caller_identity["Arn"]


def run_insert_custom_query(event, conn, calling_user):
    custom_query = event["custom_query"]
    validation_query = event["validation_query"]
    expected_before = event["expected_before"]
    expected_after = event["expected_before"]
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
        cursor.callproc("insert_custom_query", procedure_args)
        result = cursor.fetchall()
        conn.commit()
        cursor.close()
        conn.close()

        return {"message": "Stored procedure executed successfully", "result": result}
    except Exception as e:
        return {"message": "Stored procedure failed to execute", "result": result}


def run_sign_off_custom_query(event, conn, calling_user):
    query_id = event["query_id"]
    try:
        cursor = conn.cursor()
        procedure_args = [query_id, calling_user]
        cursor.callproc("sign_off_custom_query", procedure_args)
        result = cursor.fetchall()
        conn.commit()
        cursor.close()
        conn.close()

        return {"message": "Stored procedure executed successfully", "result": result}
    except Exception as e:
        return {"message": "Stored procedure failed to execute", "result": result}


def run_execute_and_verify_query(event, conn):
    query_id = event["query_id"]
    try:
        cursor = conn.cursor()
        procedure_args = [query_id]
        cursor.callproc("execute_and_verify_query", procedure_args)
        result = cursor.fetchall()
        conn.commit()
        cursor.close()
        conn.close()

        return {"message": "Stored procedure executed successfully", "result": result}
    except Exception as e:
        return {"message": "Stored procedure failed to execute", "result": result}


def run_revoke_custom_query(event, conn):
    query_id = event["query_id"]
    try:
        cursor = conn.cursor()
        procedure_args = [query_id]
        cursor.callproc("revoke_custom_query", procedure_args)
        result = cursor.fetchall()
        conn.commit()
        cursor.close()
        conn.close()

        return {"message": "Stored procedure executed successfully", "result": result}
    except Exception as e:
        return {"message": "Stored procedure failed to execute", "result": result}


def run_get_custom_query(event, conn):
    query_id = event["query_id"]
    try:
        cursor = conn.cursor()
        procedure_args = [query_id]
        cursor.callproc("get_custom_query", procedure_args)
        result = cursor.fetchall()
        conn.commit()
        cursor.close()
        conn.close()

        return {"message": "Stored procedure executed successfully", "result": result}
    except Exception as e:
        return {"message": "Stored procedure failed to execute", "result": result}


def connect_to_db(db_credentials):
    try:
        conn = psycopg2.connect(
            host=db_credentials["host"],
            database=db_credentials["dbname"],
            user=db_credentials["username"],
            password=db_credentials["password"],
            port=db_credentials["port"],
        )
        return conn
    except Exception as e:
        raise Exception(f"Error connecting to the database: {str(e)}")


def lambda_handler(event, context):
    procedure_to_call = event["procedure"]

    try:
        db_credentials = get_db_credentials(secret_name)
        conn = connect_to_db(db_credentials)
        calling_user_arn = get_calling_user_arn()

        if procedure_to_call == "insert_custom_query":
            response = run_insert_custom_query(event, conn, calling_user_arn)
        elif procedure_to_call == "sign_off_custom_query":
            response = run_sign_off_custom_query(event, conn, calling_user_arn)
        elif procedure_to_call == "execute_and_verify_query":
            response = run_execute_and_verify_query(event, conn)
        elif procedure_to_call == "revoke_custom_query":
            response = run_revoke_custom_query(event, conn)
        elif procedure_to_call == "get_custom_query":
            response = run_get_custom_query(event, conn)
        else:
            response = "Unknown procedure selected"

        return {
            "statusCode": 200,
            "body": json.dumps(response),
        }
    except Exception as e:
        if conn:
            conn.rollback()  # Roll back in case of error
        return {
            "statusCode": 500,
            "body": json.dumps({"message": f"Error executing procedure: {str(e)}"}),
        }
    finally:
        if conn:
            conn.close()  # Ensure the connection is closed
