module "ssm_ec2_instance_data_access" {
  source = "./modules/ssm_ec2_instance"

  instance_type = "t3.micro"
  subnet_id     = aws_subnet.private[0].id
  name          = "data-access"
  tags          = var.default_tags
  # kms_key_id  = module.logs_kms.eu_west_1_target_key_arn
  instance_profile = data.aws_iam_instance_profile.data_access.name

  vpc_id = aws_vpc.main.id
}

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

# Start EC2 Statements

data "aws_iam_policy_document" "start_ec2_data_access" {
  statement {
    sid    = "AllowStartStopSpecificInstance"
    effect = "Allow"

    actions = [
      "ec2:StartInstances",
      "ec2:StopInstances"
    ]

    resources = [module.ssm_ec2_instance_data_access.ssm_instance_arn]
  }
}

resource "aws_iam_policy" "start_ec2_data_access" {
  name   = "data-access-ssm-policy"
  policy = data.aws_iam_policy_document.start_ec2_data_access.json
}

resource "aws_iam_role_policy_attachment" "start_ec2_data_access" {
  role       = data.aws_iam_role.data_access.name
  policy_arn = aws_iam_policy.start_ec2_data_access.arn
}
