resource "aws_security_group" "api_task" {
  name_prefix = aws_ecs_task_definition.api.family
  vpc_id      = data.aws_vpc.vpc.id
  tags        = local.default_tags

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "api_https_admin_in" {
  type      = "ingress"
  protocol  = "tcp"
  from_port = 443
  to_port   = 443

  security_group_id        = aws_security_group.api_task.id
  source_security_group_id = aws_security_group.admin_service.id
}

resource "aws_security_group_rule" "api_https_front_in" {
  type      = "ingress"
  protocol  = "tcp"
  from_port = 443
  to_port   = 443

  security_group_id        = aws_security_group.api_task.id
  source_security_group_id = aws_security_group.front.id
}

locals {
  api_sg_rules = merge(
    local.common_sg_rules,
    {
      cache = {
        port              = 6379
        security_group_id = aws_security_group.api_cache.id
      },
      rds = {
        port              = 5432
        security_group_id = aws_security_group.api_rds.id
      }
    }
  )
}

resource "aws_security_group_rule" "api_task_out" {
  for_each = local.api_sg_rules

  type                     = "egress"
  protocol                 = "tcp"
  from_port                = each.value.port
  to_port                  = each.value.port
  security_group_id        = aws_security_group.api_task.id
  source_security_group_id = contains(keys(each.value), "security_group_id") ? each.value.security_group_id : null
  prefix_list_ids          = contains(keys(each.value), "prefix_list_id") ? [each.value.prefix_list_id] : null
  description              = each.key
}
