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
  description = "SSM EC2 instance SG"
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
  subnet_ids         = [var.subnet_id]
  security_group_ids = [aws_security_group.ssm_endpoint_sg.id]
}

resource "aws_vpc_endpoint" "ssmmessages" {
  vpc_id             = var.vpc_id
  service_name       = "com.amazonaws.eu-west-1.ssmmessages"
  vpc_endpoint_type  = "Interface"
  subnet_ids         = [var.subnet_id]
  security_group_ids = [aws_security_group.ssm_endpoint_sg.id]
}

resource "aws_vpc_endpoint" "ec2messages" {
  vpc_id             = var.vpc_id
  service_name       = "com.amazonaws.eu-west-1.ec2messages"
  vpc_endpoint_type  = "Interface"
  subnet_ids         = [var.subnet_id]
  security_group_ids = [aws_security_group.ssm_endpoint_sg.id]
}

# Allowing Internet Access

resource "aws_subnet" "public_subnet" {
  vpc_id                  = var.vpc_id
  cidr_block              = "0.0.0.0/0"
  availability_zone       = "eu-west-1a"
  map_public_ip_on_launch = true

  tags = {
    Name = "${var.environment}-public-subnet"
  }
}

resource "aws_internet_gateway" "igw" {
  vpc_id = var.vpc_id

  tags = {
    Name = "${var.environment}-igw"
  }
}

resource "aws_eip" "nat_eip" {
  vpc = true
}

resource "aws_nat_gateway" "nat" {
  allocation_id = aws_eip.nat_eip.id
  subnet_id     = aws_subnet.public_subnet.id

  tags = {
    Name = "${var.environment}-nat"
  }

  depends_on = [aws_internet_gateway.igw]
}

resource "aws_route_table" "private_rt" {
  vpc_id = var.vpc_id

  route {
    cidr_block     = "0.0.0.0/0"
    nat_gateway_id = aws_nat_gateway.nat.id
  }

  tags = {
    Name = "${var.environment}-private-rt"
  }
}

resource "aws_route_table_association" "private_subnet_association" {
  subnet_id      = var.subnet_id
  route_table_id = aws_route_table.private_rt.id
}

 