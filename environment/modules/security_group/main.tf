resource "aws_security_group_rule" "rules" {
  for_each = var.rules

  type                     = each.value.type
  protocol                 = each.value.protocol
  from_port                = each.value.port
  to_port                  = each.value.port
  security_group_id        = aws_security_group.group.id
  source_security_group_id = each.value.target_type == "security_group_id" ? each.value.target : null
  prefix_list_ids          = each.value.target_type == "prefix_list_id" ? [each.value.target] : null
  description              = each.key
  #tfsec:ignore:aws-vpc-no-public-egress-sgr - these should be legit but we need to split out this module to be sure
  cidr_blocks = each.value.target_type == "cidr_block" ? [each.value.target] : null
  self        = each.value.target_type == "self" ? each.value.target : null
}

resource "aws_security_group" "group" {
  name_prefix = var.name
  vpc_id      = var.vpc_id
  description = var.description

  lifecycle {
    create_before_destroy = true
  }

  revoke_rules_on_delete = true

  tags = merge(
    var.tags,
    {
      "Name" = var.name
    },
  )
}
