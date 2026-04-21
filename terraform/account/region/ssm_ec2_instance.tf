data "aws_iam_instance_profile" "data_access" {
  name = "data-access"
}

data "aws_iam_role" "data_access" {
  name = "data-access"
}

# SSM Core Statements

resource "aws_iam_role_policy_attachment" "ssm_core_role_policy_document_data_access" {
  role       = data.aws_iam_role.data_access.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonSSMManagedInstanceCore"
}

module "ssm_ec2_data_access" {
  source = "./modules/ssm_ec2_instance"

  instance_type = "t3.micro"
  subnet_id     = module.network.application_subnets[0].id
  name          = "data-access"
  tags          = var.default_tags
  # kms_key_id  = module.logs_kms.eu_west_1_target_key_arn
  instance_profile = data.aws_iam_instance_profile.data_access.name

  vpc_id = module.network.vpc.id
}
