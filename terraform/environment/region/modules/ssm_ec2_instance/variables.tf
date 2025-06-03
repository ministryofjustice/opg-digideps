variable "instance_type" {
  description = "EC2 instance type"
  type        = string
}

variable "subnet_id" {
  description = "Subnet ID for the EC2 instance"
  type        = string
}

variable "security_group_ids" {
  description = "List of security group IDs"
  type        = list(string)
}

variable "name" {
  description = "Name tag for the EC2 instance"
  type        = string
}

variable "tags" {
  description = "Tags to apply to resources"
  type        = map(string)
}

variable "vpc_id" {
  description = "The VPC ID where the EC2 and endpoints are created"
  type        = string
}

variable "private_subnet_ids" {
  description = "List of private subnet IDs for VPC endpoints"
  type        = list(string)
}

variable "endpoint_sg_id" {
  description = "Security group ID that allows access to VPC endpoints"
  type        = string
}
