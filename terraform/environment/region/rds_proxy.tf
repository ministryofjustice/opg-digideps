# Create an IAM role for RDS Proxy to access Secrets Manager
resource "aws_iam_role" "rds_proxy_role" {
  name               = "rds-proxy-role"
  assume_role_policy = data.aws_iam_policy_document.rds_proxy_role_assume_policy.json
}

data "aws_iam_policy_document" "rds_proxy_role_assume_policy" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["rds.amazonaws.com"]
      type        = "Service"
    }
  }
}

# Attach policy for Secrets Manager access
resource "aws_iam_role_policy_attachment" "rds_proxy_secrets" {
  role       = aws_iam_role.rds_proxy_role.name
  policy_arn = "arn:aws:iam::aws:policy/SecretsManagerReadWrite"
}

# Create the RDS Proxy
resource "aws_db_proxy" "example" {
  name                   = "my-rds-proxy"
  engine_family          = "POSTGRESQL"
  role_arn               = aws_iam_role.rds_proxy_role.arn
  vpc_security_group_ids = [aws_security_group.db_proxy_sg.id]
  vpc_subnet_ids         = [data.aws_subnet.private[*].id]

  auth {
    auth_scheme = "SECRETS"
    secret_arn  = aws_secretsmanager_secret.db_credentials.arn
    iam_auth    = "ENABLED"
  }

  require_tls = true
}

resource "aws_security_group" "db_proxy_sg" {
  name        = "${var.account.name}-proxy-db"
  vpc_id      = data.aws_vpc.vpc.id
  description = "proxy db security group"
}

resource "aws_security_group_rule" "proxy_db_to_rds_sg" {
  protocol                 = "tcp"
  from_port                = 5432
  to_port                  = 5432
  security_group_id        = aws_security_group.db_proxy_sg.id
  source_security_group_id = module.api_rds_security_group.id
  type                     = "egress"
}

resource "aws_security_group_rule" "proxy_db_to_rds_ingress_sg" {
  protocol                 = "tcp"
  from_port                = 5432
  to_port                  = 5432
  security_group_id        = module.api_rds_security_group.id
  source_security_group_id = aws_security_group.db_proxy_sg.id
  type                     = "ingress"
}

resource "aws_security_group_rule" "ecs_to_proxy_db_sg" {
  protocol                 = "tcp"
  from_port                = 5432
  to_port                  = 5432
  security_group_id        = module.api_service_security_group.id
  source_security_group_id = aws_security_group.db_proxy_sg.id
  type                     = "ingress"
}

resource "aws_security_group_rule" "ecs_to_proxy_db_ingress_sg" {
  protocol                 = "tcp"
  from_port                = 5432
  to_port                  = 5432
  security_group_id        = aws_security_group.db_proxy_sg.id
  source_security_group_id = module.api_service_security_group.id
  type                     = "egress"
}

resource "aws_secretsmanager_secret" "db_credentials" {
  name        = "${var.account.environment}/db_credentials"
  description = "DB credentials"
  tags        = var.default_tags
}

# Target group for the proxy
resource "aws_db_proxy_target_group" "proxy" {
  db_proxy_name = aws_db_proxy.example.name
  name          = "default"

  connection_pool_config {
    max_connections_percent      = 75
    max_idle_connections_percent = 50
    connection_borrow_timeout    = 120
  }
}

# Register RDS instance with the proxy
resource "aws_db_proxy_target" "proxy" {
  db_proxy_name         = aws_db_proxy.example.name
  target_group_name     = aws_db_proxy_target_group.proxy.name
  db_cluster_identifier = module.api_aurora.cluster_resource_id
}
