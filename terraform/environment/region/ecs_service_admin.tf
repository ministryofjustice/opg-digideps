resource "aws_ecs_task_definition" "admin" {
  family                   = "admin-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.admin_web}, ${local.admin_container}]"
  task_role_arn            = aws_iam_role.admin.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  volume {
    name = "admin-efs-volume"
    efs_volume_configuration {
      file_system_id     = aws_efs_file_system.admin_efs.id
      root_directory     = "/"
      transit_encryption = "ENABLED"
    }
  }
  tags = var.default_tags
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
    subnets          = data.aws_subnet.private[*].id
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

  depends_on = [
    aws_lb_listener.admin,
    aws_ecs_service.api
  ]
}

locals {
  admin_web = jsonencode(
    {
      cpu                    = 0,
      essential              = true,
      image                  = local.images.client-webserver,
      mountPoints            = [],
      name                   = "admin_web",
      readonlyRootFilesystem = true,
      mountPoints = [
        {
          sourceVolume  = "admin-efs-volume",
          containerPath = "/tmp", # Adjust this to a required writable path
          readOnly      = false
        },
        {
          sourceVolume  = "admin-efs-volume",
          containerPath = "/www/data", # Adjust this to a required writable path
          readOnly      = false
        }
      ],
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
          "/opt/scripts/health-check.sh"
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
      cpu                    = 0,
      essential              = true,
      image                  = local.images.client,
      mountPoints            = [],
      name                   = "admin_app",
      readonlyRootFilesystem = true,
      mountPoints = [
        {
          sourceVolume  = "admin-efs-volume",
          containerPath = "/tmp", # Adjust this to a required writable path
          readOnly      = false
        },
        {
          sourceVolume  = "admin-efs-volume",
          containerPath = "/var/www", # Adjust this to a required writable path
          readOnly      = false
        }
      ],
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
      environment = concat(local.frontend_base_variables,
        [
          { name = "NGINX_APP_NAME", value = "admin" },
          { name = "ROLE", value = "admin" },
          { name = "SESSION_PREFIX", value = "dd_admin" },
      ])
    }
  )
}

resource "aws_efs_file_system" "admin_efs" {
  creation_token   = "admin-efs-${local.environment}"
  encrypted        = true
  performance_mode = "generalPurpose"
  throughput_mode  = "bursting"
  tags = {
    Name = "admin-efs-${local.environment}"
  }
}

resource "aws_efs_mount_target" "admin_mount" {
  for_each        = toset(data.aws_subnet.private[*].id)
  file_system_id  = aws_efs_file_system.admin_efs.id
  subnet_id       = each.value
  security_groups = [module.admin_efs_security_group.id]
}
