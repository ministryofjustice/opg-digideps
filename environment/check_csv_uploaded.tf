locals {
  check_csv_uploaded_sg_rules = {
    ecr     = local.common_sg_rules.ecr
    logs    = local.common_sg_rules.logs
    ssm     = local.common_sg_rules.ssm
    ecr_api = local.common_sg_rules.ecr_api
    secrets = local.common_sg_rules.secrets
    api = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_service_security_group.id
    }
  }

  check_csv_uploaded_interval = "cron(0 12 ? * MON-FRI *)"
}

module "check_csv_uploaded_service_security_group" {
  source = "./security_group"
  rules  = local.check_csv_uploaded_sg_rules
  name   = "check-csv-uploaded-service"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
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
  tags                     = local.default_tags
}

resource "aws_ecs_service" "check_csv_uploaded" {
  name                    = aws_ecs_task_definition.check_csv_uploaded.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.check_csv_uploaded.arn
  launch_type             = "FARGATE"
  platform_version        = "1.4.0"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  tags                    = local.default_tags

  network_configuration {
    security_groups  = [module.check_csv_uploaded_service_security_group.id]
    subnets          = data.aws_subnet.private.*.id
    assign_public_ip = false
  }
}

resource "aws_cloudwatch_event_rule" "check_csv_uploaded_cron_rule" {
  name                = "${aws_ecs_task_definition.check_csv_uploaded.family}-schedule"
  description         = "Check daily which CSVs have been uploaded in ${terraform.workspace}"
  schedule_expression = local.check_csv_uploaded_interval
  tags                = local.default_tags
}

resource "aws_cloudwatch_event_target" "check_csv_uploaded_scheduled_task" {
  target_id = "ScheduledCheckCSVUploaded"
  rule      = aws_cloudwatch_event_rule.check_csv_uploaded_cron_rule.name
  arn       = aws_ecs_cluster.main.arn
  role_arn  = data.aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.check_csv_uploaded.arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"
    network_configuration {
      subnets          = data.aws_subnet.private.*.id
      assign_public_ip = true
      security_groups  = [module.check_csv_uploaded_service_security_group.id]
    }
  }
}

locals {
  check_csv_uploaded_container = <<EOF
  {
    "name": "check-csv-uploaded",
    "image": "${local.images.client}",
    "command": [ "sh", "scripts/check-csv-uploaded.sh", "-d" ],
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
        "awslogs-region": "eu-west-1",
        "awslogs-stream-prefix": "check-csv-uploaded"
      }
    }
  }

EOF
}
