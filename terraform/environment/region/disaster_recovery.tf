module "disaster_recovery_backup" {
  source                  = "./modules/disaster_recovery"
  count                   = var.account.dr_backup ? 1 : 0
  account_id              = var.account.account_id
  backup_account_id       = local.backup_account_id
  task_runner_arn         = aws_iam_role.events_task_runner.arn
  execution_role_arn      = aws_iam_role.execution_role_db.arn
  cross_account_role_name = local.cross_account_role_name
  images                  = local.images
  aws_ecs_cluster_arn     = aws_ecs_cluster.main.arn
  aws_subnet_ids          = data.aws_subnet.private[*].id
  db                      = local.db
  aws_vpc_id              = data.aws_vpc.vpc.id
  logs_kms_key_arn        = aws_kms_key.cloudwatch_logs.arn
  log_retention           = 30
  common_sg_rules         = local.common_sg_rules
  task_role_assume_policy = data.aws_iam_policy_document.task_role_assume_policy
  environment             = local.environment
  default_tags            = var.default_tags
}
