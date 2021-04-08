resource "aws_ecs_task_definition" "admin" {
  family                   = "admin-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.admin_container}]"
  task_role_arn            = aws_iam_role.admin.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

resource "aws_ecs_service" "admin" {
  name                    = aws_ecs_task_definition.admin.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.admin.arn
  desired_count           = 1
  launch_type             = "FARGATE"
  platform_version        = "1.4.0"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  tags                    = local.default_tags

  network_configuration {
    security_groups  = [module.admin_service_security_group.id]
    subnets          = data.aws_subnet.private.*.id
    assign_public_ip = false
  }

  load_balancer {
    target_group_arn = aws_lb_target_group.admin.arn
    container_name   = "admin_app"
    container_port   = 443
  }

  depends_on = [aws_lb_listener.admin]
}

locals {
  admin_container = <<EOF
  {
    "cpu": 0,
    "essential": true,
    "image": "${local.images.client}",
    "mountPoints": [],
    "name": "admin_app",
    "portMappings": [{
      "containerPort": 443,
      "hostPort": 443,
      "protocol": "tcp"
    }],
    "volumesFrom": [],
    "healthCheck": {
      "command": [
        "CMD-SHELL",
        "curl -f -k https://localhost:443/manage/elb || exit 1"
      ],
      "interval": 30,
      "timeout": 10,
      "retries": 3
    },
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
        "awslogs-region": "eu-west-1",
        "awslogs-stream-prefix": "${aws_iam_role.admin.name}"
      }
    },
    "secrets": [
      { "name": "API_CLIENT_SECRET", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.admin_api_client_secret.name}" },
      { "name": "NOTIFY_API_KEY", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.front_notify_api_key.name}" },
      { "name": "SECRET", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.admin_frontend_secret.name}" },
      { "name": "SIRIUS_API_BASE_URI", "valueFrom": "${aws_ssm_parameter.sirius_api_base_uri.arn}" }
    ],
    "environment": [
      { "name": "ADMIN_HOST", "value": "https://${aws_route53_record.admin.fqdn}" },
      { "name": "API_URL", "value": "https://${local.api_service_fqdn}" },
      { "name": "AUDIT_LOG_GROUP_NAME", "value": "audit-${local.environment}" },
      { "name": "EMAIL_SEND_INTERNAL", "value": "${local.account.is_production == 1 ? "true" : "false"}" },
      { "name": "FEATURE_FLAG_PREFIX", "value": "${local.feature_flag_prefix}" },
      { "name": "FILESCANNER_SSLVERIFY", "value": "False" },
      { "name": "FILESCANNER_URL", "value": "https://${local.scan_service_fqdn}:8080" },
      { "name": "GA_DEFAULT", "value": "${local.account.ga_default}" },
      { "name": "GA_GDS", "value": "${local.account.ga_gds}" },
      { "name": "NONADMIN_HOST", "value": "https://${aws_route53_record.front.fqdn}" },
      { "name": "PARAMETER_PREFIX", "value": "${local.parameter_prefix}" },
      { "name": "ROLE", "value": "admin" },
      { "name": "S3_BUCKETNAME", "value": "pa-uploads-${local.environment}" },
      { "name": "SESSION_REDIS_DSN", "value": "redis://${aws_route53_record.frontend_redis.fqdn}" },
      { "name": "SESSION_PREFIX", "value": "dd_session_admin" },
      { "name": "APP_ENV", "value": "${local.account.app_env}" },
      { "name": "OPG_DOCKER_TAG", "value": "${var.OPG_DOCKER_TAG}" },
      { "name": "WKHTMLTOPDF_ADDRESS", "value": "http://${local.wkhtmltopdf_service_fqdn}" },
      { "name": "ENVIRONMENT", "value": "${local.environment}" },
      { "name": "NGINX_APP_NAME", "value": "admin" }
    ]
  }

EOF

}
