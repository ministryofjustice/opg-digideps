# Define function directory
ARG FUNCTION_DIR="/function"
FROM python:3.12-alpine3.20 AS python-alpine
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
COPY requirements.txt requirements.txt
# Install the runtime interface client
RUN python -m pip install --no-cache-dir --upgrade pip
RUN python -m pip install --no-cache-dir \
        --target ${FUNCTION_DIR} \
        --requirement requirements.txt

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
