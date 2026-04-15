FROM python:3.14-alpine3.23@sha256:01f125438100bb6b5770c0b1349e5200b23ca0ae20a976b5bd8628457af607ae

WORKDIR /
COPY app /app
COPY tests /tests
COPY requirements.txt /requirements.txt

RUN python -m pip install --no-cache-dir -r /requirements.txt

ENTRYPOINT ["python", "-m", "unittest", "discover", "-s", "/tests", "-p", "*_tests.py"]
