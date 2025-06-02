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
