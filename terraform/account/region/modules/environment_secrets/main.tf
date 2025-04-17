resource "aws_secretsmanager_secret" "secret" {
  for_each = var.secrets

  name        = join("/", [var.environment, each.value])
  kms_key_id  = var.kms_key
  description = "${each.value} secret for ${var.environment}"
  tags        = var.tags
}

output "secret_arns" {
  description = "ARNs of the created Secrets Manager secrets"
  value = {
    for key, secret in aws_secretsmanager_secret.secret :
    key => secret.arn
  }
}
