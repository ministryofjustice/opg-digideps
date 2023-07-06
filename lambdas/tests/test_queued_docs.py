# import json
import pytest
import psycopg2
from psycopg2.extensions import ISOLATION_LEVEL_AUTOCOMMIT
from lambdas.functions.monitoring.monitoring import (
    get_secret,
    queued_documents
)

import boto3
from moto import (mock_secretsmanager)


@pytest.mark.parametrize(
    "secret_code, environment, region",
    [("i_am_a_secret_code", "development", "eu-west-1")],
)
@mock_secretsmanager
def test_get_secret(secret_code, environment, region):
    session = boto3.session.Session()
    client = session.client(service_name="secretsmanager", region_name=region)

    client.create_secret(Name=f"local/database-password", SecretString=secret_code)

    assert get_secret("local/database-password") == secret_code


def setup_db(conn, no_of_queued):
    cursor = conn.cursor()
    cursor.execute("""
    CREATE TABLE document(
    id integer PRIMARY KEY,
    report_submission_id int,
    created_on timestamp(0) without time zone,
    synchronisation_status character varying(255))
    """)
    conn.commit()

    cursor.execute("""
        CREATE TABLE report_submission(
        id integer PRIMARY KEY,
        created_on timestamp(0) without time zone)
        """)
    conn.commit()

    cursor.execute(
        """
        insert into report_submission
        (id, created_on)
        values (1,'20200101')
        """
    )
    conn.commit()

    for doc in range(no_of_queued):
        cursor.execute(
            f"""
            insert into document
            (id, report_submission_id, created_on, synchronisation_status)
            values ((select coalesce(max(id), 0)  + 1 + {doc} from document), 1, '20200101','QUEUED')
            """
        )
        conn.commit()

    return no_of_queued


def teardown_db(conn, table):
    cursor = conn.cursor()
    cursor.execute(f"drop table {table}")
    conn.commit()


def create_db(conn_string, db):
    conn_string = conn_string + " dbname='postgres'"
    conn = psycopg2.connect(conn_string)
    cursor = conn.cursor()
    conn.set_isolation_level(ISOLATION_LEVEL_AUTOCOMMIT);
    cursor.execute(f"DROP DATABASE IF EXISTS {db};")
    conn.commit()
    cursor.execute(f"CREATE DATABASE {db};")
    conn.commit()


def test_queued_documents():
    db = "api_test"
    conn_string = "port='5432' user='api' password='api' host='local.postgres'"
    create_db(conn_string, db)
    conn_string = conn_string + f" dbname='{db}'"
    conn = psycopg2.connect(conn_string)
    queued_docs_created = setup_db(conn, 3)
    queued_docs = queued_documents(conn)["count"]
    teardown_db(conn, "document")
    assert queued_docs_created == int(queued_docs)
