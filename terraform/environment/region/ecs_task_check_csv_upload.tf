locals {
  check_csv_uploaded_sg_rules = {
    ecr     = local.common_sg_rules.ecr
    logs    = local.common_sg_rules.logs
    s3      = local.common_sg_rules.s3
    ssm     = local.common_sg_rules.ssm
    ecr_api = local.common_sg_rules.ecr_api
    secrets = local.common_sg_rules.secrets
    api = {
      port        = 80
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_service_security_group.id
    }
    gov_uk_bank_holidays_api = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
  }

  check_csv_uploaded_interval = "cron(0 12 ? * MON-FRI *)"
}

#trivy:ignore:avd-aws-0104 - Currently needed in as no domain egress filtering
module "check_csv_uploaded_service_security_group" {
  source      = "./modules/security_group"
  description = "Check CSV Uploaded Service"
  rules       = local.check_csv_uploaded_sg_rules
  name        = "check-csv-uploaded-service"
  tags        = var.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}

resource "aws_ecs_task_definition" "check_csv_uploaded" {
  family                   = "check-csv-uploaded-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.check_csv_uploaded_container}]"
  task_role_arn            = aws_iam_role.front.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  runtime_platform {
    cpu_architecture        = "ARM64"
    operating_system_family = "LINUX"
  }
  tags = var.default_tags
}

resource "aws_cloudwatch_event_rule" "check_csv_uploaded_cron_rule" {
  name                = "${aws_ecs_task_definition.check_csv_uploaded.family}-schedule"
  description         = "Check daily which CSVs have been uploaded in ${terraform.workspace}"
  schedule_expression = local.check_csv_uploaded_interval
  tags                = var.default_tags
  state               = local.environment == "production02" ? "ENABLED" : "DISABLED"
}

resource "aws_cloudwatch_event_target" "check_csv_uploaded_scheduled_task" {
  target_id = "ScheduledCheckCSVUploaded"
  rule      = aws_cloudwatch_event_rule.check_csv_uploaded_cron_rule.name
  arn       = aws_ecs_cluster.main.arn
  role_arn  = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.check_csv_uploaded.arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"
    network_configuration {
      subnets          = data.aws_subnet.private[*].id
      assign_public_ip = false
      security_groups  = [module.check_csv_uploaded_service_security_group.id]
    }
  }
}

locals {
  check_csv_uploaded_container = jsonencode(
    {
      name    = "check-csv-uploaded",
      image   = local.images.client,
      command = ["sh", "scripts/check-csv-uploaded.sh", "-d"],
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "check-csv-uploaded"
        }
      },
      secrets = [
        {
          name = "API_CLIENT_SECRET",
        valueFrom = data.aws_secretsmanager_secret.front_api_client_secret.arn },
        {
          name = "NOTIFY_API_KEY",
        valueFrom = data.aws_secretsmanager_secret.front_notify_api_key.arn },
        {
          name = "SECRET",
        valueFrom = data.aws_secretsmanager_secret.front_frontend_secret.arn },
        {
          name = "SIRIUS_API_BASE_URI",
        valueFrom = aws_ssm_parameter.sirius_api_base_uri.arn }
      ],
      environment = [
        {
          name  = "ADMIN_HOST",
          value = "https://${var.admin_fully_qualified_domain_name}"
        },
        {
          name  = "API_URL",
          value = "http://api"
        },
        {
          name  = "APP_ENV",
          value = var.account.app_env
        },
        {
          name  = "AUDIT_LOG_GROUP_NAME",
          value = "audit-${local.environment}"
        },
        {
          name  = "EMAIL_SEND_INTERNAL",
          value = var.account.is_production == 1 ? "true" : "false"
        },
        {
          name  = "ENVIRONMENT",
          value = local.environment
        },
        {
          name  = "FEATURE_FLAG_PREFIX",
          value = local.feature_flag_prefix
        },
        {
          name  = "FILESCANNER_SSLVERIFY",
          value = "false"
        },
        {
          name  = "FILESCANNER_URL",
          value = "http://scan:8080"
        },
        {
          name  = "GA_DEFAULT",
          value = var.account.ga_default
        },
        {
          name  = "GA_GDS",
          value = var.account.ga_gds
        },
        {
          name  = "HTMLTOPDF_ADDRESS",
          value = "http://htmltopdf:8080"
        },
        {
          name  = "NGINX_APP_NAME",
          value = "frontend"
        },
        {
          name  = "NONADMIN_HOST",
          value = "https://${var.front_fully_qualified_domain_name}"
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
          name  = "ROLE",
          value = "front"
        },
        {
          name  = "S3_BUCKETNAME",
          value = "pa-uploads-${local.environment}"
        },
        {
          name  = "SECRETS_PREFIX",
          value = var.secrets_prefix
        },
        {
          name  = "SESSION_REDIS_DSN",
          value = "redis://${aws_route53_record.frontend_redis.fqdn}"
        },
        {
          name  = "SESSION_PREFIX",
          value = "dd_front"
        },
        {
          name  = "WORKSPACE",
          value = local.environment
        }
      ]
    }
  )
}
