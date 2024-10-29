variable "kms_key_policy" {
  type        = string
  description = "Policy json to attach to the KMS key and replica key."
}

variable "encrypted_resource" {
  type        = string
  description = "The resource that will be encrypted by the KMS key."
}

variable "kms_key_alias_name" {
  type        = string
  description = "The alias name for the KMS key. Module will prefix alias/ to the name."
}

variable "enable_key_rotation" {
  type        = bool
  description = "Whether to enable key rotation for the KMS key."
  default     = true
}

variable "enable_multi_region" {
  type        = bool
  description = "Whether to enable multi-region replication for the KMS key."
  default     = true
}

variable "deletion_window_in_days" {
  type        = number
  description = "The number of days to retain the KMS key before it is deleted."
  default     = 10
}
