import psycopg2
import boto3
import os
from botocore.exceptions import ClientError
import json


def lambda_handler(event, context):

    checks = {
        "queued_documents": queued_documents,
    }

    if "check_name" in event:

        host = os.getenv("DB_ENDPOINT")
        user = os.getenv("DB_USER")
        db = os.getenv("DB_NAME")
        db_port = os.getenv("DB_PORT")
        password_name = os.getenv("SECRET_NAME")
        secret = get_secret(password_name)

        conn_string = f"dbname='{db}' port='{db_port}' user='{user}' password='{secret}' host='{host}'"
        conn = psycopg2.connect(conn_string)
        response = checks[event["check_name"]](conn)
        status_code = 200
        msg = str(response)
    else:
        status_code = 400
        msg = "Invalid JSON. The field 'check_name', is required"

    lambda_response = {
        "isBase64Encoded": False,
        "statusCode": status_code,
        "headers": {"Content-Type": "application/json"},
        "body": msg,
    }

    return lambda_response


def queued_documents(conn):
    cursor = conn.cursor()
    cursor.execute(
        """select count(*)
            from document d
            inner join report_submission rs
            on d.report_submission_id = rs.id
            where rs.created_on < NOW() - INTERVAL '2 hours'
            and d.synchronisation_status = 'QUEUED';"""
    )
    conn.commit()

    records = cursor.fetchall()
    number_of_documents = 0
    for i in records:
        number_of_documents = i[0]

    cursor.close()

    log_message = {
        "eventType": "Queued_Documents",
        "count": str(number_of_documents)
    }

    print(json.dumps(log_message))

    return log_message


def get_secret(secret_name):
    region_name = "eu-west-1"

    session = boto3.session.Session()
    client = session.client(service_name="secretsmanager", region_name=region_name)

    try:
        get_secret_value_response = client.get_secret_value(SecretId=secret_name)
        secret = get_secret_value_response["SecretString"]
    except ClientError as e:
        print("Unable to get secret from Secrets Manager")
        raise e

    return secret
