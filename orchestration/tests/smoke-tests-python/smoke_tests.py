import os
import sys
import json
import time
from asyncio import Timeout
from requests.exceptions import RequestException

import requests
from bs4 import BeautifulSoup
import boto3


# -----------------------------------------------------------
# Helpers
# -----------------------------------------------------------
def resolve(base_url, href):
    if not href:
        return None
    if href.startswith("http://") or href.startswith("https://"):
        return href
    # naive join; fine for these paths
    if base_url.endswith("/") and href.startswith("/"):
        return base_url[:-1] + href
    if not base_url.endswith("/") and not href.startswith("/"):
        return base_url + "/" + href
    return base_url + href


def error_and_exit(msg):
    print(f"ERROR: {msg}")
    sys.exit(1)


def check_text_contains(expected, actual):
    if expected not in actual:
        error_and_exit(f'Expected "{expected}" but value was different')
    print(f'✓ Found "{expected}"')


def extract_form_data(form):
    payload = {}
    for inp in form.find_all("input"):
        name = inp.get("name")
        value = inp.get("value", "")
        if name:
            payload[name] = value
    return payload


def extract_form_inputs(form):
    """Extract only relevant input fields, skipping unchecked checkboxes."""
    params = {}
    for inp in form.find_all("input"):
        name = inp.get("name")
        if not name:
            continue
        itype = (inp.get("type") or "").lower()
        if itype in ("checkbox", "radio"):
            if inp.has_attr("checked"):
                params[name] = inp.get("value", "1")
            continue
        params[name] = inp.get("value", "")
    return params


def http(session, method, url, *, retries=3, backoff=1, timeout=10, **kwargs):
    """
    Simple retry wrapper.
    Retries on ANY exception.
    """
    for attempt in range(1, retries + 1):
        try:
            return session.request(method, url, timeout=timeout, **kwargs)

        except Exception:
            if attempt == retries:
                error_and_exit(
                    f"HTTP {method.upper()} {url} failed after {retries} retries"
                )

            time.sleep(backoff * attempt)


# -----------------------------------------------------------
# AWS Secret Manager
# -----------------------------------------------------------
def get_secret(environment, endpoint):
    print(f"=== Getting secrets for {environment} ===")
    if environment not in [
        "staging",
        "preproduction",
        "production",
        "training",
        "local",
    ]:
        environment = "default"

    secret_name = f"{environment}/smoke-test-variables"

    if environment == "local":
        client = boto3.client(
            "secretsmanager",
            region_name="eu-west-1",
            endpoint_url=endpoint,
            aws_access_key_id="test",
            aws_secret_access_key="test",
        )
    else:
        client = boto3.client("secretsmanager", region_name="eu-west-1")

    response = client.get_secret_value(SecretId=secret_name)
    data = json.loads(response["SecretString"])

    return (
        data["admin_user"],
        data["admin_password"],
        data["client"],
        data["deputy_user"],
        data["deputy_password"],
    )


# -----------------------------------------------------------
# Session Setup
# -----------------------------------------------------------
def new_session():
    s = requests.Session()
    s.headers.update({"User-Agent": "SmokeTestBot/1.0"})
    return s


# -----------------------------------------------------------
# Actions
# -----------------------------------------------------------
def login(session, base_url, user, password, expected_page):
    print("=== Logging in ===")

    login_url = f"{base_url}/login"
    r = http(session, "get", login_url)

    soup = BeautifulSoup(r.text, "html.parser")

    form = soup.find("form", {"name": "login"})
    if not form:
        error_and_exit("Login form not found")

    payload = extract_form_data(form)

    payload["login[email]"] = user
    payload["login[password]"] = password
    payload["login[login]"] = "Sign in"  # simulate clicking <button>

    session.headers.update({"Referer": login_url, "Origin": base_url})

    r = http(session, "post", login_url, data=payload, allow_redirects=True)
    if expected_page not in r.url:
        error_and_exit(
            f"Login failed. Expected redirect to {expected_page}. Got {r.url}"
        )

    print("✓ Login successful")


def search_for_user(session, base_url, user):
    print("=== Searching for user ===")

    users_url = f"{base_url}/admin/"
    r = http(session, "get", users_url)
    if r.status_code != 200:
        error_and_exit(f"Failed to load users page ({r.status_code})")

    soup = BeautifulSoup(r.text, "html.parser")
    form = soup.find("form", {"name": "admin"})
    if not form:
        error_and_exit("Could not find admin search form")

    params = extract_form_inputs(form)
    params["admin[q]"] = user
    params["admin[search]"] = "Search"

    action = form.get("action")
    target_url = users_url if not action else (base_url + action)

    r = http(session, "get", target_url, params=params)
    if r.status_code != 200:
        error_and_exit(f"Search failed with status {r.status_code}")

    soup = BeautifulSoup(r.text, "html.parser")
    region = soup.select_one(".behat-region-users")
    if not region:
        error_and_exit("Search results region missing")

    # Must find at least one <tr> row
    rows = region.select("table tbody tr")
    if not rows:
        error_and_exit("No user rows returned")

    print("✓ User search returned results")


