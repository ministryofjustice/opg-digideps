FROM python:3.14-alpine3.23@sha256:faee120f7885a06fcc9677922331391fa690d911c020abb9e8025ff3d908e510

WORKDIR /
COPY app /app
COPY tests /tests
COPY requirements.txt /requirements.txt

RUN python -m pip install --no-cache-dir -r /requirements.txt

ENTRYPOINT ["python", "-m", "unittest", "discover", "-s", "/tests", "-p", "*_tests.py"]
