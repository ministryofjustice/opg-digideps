locals {
  scan_sg_rules = {
    logs = local.common_sg_rules_new.logs,
    registry_docker_io = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
    db_local_clamav_net = {
      port        = 80
      type        = "egress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
    front = {
      port        = 8080
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.front_service_security_group.id
    }
  }
}

module "scan_security_group" {
  source = "./security_group"
  rules  = local.scan_sg_rules
  name   = aws_ecs_task_definition.scan.family
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}