def search_for_client(session, base_url, client):
    print("=== Searching for client ===")

    url = f"{base_url}/admin/client/search"
    r = http(session, "get", url)
    if r.status_code != 200:
        error_and_exit(f"Failed to load client search page ({r.status_code})")

    soup = BeautifulSoup(r.text, "html.parser")
    form = soup.find("form", {"name": "search_clients"})
    if not form:
        error_and_exit("Client search form not found")

    params = {}
    for inp in form.find_all("input"):
        name = inp.get("name")
        if not name:
            continue
        itype = (inp.get("type") or "").lower()
        if itype in ("checkbox", "radio"):
            if inp.has_attr("checked"):
                params[name] = inp.get("value", "1")
            continue

        params[name] = inp.get("value", "")

    params["search_clients[q]"] = client
    params["search_clients[search]"] = "Search"

    action = form.get("action")
    target = url if not action else base_url + action

    r = http(session, "get", target, params=params)
    if r.status_code != 200:
        error_and_exit(f"Client search failed ({r.status_code})")

    soup = BeautifulSoup(r.text, "html.parser")

    region = soup.select_one(".behat-region-client-search-count")
    if not region:
        error_and_exit("Client search result region missing")

    text = region.text.strip()
    check_text_contains("Found 1 clients", text)

    print("✓ Client search OK")


def check_organisations(session, base_url):
    print("=== Checking organisations ===")

    r = http(session, "get", f"{base_url}/admin/organisations")
    soup = BeautifulSoup(r.text, "html.parser")

    rows = soup.select(".govuk-table__body tr")
    print(f"✓ Found {len(rows)} organisation rows")


def check_submissions(session, base_url):
    print("=== Checking submissions ===")

    r = http(session, "get", f"{base_url}/admin/documents?tab=archived")
    soup = BeautifulSoup(r.text, "html.parser")

    rows = soup.select(".govuk-table__body tr")
    print(f"✓ Found {len(rows)} submissions rows")


def check_analytics(session, base_url):
    print("=== Checking analytics ===")

    url = f"{base_url}/admin/stats/metrics"
    r = http(session, "get", url)
    if r.status_code != 200:
        error_and_exit(f"Failed to load analytics page ({r.status_code})")

    soup = BeautifulSoup(r.text, "html.parser")

    # Select the numeric element for "Total registered deputies"
    element = soup.select_one(
        '.govuk-heading-xl[aria-labelledby="metric-registeredDeputies-total-label"]'
    )
    if not element:
        error_and_exit("Could not find registered deputies metric")

    value = int(element.text.strip())

    if value <= 0:
        error_and_exit(f"Analytics value {value} is not > 0")

    print(f"✓ Analytics value OK: {value}")


def update_user_details(session, base_url):
    print("=== Updating user details ===")

    view_url = f"{base_url}/deputyship-details/your-details"
    edit_url = f"{base_url}/deputyship-details/your-details/edit"

    def _extract_form_payload(form):
        """
        Extract all relevant fields from the form:
        - inputs (skip unchecked checkboxes/radios)
        - selects (current selected option)
        - textareas
        """
        payload = {}

        # inputs
        for inp in form.find_all("input"):
            name = inp.get("name")
            if not name:
                continue
            itype = (inp.get("type") or "").lower()
            if itype in ("checkbox", "radio"):
                if inp.has_attr("checked"):
                    payload[name] = inp.get("value", "1")
                continue
            payload[name] = inp.get("value", "")

        for ta in form.find_all("textarea"):
            name = ta.get("name")
            if not name:
                continue
            payload[name] = (ta.get_text() or "").strip()

        for sel in form.find_all("select"):
            name = sel.get("name")
            if not name:
                continue
            selected = sel.find("option", selected=True)
            if selected is None:
                first = sel.find("option")
                payload[name] = first.get("value", "") if first else ""
            else:
                payload[name] = selected.get("value", "")

        submit = form.find("button", {"type": "submit"})
        if submit and submit.get("name"):
            payload[submit["name"]] = submit.get_text(strip=True) or submit.get(
                "value", "Save"
            )
        else:
            submit_inp = form.find("input", {"type": "submit"})
            if submit_inp and submit_inp.get("name"):
                payload[submit_inp["name"]] = submit_inp.get("value", "Save")

        return payload

    def update(new_name):
        r = http(session, "get", edit_url)
        if r.status_code != 200:
            error_and_exit("Could not load edit details page")

        soup = BeautifulSoup(r.text, "html.parser")

        form = (
            soup.find("form", {"name": "profile"})
            or soup.find("form", {"name": "user_details"})
            or soup.find("form")
        )
        if not form:
            error_and_exit("Edit details form not found")

        params = _extract_form_payload(form)

        candidate_keys = [
            "profile[firstname]",
            "user_details[firstname]",
            "user[firstName]",
        ]
        target_key = next((k for k in candidate_keys if k in params), None)
        if not target_key:
            if form.get("name") == "profile":
                target_key = "profile[firstname]"
            elif form.get("name") == "user_details":
                target_key = "user_details[firstname]"
            else:
                error_and_exit("Could not determine first-name field in edit form")

        params[target_key] = new_name

        r2 = http(session, "post", edit_url, data=params, allow_redirects=True)
        if r2.status_code != 200:
            error_and_exit(f"Failed to submit details ({r2.status_code})")

        r3 = http(session, "get", view_url)
        if r3.status_code != 200:
            error_and_exit(f"Failed to load profile view page ({r3.status_code})")
        soup3 = BeautifulSoup(r3.text, "html.parser")
        region = soup3.select_one(".behat-region-profile-name")
        if not region:
            error_and_exit("Profile name region missing after update")

        check_text_contains(new_name, region.text.strip())
        print(f"✓ User details updated successfully")

    update("SmokeyEdit")
    update("SmokeyJoe")


