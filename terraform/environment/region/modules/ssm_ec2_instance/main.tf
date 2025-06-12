#Create the EC2 instance (gets the image from data sources in this case amazon_linux_2023)
resource "aws_instance" "ssm_ec2" {
  ami                         = data.aws_ami.amazon_linux_2023.id
  instance_type               = var.instance_type
  subnet_id                   = var.subnet_id
  vpc_security_group_ids      = [aws_security_group.ssm_instance_sg.id]
  iam_instance_profile        = aws_iam_instance_profile.instance_profile.name
  user_data_base64            = base64encode(file("${path.module}/boot.sh"))
  associate_public_ip_address = false

  tags = merge(var.tags, {
    Name = "ssm-${var.name}-instance"
  })
}

resource "aws_security_group" "ssm_instance_sg" {
  name        = "${var.environment}-${var.name}-ssm-instance"
  description = "${var.name}-ssm-instance - ${var.environment}"
  vpc_id      = var.vpc_id

  tags = merge(var.tags, {
    Name = "ssm-${var.name}-instance"
  })
}

resource "aws_security_group_rule" "postgres_ssm_egress" {
  description       = "${var.name}-ssm-instance-postgres - ${var.environment}"
  type              = "egress"
  from_port         = 5432
  to_port           = 5432
  protocol          = "tcp"
  cidr_blocks       = ["0.0.0.0/0"]
  security_group_id = aws_security_group.ssm_instance_sg.id
}

resource "aws_security_group_rule" "https_ssm_egress" {
  description       = "${var.name}-ssm-instance-https - ${var.environment}"
  type              = "egress"
  from_port         = 443
  to_port           = 443
  protocol          = "tcp"
  cidr_blocks       = ["0.0.0.0/0"]
  security_group_id = aws_security_group.ssm_instance_sg.id
}

data "aws_iam_role" "ssm_role" {
  name = var.name
}

resource "aws_iam_instance_profile" "instance_profile" {
  name = "${var.name}-instance-profile"
  role = data.aws_iam_role.ssm_role.name
}

resource "aws_iam_role_policy_attachment" "ssm_core_role_for_instance_profile" {
  count      = var.name == "operator" ? 1 : 0
  role       = data.aws_iam_role.ssm_role.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonSSMManagedInstanceCore"
}
