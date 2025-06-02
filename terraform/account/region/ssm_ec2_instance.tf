module "ssm_ec2_instance" {
  source = "./modules/ssm_ec2_instance"

  instance_type      = "t3.micro"
  subnet_id          = aws_subnet.private[0].id
  security_group_ids = [aws_security_group.custom_sql_query.id]
  name               = "ssm-instance"
  tags               = local.common_tags
}