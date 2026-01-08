moved {
  from = module.eu_west_1[0].aws_kms_alias.cloudwatch_logs_alias
  to   = module.eu_west_1[0].module.logs_kms.aws_kms_alias.main_eu_west_1
}

moved {
  from = module.eu_west_1[0].aws_kms_key.cloudwatch_logs
  to   = module.eu_west_1[0].module.logs_kms.aws_kms_key.main
}

moved {
  from = module.eu_west_1[0].module.ecr_api_endpoint_vpc.aws_security_group.vpc_endpoint
  to   = module.eu_west_1[0].module.ecr_api_endpoint_vpc[0].aws_security_group.vpc_endpoint
}
moved {
  from = module.eu_west_1[0].module.ecr_api_endpoint_vpc.aws_security_group_rule.vpc_endpoint_https_in
  to   = module.eu_west_1[0].module.ecr_api_endpoint_vpc[0].aws_security_group_rule.vpc_endpoint_https_in
}
moved {
  from = module.eu_west_1[0].module.ecr_api_endpoint_vpc.aws_vpc_endpoint.vpc_endpoint
  to   = module.eu_west_1[0].module.ecr_api_endpoint_vpc[0].aws_vpc_endpoint.vpc_endpoint
}
moved {
  from = module.eu_west_1[0].module.ecr_endpoint_vpc.aws_security_group.vpc_endpoint
  to   = module.eu_west_1[0].module.ecr_endpoint_vpc[0].aws_security_group.vpc_endpoint
}
moved {
  from = module.eu_west_1[0].module.ecr_endpoint_vpc.aws_security_group_rule.vpc_endpoint_https_in
  to   = module.eu_west_1[0].module.ecr_endpoint_vpc[0].aws_security_group_rule.vpc_endpoint_https_in
}
moved {
  from = module.eu_west_1[0].module.ecr_endpoint_vpc.aws_vpc_endpoint.vpc_endpoint
  to   = module.eu_west_1[0].module.ecr_endpoint_vpc[0].aws_vpc_endpoint.vpc_endpoint
}
moved {
  from = module.eu_west_1[0].module.logs_endpoint_vpc.aws_security_group.vpc_endpoint
  to   = module.eu_west_1[0].module.logs_endpoint_vpc[0].aws_security_group.vpc_endpoint
}
moved {
  from = module.eu_west_1[0].module.logs_endpoint_vpc.aws_security_group_rule.vpc_endpoint_https_in
  to   = module.eu_west_1[0].module.logs_endpoint_vpc[0].aws_security_group_rule.vpc_endpoint_https_in
}
moved {
  from = module.eu_west_1[0].module.logs_endpoint_vpc.aws_vpc_endpoint.vpc_endpoint
  to   = module.eu_west_1[0].module.logs_endpoint_vpc[0].aws_vpc_endpoint.vpc_endpoint
}
moved {
  from = module.eu_west_1[0].module.secrets_endpoint_vpc.aws_security_group.vpc_endpoint
  to   = module.eu_west_1[0].module.secrets_endpoint_vpc[0].aws_security_group.vpc_endpoint
}
moved {
  from = module.eu_west_1[0].module.secrets_endpoint_vpc.aws_security_group_rule.vpc_endpoint_https_in
  to   = module.eu_west_1[0].module.secrets_endpoint_vpc[0].aws_security_group_rule.vpc_endpoint_https_in
}
moved {
  from = module.eu_west_1[0].module.secrets_endpoint_vpc.aws_vpc_endpoint.vpc_endpoint
  to   = module.eu_west_1[0].module.secrets_endpoint_vpc[0].aws_vpc_endpoint.vpc_endpoint
}
moved {
  from = module.eu_west_1[0].module.ssm_endpoint_vpc.aws_security_group.vpc_endpoint
  to   = module.eu_west_1[0].module.ssm_endpoint_vpc[0].aws_security_group.vpc_endpoint
}
moved {
  from = module.eu_west_1[0].module.ssm_endpoint_vpc.aws_security_group_rule.vpc_endpoint_https_in
  to   = module.eu_west_1[0].module.ssm_endpoint_vpc[0].aws_security_group_rule.vpc_endpoint_https_in
}
moved {
  from = module.eu_west_1[0].module.ssm_endpoint_vpc.aws_vpc_endpoint.vpc_endpoint
  to   = module.eu_west_1[0].module.ssm_endpoint_vpc[0].aws_vpc_endpoint.vpc_endpoint
}
