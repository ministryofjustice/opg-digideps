import os

import pytest

import logging

log = logging.getLogger(__name__)

test_pw = "DigidepsPass1234"
fixture_type_mock_api_response = {
    "lay_user": {
        "email": "lay-opg104-user-5@publicguardian.gov.uk",
    },
    "pro_user": {
        "email": "prof-103-member-1@prof103s.gov.uk",
    },
}


@pytest.fixture(scope="session")
def base_url():
    return os.getenv("FRONT_URL", "https://digideps.local")


@pytest.fixture(scope="session")
def browser_context_args():
    return {
        "ignore_https_errors": True,
    }


@pytest.fixture
def user_fixture(request):
    fixture_type = request.node.get_closest_marker("fixture_type")

    if fixture_type:
        user_type = fixture_type.args[0]
        print(f"🔧 Would create fixture: {user_type}")

        email = fixture_type_mock_api_response[user_type]["email"]
    else:
        raise Exception("No fixture_type specified")

    return {
        "email": email,
        "password": test_pw,
    }
