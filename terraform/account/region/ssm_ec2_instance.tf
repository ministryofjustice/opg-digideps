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

resource "aws_iam_role_policy_attachment" "ssm_core_role_for_instance_profile" {
  role       = data.aws_iam_role.operator.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonSSMManagedInstanceCore"
}

resource "aws_iam_policy" "allow_start_stop_ssm_operator_instances" {
  name        = "AllowStartStopSSMOperatorInstances"
  description = "Allow start/stop of the ssm-operator-instance"
  policy = jsonencode({
    Version = "2012-10-17",
    Statement = [
      {
        Sid    = "AllowStartStopSpecificInstance"
        Effect = "Allow"
        Action = [
          "ec2:StartInstances",
          "ec2:StopInstances"
        ]
        Resource = module.ssm_ec2_instance_operator.ssm_instance_arn
      }
    ]
  })
}

resource "aws_iam_role_policy_attachment" "attach_operator_policy" {
  role       = data.aws_iam_role.operator.name
  policy_arn = aws_iam_policy.allow_start_stop_ssm_operator_instances.arn
}
