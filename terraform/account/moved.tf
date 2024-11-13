moved {
  from = module.eu_west_1[0].aws_kms_alias.cloudwatch_logs_alias
  to   = module.eu_west_1[0].module.logs_kms.aws_kms_alias.main_eu_west_1
}

moved {
  from = module.eu_west_1[0].aws_kms_key.cloudwatch_logs
  to   = module.eu_west_1[0].module.logs_kms.aws_kms_key.main
}
