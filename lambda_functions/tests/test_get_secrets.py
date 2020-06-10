# import json
import pytest
from lambda_functions.functions.monitoring.monitoring import (
    get_secret
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

    client.create_secret(Name=f"default/database-password", SecretString=secret_code)

    assert get_secret() == secret_code
