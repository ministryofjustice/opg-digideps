FROM python:3.14-alpine3.23

WORKDIR /
COPY app /app
COPY tests /tests
COPY requirements.txt /requirements.txt

RUN python -m pip install --no-cache-dir -r /requirements.txt

ENTRYPOINT ["python", "-m", "unittest", "discover", "-s", "/tests", "-p", "*_tests.py"]
