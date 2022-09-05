variable "bucket_name" {
  description = "Name of the bucket."
}

variable "account_name" {
  description = "Account friendly that the current environment resides in."
  type        = string
}

variable "acl" {
  description = "The canned ACL to apply. Defaults to private. (https://docs.aws.amazon.com/AmazonS3/latest/dev/acl-overview.html#canned-acl)"
  default     = "private"
}

variable "force_destroy" {
  description = " A boolean that indicates all objects should be deleted from the bucket so that the bucket can be destroyed without error. These objects are not recoverable."
  default     = false
}

variable "block_public_acls" {
  description = "Whether Amazon S3 should block public ACLs for this bucket."
  default     = true
}

variable "block_public_policy" {
  description = "Whether Amazon S3 should block public bucket policies for this bucket."
  default     = true
}

variable "ignore_public_acls" {
  description = "Whether Amazon S3 should ignore public ACLs for this bucket."
  default     = true
}

variable "restrict_public_buckets" {
  description = "Whether Amazon S3 should restrict public bucket policies for this bucket. Enabling this setting does not affect the previously stored bucket policy, except that public and cross-account access within the public bucket policy, including non-public delegation to specific accounts, is blocked."
  default     = true
}

variable "kms_key" {
  description = "kms key to encrypt s3 bucket with"
}

variable "enable_lifecycle" {
  description = "Set to true to delete items in the bucket after 6 months."
  default     = false
}

variable "expiration_days" {
  description = "Number of days to expire the items in the bucket. Only takes effect when enable_lifecycle is set to true."
  default     = "1825"
}

variable "versioning_enabled" {
  description = "Whether versioning is enabled on the bucket."
  default     = false
}

locals {
  environment = split("_", terraform.workspace)[0]
}
