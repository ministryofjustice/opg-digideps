resource "aws_secretsmanager_secret" "secret" {
  for_each = var.secrets

  name        = join("/", [var.environment, each.value])
  description = "${each.value} secret for ${var.environment}"
  tags        = var.tags
}
