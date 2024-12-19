# INFO - Table used for holding locks on environments for our environment cleanup job
#trivy:ignore:avd-aws-0024 ignore:avd-aws-0025 - point in time recovery not needed as transient data
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

  server_side_encryption {
    enabled     = true
    kms_key_arn = module.dynamodb_kms.eu_west_1_target_key_arn
  }

  lifecycle {
    prevent_destroy = false
  }
}

# INFO - Table used for working out which IP addresses should be blocked on our WAF
#trivy:ignore:avd-aws-0024 ignore:avd-aws-0025 - point in time recovery not needed as transient data
resource "aws_dynamodb_table" "blocked_ips_table" {
  name         = "BlockedIPs"
  billing_mode = "PAY_PER_REQUEST"
  hash_key     = "IP" # Set IP as the primary key

  attribute {
    name = "IP"
    type = "S"
  }

  attribute {
    name = "TimeoutExpiry"
    type = "N"
  }

  attribute {
    name = "BlockCounter"
    type = "N"
  }

  attribute {
    name = "UpdatedAt"
    type = "N"
  }

  global_secondary_index {
    name            = "TimeoutExpiryIndex"
    hash_key        = "TimeoutExpiry"
    projection_type = "ALL"
  }

  global_secondary_index {
    name            = "BlockCounterIndex"
    hash_key        = "BlockCounter"
    projection_type = "ALL"
  }

  global_secondary_index {
    name            = "UpdatedAtIndex"
    hash_key        = "UpdatedAt"
    projection_type = "ALL"
  }

  ttl {
    attribute_name = "ExpiresTTL"
    enabled        = true
  }

  server_side_encryption {
    enabled     = true
    kms_key_arn = module.dynamodb_kms.eu_west_1_target_key_arn
  }

  lifecycle {
    prevent_destroy = false
  }
}
