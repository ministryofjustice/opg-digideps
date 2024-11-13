locals {
  dr_backup_sg_rules = {
    ecr     = var.common_sg_rules.ecr
    logs    = var.common_sg_rules.logs
    s3      = var.common_sg_rules.s3
    ssm     = var.common_sg_rules.ssm
    ecr_api = var.common_sg_rules.ecr_api
    #trivy:ignore:avd-aws-0104 - Currently needed in as no domain egress filtering
    dr_backup = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
  }
}

module "dr_backup_security_group" {
  source      = "../security_group"
  name        = "backup-cross-account"
  description = "Cross Account Backup Service"
  rules       = local.dr_backup_sg_rules
  tags        = var.default_tags
  vpc_id      = var.aws_vpc_id
  environment = var.environment
}
