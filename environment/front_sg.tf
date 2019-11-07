# TODO: switch to name prefix
resource "aws_security_group" "front" {
  name        = "front-${local.environment}"
  description = "frontend client access for ${local.environment}"
  vpc_id      = data.aws_vpc.vpc.id
  tags = merge(
    local.default_tags,
    {
      "Name" = "front"
    },
  )

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "front_task_in" {
  type                     = "ingress"
  protocol                 = "tcp"
  from_port                = 443
  to_port                  = 443
  security_group_id        = aws_security_group.front.id
  source_security_group_id = aws_security_group.front_lb.id
}

data "aws_vpc_endpoint" "ecr_endpoint" {
  service_name = "com.amazonaws.eu-west-1.ecr.dkr"
  vpc_id       = data.aws_vpc.vpc.id
}

data "aws_vpc_endpoint" "logs_endpoint" {
  service_name = "com.amazonaws.eu-west-1.logs"
  vpc_id       = data.aws_vpc.vpc.id
}

data "aws_vpc_endpoint" "s3_endpoint" {
  service_name = "com.amazonaws.eu-west-1.s3"
  vpc_id       = data.aws_vpc.vpc.id
}

locals {
  common_sg_rules = {
    ecr = {
      port              = 443
      security_group_id = tolist(data.aws_vpc_endpoint.ecr_endpoint.security_group_ids)[0]
    },
    logs = {
      port              = 443
      security_group_id = tolist(data.aws_vpc_endpoint.logs_endpoint.security_group_ids)[0]
    },
    s3 = {
      port           = 443
      prefix_list_id = data.aws_vpc_endpoint.s3_endpoint.prefix_list_id
    }
  }

  front_sg_rules = merge(
    local.common_sg_rules,
    {
      cache = {
        port              = 6379
        security_group_id = aws_security_group.front_cache.id
      },
      api = {
        port              = 443
        security_group_id = aws_security_group.api_task.id
      },
      pdf = {
        port              = 80
        security_group_id = aws_security_group.wkhtmltopdf.id
      },
      scan = {
        port              = 8080
        security_group_id = aws_security_group.scan.id
      }
    }
  )
}

resource "aws_security_group_rule" "front_task_out" {
  for_each = local.front_sg_rules

  type                     = "egress"
  protocol                 = "tcp"
  from_port                = each.value.port
  to_port                  = each.value.port
  security_group_id        = aws_security_group.front.id
  source_security_group_id = contains(keys(each.value), "security_group_id") ? each.value.security_group_id : null
  prefix_list_ids          = contains(keys(each.value), "prefix_list_id") ? [each.value.prefix_list_id] : null
  description              = each.key
}
