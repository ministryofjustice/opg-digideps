#trivy:ignore:avd-aws-0104 - Currently needed in as no domain egress filtering
module "admin_service_security_group" {
  source      = "./modules/security_group"
  description = "Admin Service"
  rules       = local.admin_sg_rules
  name        = "admin-service"
  tags        = var.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}

locals {
  admin_sg_rules = {
    ecr     = local.common_sg_rules.ecr
    logs    = local.common_sg_rules.logs
    s3      = local.common_sg_rules.s3
    ssm     = local.common_sg_rules.ssm
    ecr_api = local.common_sg_rules.ecr_api
    secrets = local.common_sg_rules.secrets
    admin_elb_http = {
      port        = 80
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.admin_elb_security_group.id
    }
    front_cache = {
      port        = 6379
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = data.aws_security_group.cache_front_sg.id
    }
    api = {
      port        = 80
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_service_security_group.id
    }
    pdf = {
      port        = 8080
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.htmltopdf_security_group.id
    }
    notify = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
    mock_sirius_integration = {
      port        = 8080
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.mock_sirius_integration_security_group.id
    }
  }
}
