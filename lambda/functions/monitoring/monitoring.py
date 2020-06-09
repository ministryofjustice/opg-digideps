import psycopg2
import boto3


def lambda_handler(event, context):
    conn_string = "dbname='api' port='5432' user='digidepsmaster' password='DigiDeps102!' host='api-ddpb3430.cluster-cvvsyavaaaqt.eu-west-1.rds.amazonaws.com'"
    conn = psycopg2.connect(conn_string)
    cursor = conn.cursor()
    cursor.execute("select count(*) from document limit 1")
    conn.commit()

    records = cursor.fetchall()

    for i in records:
        number_of_documents = "count: " + str(i[0])

    cursor.close()

    lambda_response = {
        "isBase64Encoded": False,
        "statusCode": 200,
        "headers": {"Content-Type": "application/json"},
        "body": str(number_of_documents),
    }

    return lambda_response
