resource "aws_wafv2_web_acl" "main" {
  name        = "${var.account.name}-web-acl"
  description = "Managed rules"
  scope       = "REGIONAL"

  default_action {
    allow {}
  }
  rule {
    name     = "AWS-AWSManagedRulesPHPRuleSet"
    priority = 1

    override_action {
      none {}
    }

    statement {
      managed_rule_group_statement {
        name        = "AWSManagedRulesPHPRuleSet"
        vendor_name = "AWS"
      }
    }

    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "AWS-AWSManagedRulesPHPRuleSet"
      sampled_requests_enabled   = true
    }
  }

  rule {
    name     = "AWS-AWSManagedRulesKnownBadInputsRuleSet"
    priority = 10

    override_action {
      none {}
    }

    statement {
      managed_rule_group_statement {
        name        = "AWSManagedRulesKnownBadInputsRuleSet"
        vendor_name = "AWS"
      }
    }

    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "AWS-AWSManagedRulesKnownBadInputsRuleSet"
      sampled_requests_enabled   = true
    }
  }

  rule {
    name     = "AWS-AWSManagedRulesCommonRuleSet"
    priority = 15

    override_action {
      none {}
    }

    statement {
      managed_rule_group_statement {
        name        = "AWSManagedRulesCommonRuleSet"
        vendor_name = "AWS"

        rule_action_override {
          action_to_use {
            count {}
          }
          name = "SizeRestrictions_BODY"
        }

        rule_action_override {
          action_to_use {
            count {}
          }
          name = "CrossSiteScripting_BODY"
        }
      }
    }
    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "AWS-AWSManagedRulesCommonRuleSet"
      sampled_requests_enabled   = true
    }
  }

  rule {
    name     = "BlockSpecificIPs"
    priority = 20

    action {
      block {}
    }

    statement {
      ip_set_reference_statement {
        arn = aws_wafv2_ip_set.blocked_ips.arn
      }
    }

    visibility_config {
      sampled_requests_enabled   = true
      cloudwatch_metrics_enabled = true
      metric_name                = "BlockSpecificIPs"
    }
  }

  dynamic "rule" {
    for_each = var.account.name == "production" ? [1] : []
    content {
      name     = "RateLimitByIP"
      priority = 21

      action {
        block {}
      }

      statement {
        rate_based_statement {
          limit              = 200
          aggregate_key_type = "IP"
        }
      }

      visibility_config {
        cloudwatch_metrics_enabled = true
        metric_name                = "rateLimitRule"
        sampled_requests_enabled   = true
      }
    }
  }

  rule {
    name     = "AllowSpecificURIs"
    priority = 25

    action {
      allow {}
    }

    statement {
      regex_pattern_set_reference_statement {
        arn = aws_wafv2_regex_pattern_set.allow_uris.arn
        field_to_match {
          uri_path {}
        }
        text_transformation {
          priority = 0
          type     = "NONE"
        }
      }
    }

    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "AllowSpecificURIs"
      sampled_requests_enabled   = true
    }
  }

  rule {
    name     = "BlockSpecificURIs"
    priority = 30

    action {
      block {}
    }

    statement {
      regex_pattern_set_reference_statement {
        arn = aws_wafv2_regex_pattern_set.block_uris.arn
        field_to_match {
          uri_path {}
        }
        text_transformation {
          priority = 0
          type     = "NONE"
        }
      }
    }

    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "BlockSpecificURIs"
      sampled_requests_enabled   = true
    }
  }

  visibility_config {
    cloudwatch_metrics_enabled = true
    metric_name                = "${var.account.name}-web-acl"
    sampled_requests_enabled   = true
  }
}

resource "aws_wafv2_regex_pattern_set" "block_uris" {
  name        = "${var.account.name}-block-uris"
  description = "Regex pattern set for blocking specific public URIs"

  regular_expression {
    regex_string = "^/public/.*$"
  }

  scope = "REGIONAL"
}

