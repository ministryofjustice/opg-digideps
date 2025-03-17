#!/bin/sh

cat <<EOF > parameters.yml
parameters:
  locale: en
  workspace: "%env(WORKSPACE)%"
  secret: "%env(SECRET)%"
  api_base_url: "%env(API_URL)%"
  api_client_secret: "%env(API_CLIENT_SECRET)%"
  non_admin_host: "%env(NONADMIN_HOST)%"
  admin_host: "%env(ADMIN_HOST)%"
  client_base_urls:
    front: "%env(NONADMIN_HOST)%"
    admin: "%env(ADMIN_HOST)%"
  session_expire_seconds: 3900
  session_popup_show_after: 3600
  redis_dsn: "%env(SESSION_REDIS_DSN)%"
  session_prefix: "%env(SESSION_PREFIX)%"
  use_redis: true
  session_engine: redis
  log_level: warning
  verbose_log_level: notice
  log_path: /var/log/app/application.log
  ga:
    default: "%env(GA_DEFAULT)%"
    gds: "%env(GA_GDS)%"
  opg_docker_tag: "%env(OPG_DOCKER_TAG)%"
  email_params:
    feedback_send_to_address: "%env(FEEDBACK_ADDRESS)%"
    update_send_to_address: "%env(UPDATE_ADDRESS)%"
  htmltopdf_address: "%env(HTMLTOPDF_ADDRESS)%"
  s3_bucket_name: "%env(S3_BUCKETNAME)%"
  s3_sirius_bucket: "%env(S3_SIRIUS_BUCKET)%"
  file_scanner_url: "%env(FILESCANNER_URL)%"
  file_scanner_sslverify: "%env(FILESCANNER_SSLVERIFY)%"
  pa_pro_report_csv_filename: "%env(PA_PRO_REPORT_CSV_FILENAME)%"
  lay_report_csv_filename: "%env(LAY_REPORT_CSV_FILENAME)%"
EOF

if [[ "$ENVIRONMENT" == "local" ]]; then
cat <<EOF >> parameters.yml
  session_cookie_secure: false
EOF
else
cat <<EOF >> parameters.yml
  session_cookie_secure: true
EOF
fi

if [[ "$ENVIRONMENT" == "local" ]]; then
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

if [[ "$ENVIRONMENT" == "local" ]]; then
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

if [[ "$ENVIRONMENT" == "local" ]]; then
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

if [[ "$ENVIRONMENT" == "local" ]]; then
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
