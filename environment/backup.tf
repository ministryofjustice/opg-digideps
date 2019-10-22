module "backup" {
  source = "./task"

  name = "backup"
  default_tags = local.default_tags
  environment = local.environment
  execution_role_arn = aws_iam_role.execution_role.arn
  task_role_arn = data.aws_iam_role.sync.arn
  vpc_id = data.aws_vpc.vpc.id
  container_definitions = "[${local.backup_container}]"
}

locals {
  backup_container = <<EOF
{
	"name": "backup",
	"image": "${local.images.sync}",
    "command": ["./backup.sh"],
	"logConfiguration": {
		"logDriver": "awslogs",
		"options": {
			"awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
			"awslogs-region": "eu-west-1",
			"awslogs-stream-prefix": "backup"
		}
	},
	"secrets": [{
		"name": "POSTGRES_PASSWORD",
		"valueFrom": "${data.aws_secretsmanager_secret.database_password.arn}"
	}],
	"environment": [{
			"name": "S3_BUCKET",
			"value": "${data.aws_s3_bucket.backup.bucket}"
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
