data "aws_iam_role" "sync" {
  name = "sync"
}

data "aws_s3_bucket" "backup" {
  bucket   = "backup.complete-deputy-report.service.gov.uk"
  provider = "aws.management"
}

output "Tasks" {
  value = {
    backup  = module.backup.render
    restore = module.restore.render
  }
}

output "Role" {
  value = "arn:aws:iam::${local.account["account_id"]}:role/${var.DEFAULT_ROLE}"
}


