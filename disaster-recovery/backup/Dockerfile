FROM python:3.14.8-alpine3.24@sha256:f6a589d43c42b9e7f7dc67a12d37132491f362859a5d750607710cc56da3bc72

# TODO Remove this once alpine upgrade available as it patches a vulnerability
RUN apk update && apk add --upgrade "zlib==1.3.2-r1"

COPY --from=ghcr.io/astral-sh/uv@sha256:61d393e44e249f2e4b526b6c7ddcecce245946826e608e11c93ad4f5bba55b21 /uv /uvx /bin/
COPY uv.lock .
COPY pyproject.toml .
RUN uv export --locked -o requirements.txt \
    && python -m pip install --no-cache-dir -r requirements.txt
RUN pip uninstall -y wheel setuptools
COPY ./cross_account_backup.py /cross_account_backup.py
RUN chmod 755 /cross_account_backup.py
# To delete after
RUN apk update && apk upgrade libuuid

CMD ["python3", "cross_account_backup.py"]
