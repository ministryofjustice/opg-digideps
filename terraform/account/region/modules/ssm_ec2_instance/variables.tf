variable "instance_type" {
  description = "EC2 instance type"
  type        = string
}

variable "subnet_id" {
  description = "Subnet ID for the EC2 instance"
  type        = string
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

# variable "kms_key_id" {
#   description = "The KMS key ID for Cloudwatch logs"
#   type = string
# }

variable "instance_profile" {
  description = "The Instance Profile used by the EC2S"
  type = string
}