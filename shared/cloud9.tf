resource "aws_cloud9_environment_ec2" "shared" {
  instance_type               = "t2.micro"
  name                        = "shared-env"
  automatic_stop_time_minutes = 20
  description                 = "Shared Cloud9 instance to be used by all devs"
  subnet_id                   = aws_subnet.private[0].id
}


//name - (Required) The name of the environment.
//instance_type - (Required) The type of instance to connect to the environment, e.g. t2.micro.
//automatic_stop_time_minutes - (Optional) The number of minutes until the running instance is shut down after the environment has last been used.
//description - (Optional) The description of the environment.
//owner_arn - (Optional) The ARN of the environment owner. This can be ARN of any AWS IAM principal. Defaults to the environment's creator.
//subnet_id - (Optional) The ID of the subnet in Amazon VPC that AWS Cloud9 will use to communicate with the Amazon EC2 instance.
