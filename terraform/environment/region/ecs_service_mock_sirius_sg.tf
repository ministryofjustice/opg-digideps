locals {
  mock_sirius_integration_sg_rules = {
    logs = local.common_sg_rules.logs
    front = {
      port        = 8080
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.front_service_security_group.id
    }
    admin = {
      port        = 8080
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.admin_service_security_group.id
    }
    sirius_file_sync = {
      port        = 8080
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.sirius_files_sync_service_security_group.id
    }
    registry_docker_io = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
  }
}

#trivy:ignore:avd-aws-0104 - Currently needed in as no domain egress filtering
module "mock_sirius_integration_security_group" {
  source      = "./modules/security_group"
  description = "Mock Sirius Integration"
  rules       = local.mock_sirius_integration_sg_rules
  name        = "mock-sirius-integration"
  tags        = var.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}
