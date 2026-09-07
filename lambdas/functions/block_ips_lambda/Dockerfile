FROM python:3.14.7-alpine3.24@sha256:c6ead215bfd31f1e433d968853b7a769989117115b728874824e6c0a27cb96fc

COPY --from=ghcr.io/astral-sh/uv@sha256:b46b03ddfcfbf8f547af7e9eaefdf8a39c8cebcba7c98858d3162bd28cf536f6 /uv /uvx /bin/

WORKDIR /
COPY app /app
COPY tests /tests

COPY uv.lock .
RUN uv sync --locked

ENTRYPOINT ["python", "-m", "unittest", "discover", "-s", "/tests", "-p", "*_tests.py"]
