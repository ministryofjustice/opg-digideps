resource "aws_ecs_task_definition" "admin" {
  family                   = "admin-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.admin_web}, ${local.admin_container}]"
  task_role_arn            = aws_iam_role.admin.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = var.default_tags
}

resource "aws_ecs_service" "admin" {
  name                    = aws_ecs_task_definition.admin.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.admin.arn
  desired_count           = 1
  platform_version        = "1.4.0"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  wait_for_steady_state   = true
  tags                    = var.default_tags

  network_configuration {
    security_groups  = [module.admin_service_security_group.id]
    subnets          = data.aws_subnet.private.*.id
    assign_public_ip = false
  }

  load_balancer {
    target_group_arn = aws_lb_target_group.admin.arn
    container_name   = "admin_web"
    container_port   = 80
  }

  service_connect_configuration {
    enabled   = true
    namespace = aws_service_discovery_http_namespace.cloudmap_namespace.arn
    service {
      discovery_name = "admin"
      port_name      = "admin-port"
      client_alias {
        dns_name = "admin"
        port     = 80
      }
    }
  }

  capacity_provider_strategy {
    capacity_provider = local.capacity_provider
    weight            = 1
  }

  deployment_controller {
    type = "ECS"
  }

  deployment_circuit_breaker {
    enable   = false
    rollback = false
  }

  depends_on = [aws_lb_listener.admin]
}

locals {
  admin_web = jsonencode(
    {
      cpu         = 0,
      essential   = true,
      image       = local.images.client-webserver,
      mountPoints = [],
      name        = "admin_web",
      portMappings = [
        {
          name : "admin-port",
          containerPort : 80,
          hostPort = 80,
          protocol = "tcp"
        }
      ],
      healthCheck = {
        command : [
          "CMD-SHELL",
          "curl -f http://127.0.0.1:80/health-check || exit 1"
        ],
        interval = 30,
        timeout  = 5,
        retries  = 3
      },
      volumesFrom = [],
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "${aws_iam_role.admin.name}.web"
        }
      },
      environment = [
        { name = "APP_HOST", value = "127.0.0.1" },
        { name = "APP_PORT", value = "9000" }
      ]
    }
  )
  admin_container = jsonencode(
    {
      cpu         = 0,
      essential   = true,
      image       = local.images.client,
      mountPoints = [],
      name        = "admin_app",
      portMappings = [{
        containerPort = 9000,
        hostPort      = 9000,
        protocol      = "tcp"
      }],
      volumesFrom = [],
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "${aws_iam_role.admin.name}.app"
        }
      },
      secrets = [
        { name = "API_CLIENT_SECRET", valueFrom = "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.admin_api_client_secret.name}" },
        { name = "NOTIFY_API_KEY", valueFrom = "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.front_notify_api_key.name}" },
        { name = "SECRET", valueFrom = "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.admin_frontend_secret.name}" },
        { name = "SIRIUS_API_BASE_URI", valueFrom = aws_ssm_parameter.sirius_api_base_uri.arn }
      ],
      environment = [
        { name = "ROLE", value = "admin" },
        { name = "ADMIN_HOST", value = "https://${var.admin_fully_qualified_domain_name}" },
        { name = "NONADMIN_HOST", value = "https://${var.front_fully_qualified_domain_name}" },
        { name = "API_URL", value = "http://api" },
        { name = "AUDIT_LOG_GROUP_NAME", value = "audit-${local.environment}" },
        { name = "EMAIL_SEND_INTERNAL", value = var.account.is_production == 1 ? "true" : "false" },
        { name = "FEATURE_FLAG_PREFIX", value = local.feature_flag_prefix },
        { name = "FILESCANNER_SSLVERIFY", value = "False" },
        { name = "FILESCANNER_URL", value = "http://scan:8080" },
        { name = "GA_DEFAULT", value = var.account.ga_default },
        { name = "GA_GDS", value = var.account.ga_gds },
        { name = "PARAMETER_PREFIX", value = local.parameter_prefix },
        { name = "S3_BUCKETNAME", value = "pa-uploads-${local.environment}" },
        { name = "S3_SIRIUS_BUCKET", value = "digideps.${var.account.sirius_environment}.eu-west-1.sirius.opg.justice.gov.uk" },
        { name = "SESSION_REDIS_DSN", value = "redis://${aws_route53_record.frontend_redis.fqdn}" },
        { name = "SESSION_PREFIX", value = "dd_admin" },
        { name = "APP_ENV", value = var.account.app_env },
        { name = "OPG_DOCKER_TAG", value = var.docker_tag },
        { name = "HTMLTOPDF_ADDRESS", value = "http://htmltopdf:8080" },
        { name = "ENVIRONMENT", value = local.environment },
        { name = "NGINX_APP_NAME", value = "admin" },
        { name = "PA_PRO_REPORT_CSV_FILENAME", value = "paProDeputyReport.csv" },
        { name = "LAY_REPORT_CSV_FILENAME", value = "layDeputyReport.csv" },
        { name = "WORKSPACE", value = local.environment }
      ]
    }
  )
}