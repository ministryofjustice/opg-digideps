# Define function directory
ARG FUNCTION_DIR="/function"
FROM python:3.14.9-alpine3.24sha256:dbeb11d16bb95c3a7e3c25c842cb9f20cbc8c18f8be0461a4a0dcc861ad386a0 AS python-alpine

RUN apk update && apk add --no-cache \
    libstdc++ \
    zstd-libs \
    postgresql-dev \
    python3-dev \
    musl-dev
RUN apk upgrade
RUN pip install --no-cache-dir --upgrade pip setuptools wheel

# Build image
FROM python-alpine as build-image
# Install aws-lambda-cpp build dependencies
RUN apk add  --no-cache \
    gcc \
    build-base \
    libtool \
    autoconf \
    automake \
    elfutils-dev \
    make \
    cmake \
    libcurl

# Include global arg in this stage of the build
ARG FUNCTION_DIR
# Create function directory
RUN mkdir -p ${FUNCTION_DIR}

WORKDIR /${FUNCTION_DIR}
# Copy function code
COPY app app
# Install the runtime interface client
COPY --from=ghcr.io/astral-sh/uv@sha256:32a7bb39b496babbd997b0a39a4d66c126bdaf5661b302bf6f06abda8deca6ff /uv /uvx /bin/
COPY uv.lock .
COPY pyproject.toml .
RUN uv export --locked -o requirements.txt \
    && python -m pip install --no-cache-dir \
    --target ${FUNCTION_DIR} \
    -r requirements.txt

# Multi-stage build: grab a fresh copy of the base image
FROM python-alpine
# Include global arg in this stage of the build
ARG FUNCTION_DIR
# Set working directory to function root directory
WORKDIR ${FUNCTION_DIR}
# Copy in the build image dependencies
COPY --from=build-image ${FUNCTION_DIR} ${FUNCTION_DIR}

ENTRYPOINT [ "/usr/local/bin/python", "-m", "awslambdaric" ]
CMD [ "app.sql_query.lambda_handler" ]
