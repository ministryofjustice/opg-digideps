import re

from utils.url import url


class LoginPage:
    def __init__(self, page, base_url):
        self.page = page
        self.base_url = base_url

    def goto(self):
        self.page.goto(url(self.base_url, "/login"))

    def login(self, email, password):
        self.page.fill("#login_email", email)
        self.page.fill("#login_password", password)

        with self.page.expect_navigation():
            self.page.click("#login_login")

    def onExpectedPage(self, page, expected):
        pattern = rf"/{re.escape(expected)}(/|$)"
        assert re.search(pattern, page.url)
