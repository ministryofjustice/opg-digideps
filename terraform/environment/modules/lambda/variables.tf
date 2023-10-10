variable "environment" {
  description = "The environment lambda is being deployed to."
  type        = string
}

variable "aws_subnet_ids" {
  description = "A list of subnet ids for vpc connectivity."
  type        = list(string)
}

variable "tags" {
  description = "A map of tags to use."
  type        = map(string)
  default     = {}
}

variable "rest_api" {
  default = null
}

variable "account" {
  description = "A map that defines account variables."
}

variable "target_environment" {
  description = "The sirius environment."
  type        = string
  default     = null
}

variable "memory" {
  description = "The memory to use."
  type        = number
  default     = null
}

variable "image_uri" {
  description = "The image uri in ECR."
  type        = string
  default     = null
}

variable "ecr_arn" {
  description = "The ECR arn for lambda image."
  type        = string
  default     = null
}

variable "description" {
  description = "Description of your Lambda Function (or Layer)"
  type        = string
  default     = null
}

variable "lambda_role_policy_document" {
  description = "The policy JSON for the lambda IAM role. This policy JSON is merged with Logging and ECR access included in the module."
  type        = string
  default     = null
}

variable "environment_variables" {
  description = "A map that defines environment variables for the Lambda Function."
  type        = map(string)
  default     = {}
}

variable "lambda_name" {
  description = "A unique name for your Lambda Function"
  type        = string
}

variable "package_type" {
  description = "The Lambda deployment package type."
  type        = string
  default     = "Image"
}

variable "timeout" {
  description = "The amount of time your Lambda Function has to run in seconds."
  type        = number
  default     = 30
}

variable "working_directory" {
  description = "The working directory for the docker image."
  type        = string
  default     = null
}

variable "api_version" {
  description = "The version deployed."
  type        = string
  default     = "v2"
}

variable "api_gateway_access" {
  description = "If lambda is to be called by API gateway."
  type        = bool
  default     = false
}

variable "vpc_id" {
  description = "VPC ID"
  type        = string
  default     = null
}

variable "secrets" {
  description = "Secrets lambda has access to"
  type        = list(string)
  default     = []
}

variable "logs_kms_key_arn" {
  description = "User managed KMS key for log encryption"
  type        = string
}
