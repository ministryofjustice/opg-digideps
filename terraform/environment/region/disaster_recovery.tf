module "disaster_recovery_backup" {
  source                  = "./modules/disaster_recovery"
  count                   = var.account.dr_backup ? 1 : 0
  default_tags            = var.default_tags
  default_role            = var.default_role
  environment             = local.environment
  images                  = local.images
  execution_role_arn      = aws_iam_role.execution_role.arn
  backup_account_id       = local.backup_account_id
  aws_ecs_cluster_arn     = aws_ecs_cluster.main.arn
  aws_subnet_ids          = data.aws_subnet.private.*.id
  account_id              = var.account.account_id
  db                      = local.db
  log_retention           = 30
  common_sg_rules         = local.common_sg_rules
  aws_vpc_id              = data.aws_vpc.vpc.id
  task_runner_arn         = aws_iam_role.events_task_runner.arn
  task_role_assume_policy = data.aws_iam_policy_document.task_role_assume_policy
  cross_account_role_name = local.cross_account_role_name
  logs_kms_key_arn        = aws_kms_key.cloudwatch_logs.arn
}