def log_out(session, base_url):
    print("=== Logging out ===")

    r = http(session, "get", f"{base_url}/logout", allow_redirects=True)
    if "/login" not in r.url:
        error_and_exit(f"Did not redirect to login on logout.")
    print("✓ Logged out")


def check_service_health(session, base_url):
    print("=== Checking service health ===")

    url = f"{base_url}/health-check/service"
    r = http(session, "get", url)
    if r.status_code != 200:
        error_and_exit(f"Health page failed with status {r.status_code}")

    soup = BeautifulSoup(r.text, "html.parser")

    health = {}
    for li in soup.select("ul li"):
        label_tag = li.find("b")
        if not label_tag:
            continue

        label = label_tag.get_text(strip=True)

        # Full text: e.g. "Api : OK"
        full_text = li.get_text(" ", strip=True)

        # Remove label prefix
        raw_value = full_text[len(label) :]  # removes "Api"
        raw_value = raw_value.lstrip(" :")  # removes colon + space

        # Clean final value
        value = raw_value.strip()

        health[label] = value

    # Validate components
    for comp in ("Api", "Redis"):
        val = health.get(comp)
        if val != "OK":
            error_and_exit(f"{comp} health not OK (got: {val!r})")

    print("✓ Service health OK")


def check_report_sections_visible(session, base_url):
    print("=== Checking report sections visible (frontend) ===")

    # Load a page that should contain the "start report" link
    r = http(session, "get", base_url + "/")
    if r.status_code != 200:
        error_and_exit(f"Failed to load frontend home ({r.status_code})")
    soup = BeautifulSoup(r.text, "html.parser")

    start_link = soup.select_one(".behat-link-report-start")
    if not start_link:
        # Try loading deputyship details
        r = http(session, "get", f"{base_url}/deputyship-details")
        soup = BeautifulSoup(r.text, "html.parser")
        start_link = soup.select_one(".behat-link-report-start")
        if not start_link:
            error_and_exit(
                "Could not find start report link (.behat-link-report-start)"
            )

    href = start_link.get("href")
    target = resolve(base_url, href)
    r = http(session, "get", target)
    if r.status_code != 200:
        error_and_exit(f"Failed to open report start/overview page ({r.status_code})")
    soup = BeautifulSoup(r.text, "html.parser")

    expected_texts = [
        "Decisions",
        "Contacts",
        "Visits and care",
        "Gifts",
        "Actions you plan to take",
        "Supporting documents",
    ]

    links = [
        a.get_text(strip=True)
        for a in soup.select("a.opg-overview-section__label-link")
    ]
    for txt in expected_texts:
        if txt not in links:
            error_and_exit(f'Missing report section link with text "{txt}"')
        else:
            print(f'✓ Found report section "{txt}"')


# -----------------------------------------------------------
# Main
# -----------------------------------------------------------
def run_smoke_frontend():
    front_url = os.environ["FRONT_URL"]
    environment = os.environ["ENVIRONMENT"]
    endpoint = os.environ.get("ENDPOINT")

    _, _, _, deputy_user, deputy_pass = get_secret(environment, endpoint)

    session = new_session()
    login(session, front_url, deputy_user, deputy_pass, "courtorder/")
    check_report_sections_visible(session, front_url)
    update_user_details(session, front_url)
    log_out(session, front_url)
    check_service_health(session, front_url)

    print("=== FRONTEND SMOKE PASSED ===")


def run_smoke_admin():
    # ----- Admin flow (unchanged) -----
    base_url = os.environ["ADMIN_URL"]
    environment = os.environ["ENVIRONMENT"]
    endpoint = os.environ.get("ENDPOINT")

    admin_user, admin_pass, client, _, _ = get_secret(environment, endpoint)

    session = new_session()

    login(session, base_url, admin_user, admin_pass, "admin")
    search_for_user(session, base_url, admin_user)
    search_for_client(session, base_url, client)
    check_organisations(session, base_url)
    check_submissions(session, base_url)
    check_analytics(session, base_url)
    update_user_details(session, base_url)
    log_out(session, base_url)
    check_service_health(session, base_url)

    print("=== ADMIN SMOKE PASSED ===")


def run_smoke():
    run_smoke_admin()
    run_smoke_frontend()

    print("=== ALL SMOKE TESTS PASSED ===")


if __name__ == "__main__":
    run_smoke()
