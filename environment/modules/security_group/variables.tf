variable "rules" {
  description = "A map of security group rules specifying inbound or outbound traffic."
  type = map(object({
    port        = number
    type        = string
    protocol    = string
    target_type = string
    target      = string
  }))
}

variable "name" {
  description = "The name of the security group."
  type        = string
}

variable "vpc_id" {
  description = "The ID of the VPC in which the security group will be created."
  type        = string
}

variable "tags" {
  description = "A map of tags to be assigned to the security group."
  type        = map(string)
}

variable "description" {
  description = "A description of the purpose of the security group."
  type        = string
}
