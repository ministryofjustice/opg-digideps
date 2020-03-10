locals {
  document_sync_sg_rules = {
    ecr  = local.common_sg_rules.ecr
    logs = local.common_sg_rules.logs
    s3   = local.common_sg_rules.s3
    ssm = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = data.aws_security_group.ssm_endpoint.id
    }
    api = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_service_security_group.id
    }
  }
}

module "document_sync_service_security_group" {
  source = "./security_group"
  rules  = local.document_sync_sg_rules
  name   = "document-sync-service"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}

resource "aws_ecs_task_definition" "document_sync" {
  family                   = "document-sync-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.document_sync_container}]"
  task_role_arn            = aws_iam_role.front.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

resource "aws_ecs_service" "document_sync" {
  name                    = aws_ecs_task_definition.document_sync.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.document_sync.arn
  desired_count           = 1
  launch_type             = "FARGATE"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  tags                    = local.default_tags

  network_configuration {
    security_groups  = [module.document_sync_service_security_group.id]
    subnets          = data.aws_subnet.private.*.id
    assign_public_ip = false
  }
}

locals {
  document_sync_container = <<EOF
  {
    "name": "document-sync",
    "image": "${local.images.client}",
    "command": [ "sh", "scripts/documentsync.sh", "-d" ],
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
        "awslogs-region": "eu-west-1",
        "awslogs-stream-prefix": "document-sync"
      }
    },
    "secrets": [
      { "name": "API_CLIENT_SECRET", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.front_api_client_secret.name}" },
      { "name": "SECRET", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.front_frontend_secret.name}" },
      { "name": "SIRIUS_API_BASE_URI", "valueFrom": "${data.aws_ssm_parameter.sirius_api_base_uri.arn}" }
    ],
    "environment": [
      { "name": "API_URL", "value": "https://${local.api_service_fqdn}" },
      { "name": "ROLE", "value": "document_sync" },
      { "name": "S3_BUCKETNAME", "value": "pa-uploads-${local.environment}" },
      { "name": "SYMFONY_ENV", "value": "${local.account.symfony_env}" },
      { "name": "OPG_DOCKER_TAG", "value": "${var.OPG_DOCKER_TAG}" },
      { "name": "ADMIN_HOST", "value": "https://${aws_route53_record.admin.fqdn}" },
      { "name": "NONADMIN_HOST", "value": "https://${aws_route53_record.front.fqdn}" },
      { "name": "SESSION_REDIS_DSN", "value": "redis://${aws_route53_record.front_redis.fqdn}" },
      { "name": "MOCK_EMAILS", "value": "${local.account.mock_emails ? "true" : "false"}" },
      { "name": "EMAIL_DOMAIN", "value": "${local.domain}" },
      { "name": "EMAIL_SEND_INTERNAL", "value": "${local.account.is_production == 1 ? "true" : "false"}" },
      { "name": "SMTP_DEFAULT_PASSWORD", "value": "${aws_iam_access_key.ses.ses_smtp_password}" },
      { "name": "SMTP_DEFAULT_USER", "value": "${aws_iam_access_key.ses.id}" },
      { "name": "GA_DEFAULT", "value": "${local.account.ga_default}" },
      { "name": "GA_GDS", "value": "${local.account.ga_gds}" },
      { "name": "FEATURE_FLAG_PREFIX", "value": "${local.feature_flag_prefix}" }
    ]
  }

EOF
}
