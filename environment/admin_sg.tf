# TODO: breakout to individual rules
resource "aws_security_group" "admin_service" {
  name_prefix = aws_ecs_task_definition.admin.family
  vpc_id      = data.aws_vpc.vpc.id

  lifecycle {
    create_before_destroy = true
  }

  tags = merge(
  local.default_tags,
  {
    "Name" = "admin"
  },
  )
}

locals {
  admin_sg_rules = merge(
    local.common_sg_rules,
    {
      pdf = {
        port = 80
        security_group_id = aws_security_group.wkhtmltopdf.id
      }
      api = {
        port = 443
        security_group_id = aws_security_group.api_task.id
      }
      cache = {
        port = 6379
        security_group_id = aws_security_group.admin_cache.id
      }
    }
  )
}

resource "aws_security_group_rule" "admin_task_in" {
  type = "ingress"
  protocol        = "tcp"
  from_port       = 443
  to_port         = 443
  security_group_id        = aws_security_group.admin_service.id
  source_security_group_id = aws_security_group.admin_elb.id
}

resource "aws_security_group_rule" "admin_task_out" {
  for_each = local.admin_sg_rules

  type                     = "egress"
  protocol                 = "tcp"
  from_port                = each.value.port
  to_port                  = each.value.port
  security_group_id        = aws_security_group.admin_service.id
  source_security_group_id = contains(keys(each.value), "security_group_id") ? each.value.security_group_id : null
  prefix_list_ids          = contains(keys(each.value), "prefix_list_id") ? [each.value.prefix_list_id] : null
  description              = each.key
}
