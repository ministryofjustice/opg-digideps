module "integration_test" {
  source = "./task"
  name   = "integration-test"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.integration_test_container}]"
  tags                  = local.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = data.aws_iam_role.sync.arn
  vpc_id                = data.aws_vpc.vpc.id
  security_group_id     = module.integration_test_security_group.id
}

locals {
  integration_test_sg_rules = {
    ecr     = local.common_sg_rules.ecr
    logs    = local.common_sg_rules.logs
    s3      = local.common_sg_rules.s3
    ssm     = local.common_sg_rules.ssm
    ecr_api = local.common_sg_rules.ecr_api
    secrets = local.common_sg_rules.secrets
    rds = {
      port        = 5432
      protocol    = "tcp"
      type        = "egress"
      target_type = "security_group_id"
      target      = module.api_rds_security_group.id
    }
    front = {
      port        = 443
      protocol    = "tcp"
      type        = "egress"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
  }
}

module "integration_test_security_group" {
  source = "./security_group"
  rules  = local.integration_test_sg_rules
  name   = "integration-test"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}

locals {
  integration_test_container = <<EOF
  {
    "name": "integration-test",
    "image": "${local.images.api}",
    "entrypoint": [ "sh", "./tests/Behat/run-tests.sh" ],
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
      { "name": "SECRET", "valueFrom": "${data.aws_secretsmanager_secret.front_frontend_secret.arn}" }
    ],
    "environment": [
      { "name": "PGHOST", "value": "${local.db.endpoint}" },
      { "name": "PGDATABASE", "value": "${local.db.name}" },
      { "name": "PGUSER", "value": "${local.db.username}" },
      { "name": "ADMIN_HOST", "value": "https://${aws_route53_record.admin.fqdn}" },
      { "name": "NONADMIN_HOST", "value": "https://${aws_route53_record.front.fqdn}" }
    ]
  }
EOF
}
