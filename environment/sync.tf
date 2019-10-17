data "aws_s3_bucket" "sync" {
  bucket   = "backup.complete-deputy-report.service.gov.uk"
  provider = "aws.management"
}

data "aws_iam_role" "sync" {
  name = "sync"
}

resource "aws_ecs_task_definition" "sync" {
  family                   = "sync-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 256
  memory                   = 512
  container_definitions    = "[${local.sync_container}]"
  task_role_arn            = data.aws_iam_role.sync.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

resource "aws_security_group" "sync" {
  name_prefix = aws_ecs_task_definition.sync.family
  vpc_id      = data.aws_vpc.vpc.id
  tags        = local.default_tags

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "sync_out" {
  type      = "egress"
  protocol  = "-1"
  from_port = 0
  to_port   = 0

  security_group_id = aws_security_group.sync.id
  cidr_blocks       = ["0.0.0.0/0"]
}

locals {
  sync_container = <<EOF
{
	"name": "sync",
	"image": "${local.images.sync}",
	"logConfiguration": {
		"logDriver": "awslogs",
		"options": {
			"awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
			"awslogs-region": "eu-west-1",
			"awslogs-stream-prefix": "${data.aws_iam_role.sync.name}"
		}
	},
	"secrets": [{
		"name": "POSTGRES_PASSWORD",
		"valueFrom": "${data.aws_secretsmanager_secret.database_password.arn}"
	}],
	"environment": [{
			"name": "S3_BUCKET",
			"value": "${data.aws_s3_bucket.sync.bucket}"
		},
		{
			"name": "S3_PREFIX",
			"value": "sync"
		},
		{
			"name": "POSTGRES_DATABASE",
			"value": "${aws_db_instance.api.name}"
		},
		{
			"name": "POSTGRES_HOST",
			"value": "${aws_db_instance.api.address}"
		},
		{
			"name": "POSTGRES_PORT",
			"value": "${aws_db_instance.api.port}"
		},
		{
			"name": "POSTGRES_USER",
			"value": "${aws_db_instance.api.username}"
		}
	]
}

EOF
}

data "aws_caller_identity" "ci" {}

output "Tasks" {
  value = {
    sync = {
      Cluster    = aws_ecs_cluster.main.name
      LaunchType = "FARGATE"
      NetworkConfiguration = {
        AwsvpcConfiguration = {
          SecurityGroups = aws_security_group.sync[*].id
          Subnets        = data.aws_subnet.private[*].id
        }
      }
      TaskDefinition = aws_ecs_task_definition.sync.arn
      Overrides = {
        ContainerOverrides = [
          {
            Name    = "sync",
            Command = ["./backup.sh"]
          }
        ]
      }
    }
  }
}

output "Role" {
  value = data.aws_caller_identity.ci.arn
}
