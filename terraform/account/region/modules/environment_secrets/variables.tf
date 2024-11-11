variable "environment" {
  description = "Name of the environment to create secrets for."
  type        = string
}

variable "secrets" {
  description = "List of secrets to create for the environment."
  type        = set(string)
}

variable "kms_key" {
  description = "Arn of the secret manager KMS key to use."
  type        = string
}

variable "tags" {
  description = "Tags to apply to secrets."
  type        = map(string)
}
