data "aws_wafv2_web_acl" "main" {
  name  = "${var.account.name}-web-acl"
  scope = "REGIONAL"
}

resource "aws_wafv2_web_acl_association" "admin" {
  count        = var.account.associate_alb_with_waf_web_acl_enabled ? 1 : 0
  resource_arn = aws_lb.admin.arn
  web_acl_arn  = data.aws_wafv2_web_acl.main.arn
}

resource "aws_wafv2_web_acl_association" "front" {
  count        = var.account.associate_alb_with_waf_web_acl_enabled ? 1 : 0
  resource_arn = aws_lb.front.arn
  web_acl_arn  = data.aws_wafv2_web_acl.main.arn
}

resource "aws_shield_protection" "admin_alb_protection" {
  name         = "admin-alb-protection-${local.environment}"
  resource_arn = aws_lb.admin.arn

  tags = var.default_tags
}

resource "aws_shield_protection_health_check_association" "admin" {
  health_check_arn     = var.r53_hc_admin.arn
  shield_protection_id = aws_shield_protection.admin_alb_protection.id
}

resource "aws_shield_protection" "front_alb_protection" {
  name         = "front-alb-protection-${local.environment}"
  resource_arn = aws_lb.front.arn

  tags = var.default_tags
}

resource "aws_shield_protection_health_check_association" "front" {
  health_check_arn     = var.r53_hc_front.arn
  shield_protection_id = aws_shield_protection.front_alb_protection.id
}
