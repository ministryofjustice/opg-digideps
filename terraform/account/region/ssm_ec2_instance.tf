module "ssm_ec2_instance_operator" {
  source = "./modules/ssm_ec2_instance"

  instance_type = "t3.micro"
  subnet_id     = aws_subnet.private[0].id
  name          = "operator"
  tags          = var.default_tags
  //kms_key_id    = module.logs_kms.eu_west_1_target_key_arn
  instance_profile = data.aws_iam_instance_profile.operator.name

  vpc_id = aws_vpc.main.id
}

data "aws_iam_instance_profile" "operator" {
  name = "operator"
}

data "aws_iam_role" "operator" {
  name = "operator"
}

# SSM Core Statements

resource "aws_iam_role_policy_attachment" "ssm_core_role_policy_document" {
  role       = data.aws_iam_role.operator.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonSSMManagedInstanceCore"
}

# Start EC2 Statements

data "aws_iam_policy_document" "start_ec2" {
  statement {
    sid    = "AllowStartStopSpecificInstance"
    effect = "Allow"

    actions = [
      "ec2:StartInstances",
      "ec2:StopInstances"
    ]

    resources = [module.ssm_ec2_instance_operator.ssm_instance_arn]
  }
}

resource "aws_iam_policy" "start_ec2" {
  name   = "operator-ssm-policy"
  policy = data.aws_iam_policy_document.start_ec2.json
}

resource "aws_iam_role_policy_attachment" "start_ec2" {
  role       = data.aws_iam_role.operator.name
  policy_arn = aws_iam_policy.start_ec2.arn
}
