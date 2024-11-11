resource "aws_secretsmanager_secret" "secret" {
  for_each = var.secrets

  name        = join("/", [var.environment, each.value])
  kms_key_id  = var.kms_key
  description = "${each.value} secret for ${var.environment}"
  tags        = var.tags
}