resource "aws_wafv2_regex_pattern_set" "allow_uris" {
  name        = "${var.account.name}-allow-uris"
  description = "Regex pattern set for allowing specific public URIs"

  regular_expression {
    regex_string = "^/public/apple.*\\.png$"
  }

  regular_expression {
    regex_string = "^/public/favicon.ico$"
  }

  regular_expression {
    regex_string = "^/public/opengraph-image.png$"
  }

  regular_expression {
    regex_string = "^/public/assets/.*\\.(js|js.map|txt|css|css.map|woff|woff2)$"
  }

  regular_expression {
    regex_string = "^/public/images/.*\\.png$"
  }

  scope = "REGIONAL"
}

resource "aws_wafv2_web_acl_logging_configuration" "main" {
  log_destination_configs = [aws_cloudwatch_log_group.waf_web_acl.arn]
  resource_arn            = aws_wafv2_web_acl.main.arn
}

resource "aws_cloudwatch_log_group" "waf_web_acl" {
  name              = "aws-waf-logs-${var.account.name}"
  retention_in_days = 120
  kms_key_id        = aws_kms_key.waf_cloudwatch_log_encryption.arn
  tags = {
    "Name" = "${var.account.name}-web-acl"
  }
}

resource "aws_cloudwatch_log_anomaly_detector" "waf_web_acl" {
  detector_name           = "aws-waf-logs"
  log_group_arn_list      = [aws_cloudwatch_log_group.waf_web_acl.arn]
  anomaly_visibility_time = 14
  evaluation_frequency    = "TEN_MIN"
  enabled                 = "true"
  kms_key_id              = module.anomaly_kms.eu_west_1_target_key_arn
}

resource "aws_kms_key" "waf_cloudwatch_log_encryption" {
  description             = "AWS WAF Cloudwatch encryption ${var.account.name}"
  deletion_window_in_days = 10
  enable_key_rotation     = true
  policy                  = data.aws_iam_policy_document.waf_cloudwatch_log_encryption_kms.json
}

resource "aws_kms_alias" "waf_cloudwatch_log_encryption" {
  name          = "alias/waf_cloudwatch_log_encryption"
  target_key_id = aws_kms_key.waf_cloudwatch_log_encryption.key_id
}

# See the following link for further information
# https://docs.aws.amazon.com/kms/latest/developerguide/key-policies.html
data "aws_iam_policy_document" "waf_cloudwatch_log_encryption_kms" {
  statement {
    sid       = "Enable Root account permissions on Key"
    effect    = "Allow"
    actions   = ["kms:*"]
    resources = ["*"]

    principals {
      type = "AWS"
      identifiers = [
        "arn:aws:iam::${data.aws_caller_identity.current.account_id}:root",
      ]
    }
  }

  statement {
    sid       = "Allow Key to be used for Encryption"
    effect    = "Allow"
    resources = ["*"]
    actions = [
      "kms:Encrypt",
      "kms:Decrypt",
      "kms:ReEncrypt*",
      "kms:GenerateDataKey*",
      "kms:DescribeKey",
    ]

    principals {
      type = "Service"
      identifiers = [
        "logs.${data.aws_region.current.name}.amazonaws.com",
        "events.amazonaws.com"
      ]
    }
  }

  statement {
    sid       = "Key Administrator"
    effect    = "Allow"
    resources = ["*"]
    actions = [
      "kms:Create*",
      "kms:Describe*",
      "kms:Enable*",
      "kms:List*",
      "kms:Put*",
      "kms:Update*",
      "kms:Revoke*",
      "kms:Disable*",
      "kms:Get*",
      "kms:Delete*",
      "kms:TagResource",
      "kms:UntagResource",
      "kms:ScheduleKeyDeletion",
      "kms:CancelKeyDeletion"
    ]

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:role/breakglass"]
    }
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

  lifecycle {
    ignore_changes = [
      addresses,
      description
    ]
  }
}
