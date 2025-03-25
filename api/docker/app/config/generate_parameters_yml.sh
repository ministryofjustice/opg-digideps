#!/bin/sh

# This file is auto-generated during the composer install


# This file is a "template" of what your parameters.yml file should look like
parameters:
    cloudwatch_logs_client_params:
       version: 'latest'
       region: 'eu-west-1'
    {{ if exists "/cloudwatch/logs/localstack" }}
       endpoint: 'http://localstack:4566'
       validate: false
       credentials:
           key: 'FAKE_ID'
           secret: 'FAKE_KEY'
    {{ else }}
       validate: true
    {{ end }}
    ssm_client_params:
      version: 'latest'
      region: 'eu-west-1'
    {{ if exists "/ssm/localstack" }}
      endpoint: 'http://localstack:4566'
      validate: false
      credentials:
        key: 'FAKE_ID'
        secret: 'FAKE_KEY'
    {{ else }}
      validate: true
    {{ end }}
    session_prefix: {{ getv "/session/prefix" }}
    secrets_manager_client_params:
      version: 'latest'
      region: 'eu-west-1'
    {{ if exists "/secrets/manager/localstack" }}
      endpoint: 'http://localstack:4566'
      validate: false
      credentials:
        key: 'FAKE_ID'
        secret: 'FAKE_KEY'
    {{ else }}
      validate: true
    {{ end }}
    database_driver: pdo_pgsql
    database_host: {{ getv "/database/hostname" }}
    database_port: {{ getv "/database/port" }}
    database_name: {{ getv "/database/name" }}
    database_user: {{ getv "/database/username" }}
    database_password: {{ getv "/database/password" }}
    database_ssl_mode: {{ getv "/database/ssl" }}
    locale: en
    secret: {{ getv "/secret" }}
    redis_dsn: '{{ getv "/redis/dsn" }}'
    log_level: warning
    verbose_log_level: notice
    log_path: /var/log/app/application.log
    workspace: {{ getv "/workspace" }}

    s3_client_params:
        version: 'latest'
        region: 'eu-west-1'
    {{ if exists "/s3/localstack" }}
        endpoint: 'http://localstack:4566'
        use_path_style_endpoint: true
        validate: false
        credentials:
            key: 'FAKE_ID'
            secret: 'FAKE_KEY'
    {{ else }}
        validate: true
    {{ end }}
    s3_satisfaction_bucket: opg-performance-data
    s3_sirius_bucket: {{ if exists "/s3/sirius/bucket" }}{{ getv "/s3/sirius/bucket" }}{{ else }}not_set{{ end }}
    pa_pro_report_csv_filename: {{ if exists "/pa/pro/report/csv/filename" }}{{ getv "/pa/pro/report/csv/filename" }}{{ else }}not_set{{ end }}
    lay_report_csv_filename: {{ if exists "/lay/report/csv/filename" }}{{ getv "/lay/report/csv/filename" }}{{ else }}not_set{{ end }}


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
  file_scanner_sslverify: "%env(bool:FILESCANNER_SSLVERIFY)%"
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
