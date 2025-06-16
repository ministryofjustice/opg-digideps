output "ssm_instance_sg_id" {
  description = "Security Group ID for the SSM EC2 Instance"
  value       = aws_security_group.ssm_instance_sg.id
}