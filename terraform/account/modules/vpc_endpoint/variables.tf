variable "subnet_ids" {
  description = "List of subnet ids to use"
  default     = null
}

variable "vpc" {
  description = "VPC to use"
}

variable "region" {
  description = "Region to use"
  type        = string
}

variable "service" {
  description = "Service to use as the service name"
  type        = string
}

variable "service_short_title" {
  description = "Title for differentiating the service"
  type        = string
}

variable "tags" {
  description = "A map of tags to use"
  type        = map(string)
  default     = {}
}
