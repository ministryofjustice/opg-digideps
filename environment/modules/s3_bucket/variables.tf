variable "bucket_name" {
  description = "Name of the bucket."
  type        = string
}

variable "account_name" {
  description = "Account friendly that the current environment resides in."
  type        = string
}

variable "force_destroy" {
  description = "A boolean that indicates all objects should be deleted from the bucket so that the bucket can be destroyed without error. These objects are not recoverable."
  type        = bool
  default     = false
}

variable "block_public_acls" {
  description = "Whether Amazon S3 should block public ACLs for this bucket."
  type        = bool
  default     = true
}

variable "block_public_policy" {
  description = "Whether Amazon S3 should block public bucket policies for this bucket."
  type        = bool
  default     = true
}

variable "ignore_public_acls" {
  description = "Whether Amazon S3 should ignore public ACLs for this bucket."
  type        = bool
  default     = true
}

variable "restrict_public_buckets" {
  description = "Whether Amazon S3 should restrict public bucket policies for this bucket. Enabling this setting does not affect the previously stored bucket policy, except that public and cross-account access within the public bucket policy, including non-public delegation to specific accounts, is blocked."
  type        = bool
  default     = true
}

variable "kms_key_id" {
  description = "KMS key to encrypt s3 bucket with"
}

variable "enable_lifecycle" {
  description = "Delete items in the bucket after 6 months if enabled."
  type        = bool
  default     = false
}

variable "expiration_days" {
  description = "Number of days to expire the items in the bucket. Only takes effect when enable_lifecycle is set to true."
  type        = string
  default     = "1825"
}

variable "non_current_expiration_days" {
  description = "Lifecycle expiration days for non current version"
  type        = string
  default     = "365"
}

variable "versioning_enabled" {
  description = "Whether versioning is enabled on the bucket."
  type        = bool
  default     = false
}

variable "environment_name" {
  description = "Environment name"
  type        = string
}

variable "replication_to_backup" {
  description = "Replication is enabled for backups"
  type        = bool
  default     = false
}

variable "replication_role_arn" {
  description = "Role to use for replication"
  type        = string
  default     = null
}

variable "backup_kms_key_id" {
  description = "Backup replication KMS key"
  type        = string
  default     = null
}

variable "replication_within_account_bucket" {
  description = "Replication within account bucket"
  type        = string
  default     = null
}

variable "replication_to_backup_account_bucket" {
  description = "Replication to backup account bucket"
  type        = string
  default     = null
}

variable "backup_account_id" {
  description = "Backup replication account id"
  type        = string
  default     = null
}

variable "replication_within_account" {
  description = "Replication within account"
  type        = bool
  default     = false
}
