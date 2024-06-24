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

  ttl {
    attribute_name = "ExpiresTTL"
    enabled        = true
  }

  lifecycle {
    prevent_destroy = false
  }
}

# WAF IP set
resource "aws_wafv2_ip_set" "blocked_ips" {
  name               = "BlockedIPs"
  description        = "IPs to block using the WAF"
  scope              = "REGIONAL"
  ip_address_version = "IPV4"
  addresses          = []
  tags               = var.default_tags
}
