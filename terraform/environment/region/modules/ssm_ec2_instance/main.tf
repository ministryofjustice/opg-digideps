#Create the EC2 instance (gets the image from data sources in this case amazon_linux_2023)
resource "aws_instance" "ssm_ec2" {
  ami                         = data.aws_ami.amazon_linux_2023.id
  instance_type               = var.instance_type
  subnet_id                   = var.subnet_id
  vpc_security_group_ids      = [aws_security_group.ssm_instance_sg.id]
  iam_instance_profile        = aws_iam_instance_profile.operator_profile.name
  user_data                   = templatefile("${path.module}/boot.sh.tpl", {})
  associate_public_ip_address = false

  tags = merge(var.tags, {
    Name = var.name
  })
}

resource "aws_security_group" "ssm_instance_sg" {
  name        = "${var.environment}-ssm-instance"
  description = "SSM EC2 instance SG"
  vpc_id      = var.vpc_id

  tags = merge(var.tags, {
    Name = "ssm-instance"
  })
}

resource "aws_security_group_rule" "postgres_ssm_egress" {
  type              = "egress"
  from_port         = 5432
  to_port           = 5432
  protocol          = "tcp"
  cidr_blocks       = ["0.0.0.0/0"]
  security_group_id = aws_security_group.ssm_instance_sg.id
}

resource "aws_security_group_rule" "https_ssm_egress" {
  type              = "egress"
  from_port         = 443
  to_port           = 443
  protocol          = "tcp"
  cidr_blocks       = ["0.0.0.0/0"]
  security_group_id = aws_security_group.ssm_instance_sg.id
}

resource "aws_security_group" "ssm_endpoint_sg" {
  name        = "${var.environment}-ssm-endpoints"
  description = "VPC endpoint SG"
  vpc_id      = var.vpc_id

  ingress {
    from_port       = 443
    to_port         = 443
    protocol        = "tcp"
    security_groups = [aws_security_group.ssm_instance_sg.id]
  }

  tags = merge(var.tags, {
    Name = "ssm-endpoint"
  })
}


#Creates a new assumable role
# resource "aws_iam_role" "ssm_role" {
#   name               = "${var.name}-ssm-role"
#   assume_role_policy = data.aws_iam_policy_document.ssm_assume_role.json
# }

data "aws_iam_role" "operator" {
  name = "operator"
}

#Binds the new assuamble role above to the 'iam instance profile'
resource "aws_iam_instance_profile" "operator_profile" {
  name = "${var.name}-instance-profile"
  role = data.aws_iam_role.operator.name
}

#Sets AmazonSSMManagedInstanceCore to the new role
# resource "aws_iam_role_policy_attachment" "ssm_core" {
#   role       = aws_iam_role.ssm_role.name
#   policy_arn = "arn:aws:iam::aws:policy/AmazonSSMManagedInstanceCore"
# }

#Allows Amazon into the ec2 service
# data "aws_iam_policy_document" "ssm_assume_role" {
#   statement {
#     actions = ["sts:AssumeRole"]
#     principals {
#       type        = "Service"
#       identifiers = ["ec2.amazonaws.com"]
#     }
#   }
# }


locals {
  user_data = <<EOF
#cloud-config
repo_update: true
repo_upgrade: all

packages:
- postgresql15.x86_64

runcmd:
- sudo echo "Running"
EOF
}
