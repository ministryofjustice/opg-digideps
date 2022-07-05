locals {
  dr_backup_sg_rules = {
    ecr     = var.common_sg_rules.ecr
    logs    = var.common_sg_rules.logs
    s3      = var.common_sg_rules.s3
    ssm     = var.common_sg_rules.ssm
    ecr_api = var.common_sg_rules.ecr_api
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
  description = "DR backup service"
  rules       = local.dr_backup_sg_rules
  name        = "dr-backup"
  tags        = var.default_tags
  vpc_id      = var.aws_vpc_id
}
