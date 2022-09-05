variable "account_id" { type = string }
variable "apply_immediately" {}
variable "aurora_serverless" { default = false }
variable "auto_minor_version_upgrade" { default = false }
variable "availability_zones" { default = ["eu-west-1a", "eu-west-1b", "eu-west-1c"] }
variable "backup_retention_period" { default = 14 }
variable "cluster_identifier" {}
variable "deletion_protection" {}
variable "db_subnet_group_name" {}
variable "database_name" {}
variable "engine" { default = "aurora-postgresql" }
variable "engine_version" { default = "10" }
variable "kms_key_id" {}
variable "master_username" {}
variable "master_password" {}
variable "instance_count" { default = 1 }
variable "instance_class" { default = "db.t3.medium" }
variable "publicly_accessible" { default = false }
variable "tags" { description = "default resource tags" }
variable "timeout_create" { default = "180m" }
variable "timeout_update" { default = "90m" }
variable "timeout_delete" { default = "90m" }
variable "skip_final_snapshot" {}
variable "storage_encrypted" { default = true }
variable "vpc_security_group_ids" {}
variable "replication_source_identifier" {}
variable "log_group" {}
