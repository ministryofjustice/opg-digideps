FROM python:3.14-alpine3.23@sha256:27ac3ba1699f7a526ad19bf0d35c12369b43d3439e08297a880398d97899c3d8

WORKDIR /
COPY app /app
COPY tests /tests
COPY requirements.txt /requirements.txt

RUN python -m pip install --no-cache-dir -r /requirements.txt

ENTRYPOINT ["python", "-m", "unittest", "discover", "-s", "/tests", "-p", "*_tests.py"]
