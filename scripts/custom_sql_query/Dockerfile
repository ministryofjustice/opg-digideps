ARG FUNCTION_DIR="/function"

# ===== BASE IMAGE =====
FROM python:3.14.9-alpine3.24sha256:dbeb11d16bb95c3a7e3c25c842cb9f20cbc8c18f8be0461a4a0dcc861ad386a0 AS python-base

RUN apk update && apk upgrade

# ===== Build Image =====
FROM python-base AS build-image
ARG FUNCTION_DIR

RUN mkdir -p ${FUNCTION_DIR}

# Copy source files
COPY run_custom_query.py ${FUNCTION_DIR}/run_custom_query.py
COPY _verification.sql ${FUNCTION_DIR}/_verification.sql
COPY _run.sql ${FUNCTION_DIR}/_run.sql

# Install build tools ONLY in this stage
RUN pip install --no-cache-dir --upgrade pip setuptools wheel

COPY --from=ghcr.io/astral-sh/uv@sha256:32a7bb39b496babbd997b0a39a4d66c126bdaf5661b302bf6f06abda8deca6ff /uv /uvx /bin/
COPY uv.lock .
COPY pyproject.toml .
RUN uv export --locked -o requirements.txt \
    && python -m pip install --no-cache-dir --target ${FUNCTION_DIR} -r requirements.txt

# ===== FINAL IMAGE =====
FROM python-base AS final
ARG FUNCTION_DIR

# Copy only dependencies + source code
COPY --from=build-image ${FUNCTION_DIR} ${FUNCTION_DIR}

# Remove pip/setuptools/wheel for security
RUN rm -rf /usr/local/lib/python3.12/site-packages/pip* \
           /usr/local/lib/python3.12/site-packages/setuptools* \
           /usr/local/lib/python3.12/site-packages/wheel*

ENTRYPOINT ["python3", "/function/run_custom_query.py"]
