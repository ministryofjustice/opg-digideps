variable "default_tags" {
  description = "Default tags to be applied to resources."
  type        = map(string)
}

variable "environment" {
  description = "The environment in which the resources are being deployed."
  type        = string
}

variable "images" {
  description = "A map of image names and corresponding image IDs."
  type        = map(string)
}

variable "execution_role_arn" {
  description = "The role assumed by resources during execution."
  type        = string
}

variable "backup_account_id" {
  description = "The AWS account ID to store backups."
  type        = string
}

variable "aws_ecs_cluster_arn" {
  description = "The ARN of the ECS cluster."
  type        = string
}

variable "aws_subnet_ids" {
  description = "A list of AWS subnet IDs."
  type        = list(string)
}

variable "account_id" {
  description = "The AWS account name or identifier."
  type        = string
}

variable "db" {
  description = "The database configuration."
  type        = map(any)
}

variable "common_sg_rules" {
  description = "Common security group rules."
  type = map(object({
    port        = number
    type        = string
    protocol    = string
    target_type = string
    target      = string
  }))
}

variable "aws_vpc_id" {
  description = "The ID of the AWS VPC."
  type        = string
}

variable "task_role_assume_policy" {
  type        = any
  description = "The IAM policy document for task role assumption."
}

variable "log_retention" {
  description = "The duration for which logs are retained."
  type        = string
}

variable "task_runner_arn" {
  description = "The ARN of the task runner."
  type        = string
}

variable "cross_account_role_name" {
  description = "The name of the cross-account role."
  type        = string
}

variable "logs_kms_key_arn" {
  description = "The ARN of the KMS key used for logs encryption."
  type        = string
}
