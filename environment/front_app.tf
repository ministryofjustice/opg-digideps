resource "aws_ecs_task_definition" "front" {
  family                   = "front-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.front_container}]"
  task_role_arn            = aws_iam_role.front.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

resource "aws_ecs_service" "front" {
  name                    = aws_ecs_task_definition.front.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.front.arn
  desired_count           = local.account.task_count
  launch_type             = "FARGATE"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  tags                    = local.default_tags

  network_configuration {
    security_groups  = [aws_security_group.front.id]
    subnets          = data.aws_subnet.private.*.id
    assign_public_ip = false
  }

  load_balancer {
    target_group_arn = aws_lb_target_group.front.arn
    container_name   = "front_app"
    container_port   = 443
  }

  depends_on = [aws_lb_listener.front_https]
}

locals {
  front_container = <<EOF
  {
    "cpu": 0,
    "essential": true,
    "image": "${local.images.client}",
    "mountPoints": [],
    "name": "front_app",
    "portMappings": [{
      "containerPort": 443,
      "hostPort": 443,
      "protocol": "tcp"
    }],
    "volumesFrom": [],
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
        "awslogs-region": "eu-west-1",
        "awslogs-stream-prefix": "${aws_iam_role.front.name}"
      }
    },
    "secrets": [
      { "name": "FRONTEND_API_CLIENT_SECRET", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.front_api_client_secret.name}" },
      { "name": "FRONTEND_OAUTH2_CLIENT_SECRET", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.oauth2_client_secret.name}" },
      { "name": "FRONTEND_SECRET", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.front_frontend_secret.name}" }
    ],
    "environment": [
      { "name": "FRONTEND_ADMIN_HOST", "value": "https://${aws_route53_record.admin.fqdn}" },
      { "name": "FRONTEND_API_URL", "value": "https://${local.api_service_fqdn}" },
      { "name": "FRONTEND_BEHAT_CONTROLLER_ENABLED", "value": "${local.account.test_enabled ? "true" : "false"}" },
      { "name": "FRONTEND_EMAIL_DOMAIN", "value": "${local.domain}" },
      { "name": "FRONTEND_EMAIL_FEEDBACK_TO", "value": "${local.account.email_feedback_address}" },
      { "name": "FRONTEND_EMAIL_REPORT_TO", "value": "${local.account.email_report_address}" },
      { "name": "FRONTEND_EMAIL_UPDATE_TO", "value": "${local.account.email_update_address}" },
      { "name": "FRONTEND_FILESCANNER_SSLVERIFY", "value": "False" },
      { "name": "FRONTEND_FILESCANNER_URL", "value": "http://${local.scan_service_fqdn}:8080" },
      { "name": "FRONTEND_GA_DEFAULT", "value": "${local.account.ga_default}" },
      { "name": "FRONTEND_GA_GDS", "value": "${local.account.ga_gds}" },
      { "name": "FRONTEND_NONADMIN_HOST", "value": "https://${aws_route53_record.front.fqdn}" },
      { "name": "FRONTEND_OAUTH2_CLIENT_ID", "value": "0" },
      { "name": "FRONTEND_OAUTH2_ENABLED", "value": "false" },
      { "name": "FRONTEND_ROLE", "value": "front" },
      { "name": "FRONTEND_S3_BUCKETNAME", "value": "pa-uploads-${local.environment}" },
      { "name": "FRONTEND_SESSION_COOKIE_SECURE", "value": "true" },
      { "name": "FRONTEND_SESSION_MEMCACHE", "value": "memcachefront" },
      { "name": "FRONTEND_SESSION_REDIS_DSN", "value": "redis://${aws_route53_record.front_redis.fqdn}" },
      { "name": "FRONTEND_SMTP_DEFAULT_PASSWORD", "value": "${aws_iam_access_key.ses.ses_smtp_password}" },
      { "name": "FRONTEND_SMTP_DEFAULT_USER", "value": "${aws_iam_access_key.ses.id}" },
      { "name": "FRONTEND_SMTP_DEFAULT_HOSTNAME", "value": "email-smtp.eu-west-1.amazonaws.com" },
      { "name": "FRONTEND_SMTP_DEFAULT_PORT", "value": "587" },
      { "name": "FRONTEND_SMTP_SECURE_HOSTNAME", "value": "email-smtp.eu-west-1.amazonaws.com" },
      { "name": "FRONTEND_SMTP_SECURE_PORT", "value": "25" },
      { "name": "FRONTEND_TEST_ENABLED", "value": "${local.account.test_enabled}" },
      { "name": "FRONTEND_URL_ADMIN", "value": "https://${aws_route53_record.admin.fqdn}" },
      { "name": "FRONTEND_URL_FRONTEND", "value": "https://${aws_route53_record.front.fqdn}" },
      { "name": "NGINX_INDEX", "value": "app.php" },
      { "name": "OPG_DOCKER_TAG", "value": "${var.OPG_DOCKER_TAG}" },
      { "name": "OPG_NGINX_CLIENTBODYTIMEOUT", "value": "240s" },
      { "name": "OPG_NGINX_CLIENTMAXBODYSIZE", "value": "0" },
      { "name": "OPG_NGINX_INDEX", "value": "app.php" },
      { "name": "OPG_NGINX_ROOT", "value": "/app/web" },
      { "name": "OPG_NGINX_SERVER_NAMES", "value": "*.${data.aws_route53_zone.public.name} *.${local.environment}.internal ~.*" },
      { "name": "OPG_NGINX_SSL_FORCE_REDIRECT", "value": "1" },
      { "name": "OPG_PHP_POOL_CHILDREN_MAX", "value": "12" },
      { "name": "OPG_SERVICE", "value": "front" },
      { "name": "OPG_STACKNAME", "value": "${local.environment}" },
      { "name": "WKHTMLTOPDF_ADDRESS", "value": "http://${local.wkhtmltopdf_service_fqdn}" }
    ]
  }

EOF

}

