# INFO - Table used for holding locks on environments for our environment cleanup job
resource "aws_dynamodb_table" "workspace_cleanup_table" {
  count        = var.account.name == "development" ? 1 : 0
  name         = "WorkspaceCleanup"
  billing_mode = "PAY_PER_REQUEST"
  hash_key     = "WorkspaceName"

  attribute {
    name = "WorkspaceName"
    type = "S"
  }

  ttl {
    attribute_name = "ExpiresTTL"
    enabled        = true
  }

  lifecycle {
    prevent_destroy = false
  }
}
