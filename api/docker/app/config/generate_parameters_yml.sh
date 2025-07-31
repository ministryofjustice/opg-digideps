#!/bin/sh

cat <<EOF > parameters.yml
parameters:
  session_prefix: "%env(SESSION_PREFIX)%"
  database_driver: pdo_pgsql
  database_host: "%env(DATABASE_HOSTNAME)%"
  database_port: "%env(DATABASE_PORT)%"
  database_name: "%env(DATABASE_NAME)%"
  database_user: "%env(DATABASE_USERNAME)%"
  database_password: "%env(DATABASE_PASSWORD)%"
  database_ssl_mode: "%env(DATABASE_SSL)%"
  locale: en
  secret: "%env(SECRET)%"
  redis_dsn: "%env(REDIS_DSN)%"
  log_level: warning
  verbose_log_level: notice
  log_path: /var/log/app/application.log
  workspace: "%env(WORKSPACE)%"
  s3_satisfaction_bucket: opg-performance-data
  s3_sirius_bucket: "%env(S3_SIRIUS_BUCKET)%"
  pa_pro_report_csv_filename: "%env(PA_PRO_REPORT_CSV_FILENAME)%"
  lay_report_csv_filename: "%env(LAY_REPORT_CSV_FILENAME)%"
EOF

if [[ "$LOCAL_RESOURCES" == "true" ]]; then
cat <<EOF >> parameters.yml
  s3_client_params:
    version: "latest"
    region: "eu-west-1"
    endpoint: "http://localstack:4566"
    use_path_style_endpoint: true
    validate: false
    credentials:
      key: "FAKE_ID"
      secret: "FAKE_KEY"
EOF
else
cat <<EOF >> parameters.yml
  s3_client_params:
    version: "latest"
    region: "eu-west-1"
    validate: true
EOF
fi

if [[ "$LOCAL_RESOURCES" == "true" ]]; then
cat <<EOF >> parameters.yml
  secrets_manager_client_params:
    version: "latest"
    region: "eu-west-1"
    endpoint: "http://localstack:4566"
    validate: false
    credentials:
      key: "FAKE_ID"
      secret: "FAKE_KEY"
EOF
else
cat <<EOF >> parameters.yml
  secrets_manager_client_params:
    version: "latest"
    region: "eu-west-1"
    validate: true
EOF
fi

if [[ "$LOCAL_RESOURCES" == "true" ]]; then
cat <<EOF >> parameters.yml
  ssm_client_params:
    version: "latest"
    region: "eu-west-1"
    endpoint: "http://localstack:4566"
    validate: false
    credentials:
      key: "FAKE_ID"
      secret: "FAKE_KEY"
EOF
else
cat <<EOF >> parameters.yml
  ssm_client_params:
    version: "latest"
    region: "eu-west-1"
    validate: true
EOF
fi

if [[ "$LOCAL_RESOURCES" == "true" ]]; then
cat <<EOF >> parameters.yml
  cloudwatch_logs_client_params:
    version: "latest"
    region: "eu-west-1"
    endpoint: "http://localstack:4566"
    validate: false
    credentials:
      key: "FAKE_ID"
      secret: "FAKE_KEY"
EOF
else
cat <<EOF >> parameters.yml
  cloudwatch_logs_client_params:
    version: "latest"
    region: "eu-west-1"
    validate: true
EOF
fi
