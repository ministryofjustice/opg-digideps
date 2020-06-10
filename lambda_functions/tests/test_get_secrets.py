import json
import pytest
import psycopg2
import pytest_pgsql
from pytest_dbfixtures import factories
from lambda_functions.functions.monitoring.monitoring import (
    queued_documents,
    get_secret
)

import boto3
from aws_xray_sdk.core import xray_recorder
from moto import (mock_secretsmanager, mock_rds)


@pytest.mark.parametrize(
    "secret_code, environment, region",
    [("i_am_a_secret_code", "development", "eu-west-1")],
)
@mock_secretsmanager
def test_get_secret(secret_code, environment, region):
    # Disable sampling for tests, see github issue:
    # https://github.com/aws/aws-xray-sdk-python/issues/155
    # xray_recorder.configure(sampling=False)

    session = boto3.session.Session()
    client = session.client(service_name="secretsmanager", region_name=region)

    client.create_secret(Name=f"default/database-password", SecretString=secret_code)

    assert get_secret() == secret_code
