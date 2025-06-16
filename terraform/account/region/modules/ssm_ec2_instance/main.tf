#Create the EC2 instance (gets the image from data sources in this case amazon_linux_2023)
resource "aws_instance" "ssm_ec2" {
  ami                         = data.aws_ami.amazon_linux_2023.id
  instance_type               = var.instance_type
  subnet_id                   = var.subnet_id
  vpc_security_group_ids      = [aws_security_group.ssm_instance_sg.id]
  iam_instance_profile        = var.instance_profile
  user_data_base64            = base64encode(file("${path.module}/boot.sh"))
  associate_public_ip_address = false

  tags = merge(var.tags, {
    Name = "ssm-${var.name}-instance"
  })
}

resource "aws_security_group" "ssm_instance_sg" {
  name        = "${var.name}-ssm-instance"
  description = "${var.name}-ssm-instance"
  vpc_id      = var.vpc_id

  tags = merge(var.tags, {
    Name = "ssm-${var.name}-instance"
  })
}

resource "aws_security_group_rule" "https_ssm_egress" {
  description       = "${var.name}-ssm-instance-https"
  type              = "egress"
  from_port         = 443
  to_port           = 443
  protocol          = "tcp"
  cidr_blocks       = ["0.0.0.0/0"]
  security_group_id = aws_security_group.ssm_instance_sg.id
}

# data "aws_iam_role" "ssm_role" {
#   name = var.name
# }

# resource "aws_iam_instance_profile" "instance_profile" {
#   name = "${var.name}-instance-profile"
#   role = data.aws_iam_role.ssm_role.name
# }

# Logging

# resource "aws_cloudwatch_log_group" "ssm_session" {
#   name              = "${var.name}-ssm-session-logs"
#   retention_in_days = 180
#   kms_key_id        = var.kms_key_id

#   tags = merge(var.tags, {
#     Name = "${var.name}-ssm-session-logs"
#   })
# }

# resource "aws_ssm_document" "ssm_session_logger" {
#   name          = "${var.name}-SSM-SessionLogger"
#   document_type = "Session"

#   content = jsonencode({
#     schemaVersion = "1.0",
#     description   = "Enable session logging to CWL",
#     sessionType   = "Standard_Stream",
#     inputs = {
#       cloudWatchLogGroupName      = aws_cloudwatch_log_group.ssm_session.name,
#       cloudWatchEncryptionEnabled = true
#     }
#   })

#   tags = merge(var.tags, {
#     Name = "${var.name}-SSM-SessionLogger"
#   })
# }