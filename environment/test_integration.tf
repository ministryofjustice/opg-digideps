module "test_integration" {
  source = "./task"
  name   = "test-integration"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.test_integration_container}]"
  default_tags          = local.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = data.aws_iam_role.sync.arn
  vpc_id                = data.aws_vpc.vpc.id
}

locals {
  test_integration_container = <<EOF
  {
    "name": "test_integration",
    "image": "${local.images.test}",
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
        "awslogs-region": "eu-west-1",
        "awslogs-stream-prefix": "${aws_iam_role.test.name}"
      }
    },
    "secrets": [
      { "name": "PGPASSWORD", "valueFrom": "${data.aws_secretsmanager_secret.database_password.arn}" },
      { "name": "FRONTEND_SECRET", "valueFrom": "${data.aws_secretsmanager_secret.front_frontend_secret.arn}" }
    ],
    "environment": [
      { "name": "PGHOST", "value": "${aws_db_instance.api.address}" },
      { "name": "PGDATABASE", "value": "${aws_db_instance.api.name}" },
      { "name": "PGUSER", "value": "digidepsmaster" },
      { "name": "FRONTEND_ADMIN_HOST", "value": "https://${aws_route53_record.admin.fqdn}" },
      { "name": "FRONTEND_NONADMIN_HOST", "value": "https://${aws_route53_record.front.fqdn}" }
    ]
  }
EOF
}
