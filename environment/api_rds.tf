module "api_aurora" {
  source                              = "./aurora"
  count                               = 1
  aurora_serverless                   = local.account.aurora_serverless
  account_id                          = data.aws_caller_identity.current.account_id
  apply_immediately                   = local.account.deletion_protection ? false : true
  cluster_identifier                  = "api"
  db_subnet_group_name                = local.account.db_subnet_group
  deletion_protection                 = local.account.deletion_protection ? true : false
  database_name                       = "api"
  engine_version                      = local.account.psql_engine_version
  master_username                     = "digidepsmaster"
  master_password                     = data.aws_secretsmanager_secret_version.database_password.secret_string
  instance_count                      = local.account.aurora_instance_count
  instance_class                      = "db.t3.medium"
  kms_key_id                          = data.aws_kms_key.rds.arn
  skip_final_snapshot                 = local.account.deletion_protection ? false : true
  vpc_security_group_ids              = [module.api_rds_security_group.id]
  tags                                = local.default_tags
  log_group                           = aws_cloudwatch_log_group.api_cluster
  iam_database_authentication_enabled = local.account.iam_database_authentication_enabled
}

locals {
  db = {
    endpoint = module.api_aurora[0].endpoint
    port     = module.api_aurora[0].port
    name     = module.api_aurora[0].database_name
    username = module.api_aurora[0].master_username
  }
}

data "aws_iam_role" "enhanced_monitoring" {
  name = "rds-enhanced-monitoring"
}

data "aws_kms_key" "rds" {
  key_id = "alias/aws/rds"
}

resource "aws_cloudwatch_log_group" "api_cluster" {
  name              = "/aws/rds/cluster/api-${local.environment}/postgresql"
  kms_key_id        = aws_kms_key.cloudwatch_logs.arn
  retention_in_days = 180
  tags              = local.default_tags
}

resource "aws_route53_record" "api_postgres" {
  name    = "postgres"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [local.db.endpoint]
  ttl     = 300
}

data "aws_caller_identity" "current" {}

resource "null_resource" "db_setup" {
  triggers = {
    file = filesha1("initial.sql")
  }
  provisioner "local-exec" {
    command = <<-EOF
			while read line; do
				echo "$line"
				aws rds-data execute-statement --resource-arn "$DB_ARN" --database  "$DB_NAME" --secret-arn "$SECRET_ARN" --sql "$line"
			done  < <(awk 'BEGIN{RS=";\n"}{gsub(/\n/,""); if(NF>0) {print $0";"}}' initial.sql)
			EOF
    environment = {
      DB_ARN     = module.api_aurora[0].cluster_arn
      DB_NAME    = module.api_aurora[0].database_name
      SECRET_ARN = data.aws_secretsmanager_secret.database_password.arn
    }
    interpreter = ["bash", "-c"]
  }
  depends_on = [module.api_aurora]
}
