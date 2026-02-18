moved {
  from = module.ec2messages_endpoint_vpc[0]
  to   = module.ec2messages_endpoint_vpc
}
moved {
  from = module.ecr_api_endpoint_vpc[0]
  to   = module.ecr_api_endpoint_vpc
}
moved {
  from = module.ecr_endpoint_vpc[0]
  to   = module.ecr_endpoint_vpc
}
moved {
  from = module.logs_endpoint_vpc[0]
  to   = module.logs_endpoint_vpc
}
moved {
  from = module.secrets_endpoint_vpc[0]
  to   = module.secrets_endpoint_vpc
}
moved {
  from = module.ssm_ec2_data_access[0]
  to   = module.ssm_ec2_data_access
}
moved {
  from = module.ssm_endpoint_vpc[0]
  to   = module.ssm_endpoint_vpc
}
moved {
  from = module.ssmmessages_endpoint_vpc[0]
  to   = module.ssmmessages_endpoint_vpc
}

moved {
  from = module.network[0]
  to   = module.network
}
