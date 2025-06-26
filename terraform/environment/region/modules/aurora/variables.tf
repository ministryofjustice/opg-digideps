variable "account_id" {
  description = "Account id to use."
  type        = string
}
variable "apply_immediately" {
  description = "Apply changes immediately."
  type        = bool
  default     = true
}
variable "aurora_serverless" {
  description = "Use serverless configuration."
  type        = bool
  default     = false
}
variable "auto_minor_version_upgrade" {
  description = "Auto upgrade minor versions."
  type        = bool
  default     = false
}

variable "availability_zones" {
  description = "A list of availability zones for the Aurora cluster."
  type        = list(string)
  default     = ["eu-west-1a", "eu-west-1b", "eu-west-1c"]
}

variable "backup_retention_period" {
  description = "The number of days to retain automated backups."
  type        = number
  default     = 14
}

variable "cluster_identifier" {
  description = "A unique identifier for the Aurora cluster."
  type        = string
}

variable "ca_cert_identifier" {
  description = "Identifier of the CA certificate for the DB instance."
  type        = string
}

variable "deletion_protection" {
  description = "Indicates whether deletion protection is enabled for the cluster."
  type        = bool
}

variable "db_subnet_group_name" {
  description = "The name of the DB subnet group."
  type        = string
}

variable "database_name" {
  description = "The name of the initial database to be created when the cluster is created."
  type        = string
}

variable "engine" {
  description = "The name of the database engine to be used for the Aurora cluster."
  type        = string
  default     = "aurora-postgresql"
}

variable "engine_version" {
  description = "The version of the database engine to be used."
  type        = string
  default     = "10"
}

variable "kms_key_id" {
  description = "The ARN of the Key Management Service (KMS) key to be used for encryption."
  type        = string
}

variable "master_username" {
  description = "The username for the master database user."
  type        = string
}

variable "master_password" {
  description = "The password for the master database user."
  type        = string
}

variable "instance_count" {
  description = "The number of instances to create for the Aurora cluster."
  type        = number
  default     = 1
}

variable "instance_class" {
  description = "The instance class to be used for the Aurora instances."
  type        = string
  default     = "db.t3.medium"
}

variable "publicly_accessible" {
  description = "Indicates whether the cluster can be accessed publicly."
  type        = bool
  default     = false
}

variable "tags" {
  description = "Default resource tags."
  type        = map(string)
}

variable "timeout_create" {
  description = "The maximum time to wait for resource creation."
  type        = string
  default     = "180m"
}

variable "timeout_update" {
  description = "The maximum time to wait for resource updates."
  type        = string
  default     = "90m"
}

variable "timeout_delete" {
  description = "The maximum time to wait for resource deletion."
  type        = string
  default     = "90m"
}

variable "skip_final_snapshot" {
  description = "Indicates whether a final DB snapshot is created before the cluster is deleted."
  type        = bool
  default     = false
}

variable "storage_encrypted" {
  description = "Indicates whether storage is encrypted."
  type        = bool
  default     = true
}

variable "vpc_security_group_ids" {
  description = "A list of security group IDs to associate with the cluster."
  type        = list(string)
}

variable "replication_source_identifier" {
  description = "The identifier of the source DB instance or DB cluster if this DB instance is a read replica."
  type        = string
  default     = ""
}

variable "iam_database_authentication_enabled" {
  description = "Indicates whether IAM database authentication is enabled."
  type        = bool
  default     = false
}

variable "log_group" {
  description = "The name of the CloudWatch Logs log group."
  type        = string
}

variable "db_cluster_parameter_group_name" {
  description = "Name of the DB cluster parameter group."
  type        = string
  default     = null
}
