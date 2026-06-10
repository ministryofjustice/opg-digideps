import pytest
from utils.url import url
from pages.login_page import LoginPage


@pytest.mark.fixture_type("lay_user")
def test_lay_user_can_login(page, base_url, user_fixture):
    login = LoginPage(page, base_url)
    login.goto()
    login.login(user_fixture["email"], user_fixture["password"])
    login.onExpectedPage(page, "courtorder")
    page.goto(url(base_url, "/logout"))
    assert "/login" in page.url


@pytest.mark.fixture_type("pro_user")
def test_org_user_can_login(page, base_url, user_fixture):
    login = LoginPage(page, base_url)
    login.goto()
    login.login(user_fixture["email"], user_fixture["password"])
    login.onExpectedPage(page, "org")
    page.goto(url(base_url, "/logout"))
    assert "/login" in page.url
