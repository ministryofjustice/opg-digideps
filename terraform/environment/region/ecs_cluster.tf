# ECS Cluster
resource "aws_service_discovery_http_namespace" "cloudmap_namespace" {
  name        = "digideps-${local.environment}"
  description = "Namespace for Service Discovery"
}

resource "aws_ecs_cluster" "main" {
  name = local.environment
  tags = var.default_tags
  setting {
    name  = "containerInsights"
    value = "enabled"
  }
  depends_on = [aws_cloudwatch_log_group.container_insights]
}

resource "aws_cloudwatch_log_group" "container_insights" {
  name              = "/aws/ecs/containerinsights/${local.environment}/performance"
  retention_in_days = 1
  kms_key_id        = data.aws_kms_alias.cloudwatch_application_logs_encryption.arn
  tags              = var.default_tags
}

# Shared variables for tasks and services in cluster

locals {
  api_base_variables = [
    {
      name  = "DATABASE_HOSTNAME",
      value = local.db.endpoint
    },
    {
      name  = "DATABASE_NAME",
      value = local.db.name
    },
    {
      name  = "DATABASE_PORT",
      value = tostring(local.db.port)
    },
    {
      name  = "DATABASE_USERNAME",
      value = local.db.username
    },
    {
      name  = "DATABASE_SSL",
      value = "verify-full"
    },
    {
      name  = "FIXTURES_ACCOUNTPASSWORD",
      value = "DigidepsPass1234"
    },
    {
      name  = "REDIS_DSN",
      value = "redis://${aws_route53_record.api_redis.fqdn}"
    },
    {
      name  = "SESSION_PREFIX",
      value = "dd_api"
    }
  ]

  api_service_variables = [
    {
      name  = "ADMIN_HOST",
      value = "https://${var.admin_fully_qualified_domain_name}"
    },
    {
      name  = "FRONTEND_HOST",
      value = "https://${var.front_fully_qualified_domain_name}"
    },
    {
      name  = "APP_ENV",
      value = var.account.app_env
    },
    {
      name  = "JWT_HOST",
      value = "https://${var.front_fully_qualified_domain_name}"
    },
    {
      name  = "AUDIT_LOG_GROUP_NAME",
      value = "audit-${local.environment}"
    },
    {
      name  = "FEATURE_FLAG_PREFIX",
      value = local.feature_flag_prefix
    },
    {
      name  = "NGINX_APP_NAME",
      value = "api"
    },
    {
      name  = "OPG_DOCKER_TAG",
      value = var.docker_tag
    },
    {
      name  = "PARAMETER_PREFIX",
      value = local.parameter_prefix
    },
    {
      name  = "SECRETS_PREFIX",
      value = join("", [var.secrets_prefix, "/"])
    },
    {
      name  = "WORKSPACE",
      value = local.environment
    },
    {
      name  = "S3_BUCKETNAME",
      value = "pa-uploads-${local.environment}"
    },
    {
      name  = "S3_SIRIUS_BUCKET",
      value = "digideps.${var.account.sirius_environment}.eu-west-1.sirius.opg.justice.gov.uk"
    },
    {
      name  = "PA_PRO_REPORT_CSV_FILENAME",
      value = local.pa_pro_report_csv_filename
    },
    {
      name  = "LAY_REPORT_CSV_FILENAME",
      value = local.lay_report_csv_file
    },
    {
      name  = "RUN_ONE_OFF_MIGRATIONS",
      value = var.account.run_one_off_migrations
    },
  ]

  api_integration_test_variables = [
    {
      name  = "PGHOST",
      value = local.db.endpoint
    },
    {
      name  = "PGDATABASE",
      value = local.db.name
    },
    {
      name  = "PGUSER",
      value = local.db.username
    },
    {
      name  = "NONADMIN_HOST",
      value = "https://${var.front_fully_qualified_domain_name}"
    },
  ]

  api_single_db_tasks_base_variables = [
    {
      name  = "POSTGRES_DATABASE",
      value = local.db.name
    },
    {
      name  = "POSTGRES_HOST",
      value = local.db.endpoint
    },
    {
      name  = "POSTGRES_PORT",
      value = tostring(local.db.port)
    },
    {
      name  = "POSTGRES_USER",
      value = local.db.username
    }
  ]

  fis_template_variables = var.account.fault_injection_experiments_enabled ? [
    { name = "STOP_FRONTEND_TASK_XID", value = module.fault_injection_simulator_experiments[0].ecs_stop_frontend_tasks_template_id }
  ] : []

  frontend_base_variables = [
    { name = "ADMIN_HOST", value = "https://${var.admin_fully_qualified_domain_name}" },
    { name = "NONADMIN_HOST", value = "https://${var.front_fully_qualified_domain_name}" },
    { name = "API_URL", value = "http://api" },
    { name = "APP_ENV", value = var.account.app_env },
    { name = "AUDIT_LOG_GROUP_NAME", value = "audit-${local.environment}" },
    { name = "EMAIL_SEND_INTERNAL", value = var.account.is_production == 1 ? "true" : "false" },
    { name = "ENVIRONMENT", value = local.environment },
    { name = "FEATURE_FLAG_PREFIX", value = local.feature_flag_prefix },
    { name = "FILESCANNER_SSLVERIFY", value = "false" },
    { name = "FILESCANNER_URL", value = "http://scan:8080" },
    { name = "GA_DEFAULT", value = var.account.ga_default },
    { name = "GA_GDS", value = var.account.ga_gds },
    { name = "HTMLTOPDF_ADDRESS", value = "http://htmltopdf:8080" },
    { name = "OPG_DOCKER_TAG", value = var.docker_tag },
    { name = "PARAMETER_PREFIX", value = local.parameter_prefix },
    { name = "S3_BUCKETNAME", value = "pa-uploads-${local.environment}" },
    { name = "S3_SIRIUS_BUCKET", value = "digideps.${var.account.sirius_environment}.eu-west-1.sirius.opg.justice.gov.uk" },
    { name = "SECRETS_PREFIX", value = join("", [var.secrets_prefix, "/"]) },
    { name = "SESSION_REDIS_DSN", value = "redis://${aws_route53_record.frontend_redis.fqdn}" },
    { name = "PA_PRO_REPORT_CSV_FILENAME", value = local.pa_pro_report_csv_filename },
    { name = "LAY_REPORT_CSV_FILENAME", value = local.lay_report_csv_file },
    { name = "WORKSPACE", value = local.environment },
    { name = "UPDATE_ADDRESS", value = "digideps+noop@digital.justice.gov.uk" },
  ]
}
