variable "tags" {
  description = "A map of tags to be applied to the ECS resources."
  type        = map(string)
}

variable "environment" {
  description = "The environment context for the ECS task."
  type        = string
}

variable "execution_role_arn" {
  description = "The ARN of the execution role used by ECS tasks."
  type        = string
}

variable "name" {
  description = "The name of the ECS task."
  type        = string
}

variable "container_definitions" {
  description = "The JSON-formatted container definitions for the ECS tasks."
  type        = string
}

variable "cluster_name" {
  description = "The name of the ECS cluster where the task will be deployed."
  type        = string
}

variable "subnet_ids" {
  description = "A list of subnet IDs in which to launch the ECS tasks."
  type        = list(string)
}

variable "task_role_arn" {
  description = "The ARN of the IAM role assumed by ECS tasks."
  type        = string
}

variable "security_group_id" {
  description = "The ID of the security group associated with the ECS tasks."
  type        = string
}

variable "override" {
  description = "A list of command line arguments to override the default command for the containers."
  type        = list(string)
  default     = []
}

variable "service_name" {
  description = "The name of the ECS service."
  type        = string
  default     = ""
}

variable "memory" {
  description = "The memory limit for the containers in megabytes."
  type        = number
  default     = 512
}

variable "cpu" {
  description = "The CPU units to allocate to the containers."
  type        = number
  default     = 256
}

variable "architecture" {
  description = "Architecture for the task."
  type        = string
  default     = "AMD64"
}

variable "os" {
  description = "Operating system for the task."
  type        = string
  default     = "LINUX"
}
