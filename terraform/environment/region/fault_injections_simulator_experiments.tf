module "fault_injection_simulator_experiments" {
  count                          = var.account.fault_injection_experiments_enabled ? 1 : 0
  source                         = "./modules/fault_injection_simulator_experiments"
  fault_injection_simulator_role = aws_iam_role.fault_injection_simulator
  ecs_cluster                    = aws_ecs_cluster.main.id
  account_name                   = var.account.name
  environment                    = local.environment
}
