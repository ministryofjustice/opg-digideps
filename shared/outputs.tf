output "cloud9_env_id" {
  value = aws_cloud9_environment_ec2.shared.id
}

output "development_replication_bucket_arn" {
  value = aws_s3_bucket.pa_uploads_branch_replication[0].arn
}
