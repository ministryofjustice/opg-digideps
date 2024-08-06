variable "fault_injection_simulator_role" {
  type        = any
  description = "IAM role that allows AWS FIS to make calls to other AWS services."
}

variable "ecs_cluster" {
  type        = string
  description = "Name of the ECS cluster to run the experiments on."
}

variable "account_name" {
  type        = string
  description = "Account name."
}

variable "environment" {
  type        = string
  description = "Environment identifier."
}
