moved {
  from = module.eu_west_1[0].module.ssm_ec2_instance_operator
  to   = module.eu_west_1[0].module.ssm_ec2_instance_data_access
}

moved {
  from = data.aws_iam_instance_profile.operator
  to   = data.aws_iam_instance_profile.data_access
}

moved {
  from = module.eu_west_1[0].data.aws_iam_role.operator
  to   = module.eu_west_1[0].data.aws_iam_role.data_access
}

moved {
  from = module.eu_west_1[0].aws_iam_role_policy_attachment.ssm_core_role_policy_document
  to   = module.eu_west_1[0].aws_iam_role_policy_attachment.ssm_core_role_policy_document_data_access
}

moved {
  from = module.eu_west_1[0].data.aws_iam_policy_document.start_ec2
  to   = module.eu_west_1[0].data.aws_iam_policy_document.start_ec2_data_access
}

moved {
  from = module.eu_west_1[0].aws_iam_policy.start_ec2
  to   = module.eu_west_1[0].aws_iam_policy.start_ec2_data_access
}

moved {
  from = module.eu_west_1[0].aws_iam_role_policy_attachment.start_ec2
  to   = module.eu_west_1[0].aws_iam_role_policy_attachment.start_ec2_data_access
}
