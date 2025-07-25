resource "aws_ecs_task_definition" "sirius_files_sync" {
  family                   = "sirius-files-sync-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.sirius_files_sync_container}]"
  task_role_arn            = aws_iam_role.sirius_files_sync.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  runtime_platform {
    cpu_architecture        = "ARM64"
    operating_system_family = "LINUX"
  }
  tags = var.default_tags
}

resource "aws_ecs_service" "sirius_files_sync" {
  name                    = aws_ecs_task_definition.sirius_files_sync.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.sirius_files_sync.arn
  desired_count           = local.environment == "production02" ? 1 : 0
  launch_type             = "FARGATE"
  platform_version        = "1.4.0"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  wait_for_steady_state   = true
  tags                    = var.default_tags

  service_connect_configuration {
    enabled   = true
    namespace = aws_service_discovery_http_namespace.cloudmap_namespace.arn
  }

  network_configuration {
    security_groups  = [module.sirius_files_sync_service_security_group.id]
    subnets          = data.aws_subnet.private[*].id
    assign_public_ip = false
  }

  deployment_controller {
    type = "ECS"
  }

  deployment_circuit_breaker {
    enable   = false
    rollback = false
  }

  lifecycle {
    create_before_destroy = true
  }

  depends_on = [
    aws_ecs_service.front,
    aws_ecs_service.api,
    aws_ecs_service.htmltopdf,
    aws_ecs_service.scan
  ]
}

locals {
  sirius_files_sync_container = jsonencode(
    {
      name    = "sirius-files-sync",
      image   = local.images.client,
      command = ["sh", "scripts/document_and_checklist_continuous.sh", "-d"],
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "sirius-files-sync"
        }
      },
      secrets = [
        {
          name      = "API_CLIENT_SECRET",
          valueFrom = data.aws_secretsmanager_secret.front_api_client_secret.arn
        },
        {
          name      = "SECRET",
          valueFrom = data.aws_secretsmanager_secret.front_frontend_secret.arn
        },
        {
          name      = "SIRIUS_API_BASE_URI",
          valueFrom = aws_ssm_parameter.sirius_api_base_uri.arn
        }
      ],
      environment = [
        {
          name  = "API_URL",
          value = "http://api"
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
          name  = "APP_ENV",
          value = var.account.app_env
        },
        {
          name  = "OPG_DOCKER_TAG",
          value = var.docker_tag
        },
        {
          name  = "ADMIN_HOST",
          value = "https://${var.admin_fully_qualified_domain_name}"
        },
        {
          name  = "NONADMIN_HOST",
          value = "https://${var.front_fully_qualified_domain_name}"
        },
        {
          name  = "SESSION_REDIS_DSN",
          value = "redis://${aws_route53_record.frontend_redis.fqdn}"
        },
        {
          name  = "SESSION_PREFIX",
          value = "dd_check"
        },
        {
          name  = "EMAIL_SEND_INTERNAL",
          value = "true"
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
          name  = "FEATURE_FLAG_PREFIX",
          value = local.feature_flag_prefix
        },
        {
          name  = "PARAMETER_PREFIX",
          value = local.parameter_prefix
        },
        {
          name  = "HTMLTOPDF_ADDRESS",
          value = "http://htmltopdf:8080"
        },
        {
          name  = "WORKSPACE",
          value = local.environment
        },
        { name = "FIXTURES_ENABLED", value = var.account.fixtures_enabled ? "true" : "false" }
      ]
    }
  )
}

locals {
  sirius_files_sync_sg_rules = {
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
    api_gateway = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
    pdf = {
      port        = 8080
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.htmltopdf_security_group.id
    }
    mock_sirius_integration = {
      port        = 8080
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.mock_sirius_integration_security_group.id
    }
  }
}

#trivy:ignore:avd-aws-0104 - Currently needed in as no domain egress filtering
module "sirius_files_sync_service_security_group" {
  source      = "./modules/security_group"
  description = "Sirius Files Sync Service"
  rules       = local.sirius_files_sync_sg_rules
  name        = "sirius-files-sync-service"
  tags        = var.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}
