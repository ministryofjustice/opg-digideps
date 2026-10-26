FROM python:3.14.9-alpine3.24sha256:dbeb11d16bb95c3a7e3c25c842cb9f20cbc8c18f8be0461a4a0dcc861ad386a0

WORKDIR /
COPY app /app
COPY tests /tests

COPY --from=ghcr.io/astral-sh/uv@sha256:32a7bb39b496babbd997b0a39a4d66c126bdaf5661b302bf6f06abda8deca6ff /uv /uvx /bin/
COPY uv.lock .
COPY pyproject.toml .
RUN uv export --locked -o requirements.txt && python -m pip install --no-cache-dir -r /requirements.txt

ENTRYPOINT ["python", "-m", "unittest", "discover", "-s", "/tests", "-p", "*_tests.py"]
