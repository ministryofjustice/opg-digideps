import psycopg2


def lambda_handler(event, context):
    conn_string = "dbname='api' port='5432' user='digidepsmaster' password='DigiDeps102!' host='postgres.ddpb3404.internal'"
    conn = psycopg2.connect(conn_string)
    cursor = conn.cursor()
    cursor.execute("select * from document limit 1")
    conn.commit()
    cursor.close()
    print("working")

    lambda_response = {
        "isBase64Encoded": False,
        "statusCode": 200,
        "headers": {"Content-Type": "application/json"},
        "body": "Huzzah",
    }

    return lambda_response
