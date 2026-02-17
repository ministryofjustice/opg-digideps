ARG FUNCTION_DIR="/function"

# ===== BASE IMAGE =====
FROM python:3.14-alpine3.22 AS python-base
RUN apk update && apk upgrade

# ===== Build Image =====
FROM python-base AS build-image
ARG FUNCTION_DIR

RUN mkdir -p ${FUNCTION_DIR}

# Copy source files
COPY run_custom_query.py ${FUNCTION_DIR}/run_custom_query.py
COPY _verification.sql ${FUNCTION_DIR}/_verification.sql
COPY _run.sql ${FUNCTION_DIR}/_run.sql
COPY requirements.txt /requirements.txt

# Install build tools ONLY in this stage
RUN pip install --no-cache-dir --upgrade pip setuptools wheel

# Install dependencies into the function dir
RUN pip install \
    --no-cache-dir \
    --target ${FUNCTION_DIR} \
    -r /requirements.txt


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
