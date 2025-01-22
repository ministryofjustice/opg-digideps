# Define function directory
ARG FUNCTION_DIR="/function"

# ===== BASE IMAGE =====
FROM python:3.12-alpine3.20 AS python-alpine
RUN pip install --no-cache-dir --upgrade pip setuptools wheel
RUN apk update && apk upgrade

# ===== Build image =====
FROM python-alpine as build-image
# Include global arg in this stage of the build
ARG FUNCTION_DIR
# Create function directory
RUN mkdir -p ${FUNCTION_DIR}

WORKDIR ${FUNCTION_DIR}
# Copy function code
COPY run_custom_query.py run_custom_query.py
COPY _verification.sql _verification.sql
COPY _run.sql _run.sql

COPY requirements.txt requirements.txt
# Install the requirements
RUN python -m pip install --upgrade --no-cache-dir pip
RUN python -m pip install --no-cache-dir \
        --target . \
        --requirement requirements.txt

# ===== FINAL IMAGE =====
FROM python-alpine
# Include global arg in this stage of the build
ARG FUNCTION_DIR
# Set working directory to function root directory
WORKDIR ${FUNCTION_DIR}
# Copy in the build image dependencies
COPY --from=build-image ${FUNCTION_DIR} ${FUNCTION_DIR}

ENTRYPOINT [ "python3", "/function/run_custom_query.py" ]
