module "restore" {
  source = "./task"
  name   = "restore"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.restore_container}]"
  default_tags          = local.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = data.aws_iam_role.sync.arn
  vpc_id                = data.aws_vpc.vpc.id
}

locals {
  restore_container = <<EOF
{
	"name": "restore",
	"image": "${local.images.sync}",
    "command": ["./restore.sh"],
	"logConfiguration": {
		"logDriver": "awslogs",
		"options": {
			"awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
			"awslogs-region": "eu-west-1",
			"awslogs-stream-prefix": "restore"
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
		},
		{
			"name": "DROP_PUBLIC",
			"value": "yes"
		}
	]
}

EOF
}
