module "reset_database" {
  source = "./task"
  name   = "reset-database"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.reset_database_container}]"
  default_tags          = local.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = data.aws_iam_role.sync.arn
  vpc_id                = data.aws_vpc.vpc.id
}

locals {
  reset_database_container = <<EOF
  {
    "name": "reset-database",
    "image": "${local.images.api}",
    "command": [ "sh", "scripts/resetdb.sh" ],
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
        "awslogs-region": "eu-west-1",
        "awslogs-stream-prefix": "${aws_iam_role.test.name}"
      }
    },
    "secrets": [
      { "name": "DATABASE_PASSWORD", "valueFrom": "${data.aws_secretsmanager_secret.database_password.arn}" },
      { "name": "SECRET", "valueFrom": "${data.aws_secretsmanager_secret.api_secret.arn}" }
    ],
    "environment": [
      { "name": "DATABASE_HOSTNAME", "value": "${aws_db_instance.api.address}" },
      { "name": "DATABASE_NAME", "value": "${aws_db_instance.api.name}" },
      { "name": "DATABASE_PORT", "value": "${aws_db_instance.api.port}" },
      { "name": "DATABASE_USERNAME", "value": "digidepsmaster" },
      { "name": "FIXTURES_ACCOUNTPASSWORD", "value": "Abcd1234" }
    ]
  }
EOF
}
