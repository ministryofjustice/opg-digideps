locals {
  non-replication_workspaces = ["production02", "preproduction", "training", "integration", "development"]
  bucket_replication_status  = contains(local.non-replication_workspaces, local.environment) ? false : true
  long_expiry_workspaces     = ["production02", "development"]
  expiration_days            = contains(local.long_expiry_workspaces, local.environment) ? 730 : 14
  noncurrent_expiration_days = contains(local.long_expiry_workspaces, local.environment) ? 365 : 7
}

data "aws_region" "current" {}

// GET DEV REPLICATION BUCKET
data "aws_s3_bucket" "replication_bucket" {
  bucket   = "pa-uploads-branch-replication"
  provider = aws.development
}

// DATA SOURCE FOR DEFAULT KEY
data "aws_kms_alias" "source_default_key" {
  name = "alias/aws/s3"
}

module "pa_uploads" {
  source                               = "./modules/s3_bucket"
  account_name                         = local.account.name
  bucket_name                          = "pa-uploads-${local.environment}"
  force_destroy                        = local.account.force_destroy_bucket
  kms_key_id                           = aws_kms_key.s3.key_id
  environment_name                     = local.environment
  enable_lifecycle                     = true
  expiration_days                      = local.expiration_days
  non_current_expiration_days          = local.noncurrent_expiration_days
  replication_within_account           = local.bucket_replication_status
  replication_within_account_bucket    = data.aws_s3_bucket.replication_bucket.arn
  replication_to_backup                = local.account.s3_backup_replication
  replication_to_backup_account_bucket = "arn:aws:s3:::${local.account.name}.backup.digideps.opg.service.justice.gov.uk"
  replication_role_arn                 = aws_iam_role.backup_role.arn
  backup_kms_key_id                    = "arn:aws:kms:eu-west-1:${local.backup_account_id}:key/${local.account.s3_backup_kms_arn}"
  backup_account_id                    = local.backup_account_id

  providers = {
    aws = aws
  }
}
