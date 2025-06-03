#Create the EC2 instance (gets the image from data sources in this case amazon_linux_2023)
resource "aws_instance" "ssm_ec2" {
  ami                         = data.aws_ami.amazon_linux_2023.id
  instance_type               = var.instance_type
  subnet_id                   = var.subnet_id
  vpc_security_group_ids      = [aws_security_group.ssm_instance_sg.id]
  iam_instance_profile        = aws_iam_instance_profile.ssm_profile.name
  user_data                   = templatefile("${path.module}/boot.sh.tpl", {})
  associate_public_ip_address = false

  tags = merge(var.tags, {
    Name = var.name
  })
}

resource "aws_security_group" "ssm_instance_sg" {
  name        = "${var.environment}-ssm-instance"
  description = "Security group for EC2 instance managed via SSM"
  vpc_id      = var.vpc_id

  egress {
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(var.tags, {
    Name = "ssm-instance"
  })
}


#Creates a new assumable role
resource "aws_iam_role" "ssm_role" {
  name               = "${var.name}-ssm-role"
  assume_role_policy = data.aws_iam_policy_document.ssm_assume_role.json
}

#Binds the new assuamble role above to the 'iam instance profile'
resource "aws_iam_instance_profile" "ssm_profile" {
  name = "${var.name}-instance-profile"
  role = aws_iam_role.ssm_role.name
}

#Sets AmazonSSMManagedInstanceCore to the new role
resource "aws_iam_role_policy_attachment" "ssm_core" {
  role       = aws_iam_role.ssm_role.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonSSMManagedInstanceCore"
}

#Allows Amazon into the ec2 service
data "aws_iam_policy_document" "ssm_assume_role" {
  statement {
    actions = ["sts:AssumeRole"]
    principals {
      type        = "Service"
      identifiers = ["ec2.amazonaws.com"]
    }
  }
}

#VPC Endpoints to allow Connectivity

resource "aws_vpc_endpoint" "ssm" {
  vpc_id             = var.vpc_id
  service_name       = "com.amazonaws.eu-west-1.ssm"
  vpc_endpoint_type  = "Interface"
  subnet_ids = [var.subnet_id]
  security_group_ids = [var.endpoint_sg_id]
}

resource "aws_vpc_endpoint" "ssmmessages" {
  vpc_id             = var.vpc_id
  service_name       = "com.amazonaws.eu-west-1.ssmmessages"
  vpc_endpoint_type  = "Interface"
  subnet_ids = [var.subnet_id]
  security_group_ids = [var.endpoint_sg_id]
}

resource "aws_vpc_endpoint" "ec2messages" {
  vpc_id             = var.vpc_id
  service_name       = "com.amazonaws.eu-west-1.ec2messages"
  vpc_endpoint_type  = "Interface"
  subnet_ids = [var.subnet_id]
  security_group_ids = [var.endpoint_sg_id]
}
