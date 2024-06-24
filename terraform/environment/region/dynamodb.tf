# INFO - Table used for holding locks on environments for our environment cleanup job
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

  ttl {
    attribute_name = "ExpiresTTL"
    enabled        = true
  }

  lifecycle {
    prevent_destroy = false
  }
}
