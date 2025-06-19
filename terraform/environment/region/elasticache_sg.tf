# INFO - Elasticache set up in account folder as we use shared elasticache

# Front Elasticache
data "aws_elasticache_replication_group" "front_cache_cluster" {
  replication_group_id = "frontend-cache-${var.account.name}"
}

data "aws_security_group" "cache_front_sg" {
  name = "${var.account.name}-account-cache-front"
}

resource "aws_security_group_rule" "admin_to_cache" {
  description              = "Admin to to front cache cluster"
  from_port                = 6379
  to_port                  = 6379
  type                     = "ingress"
  protocol                 = "tcp"
  security_group_id        = data.aws_security_group.cache_front_sg.id
  source_security_group_id = module.admin_service_security_group.id
}

resource "aws_security_group_rule" "front_to_cache" {
  description              = "Frontend to front cache cluster"
  from_port                = 6379
  to_port                  = 6379
  type                     = "ingress"
  protocol                 = "tcp"
  security_group_id        = data.aws_security_group.cache_front_sg.id
  source_security_group_id = module.front_service_security_group.id
}

# API Elasticache

data "aws_elasticache_replication_group" "api_cache_cluster" {
  replication_group_id = "api-cache-${var.account.name}"
}

data "aws_security_group" "cache_api_sg" {
  name = "${var.account.name}-account-cache-api"
}

resource "aws_security_group_rule" "api_to_cache" {
  description              = "Api to Api cache cluster"
  from_port                = 6379
  to_port                  = 6379
  type                     = "ingress"
  protocol                 = "tcp"
  security_group_id        = data.aws_security_group.cache_api_sg.id
  source_security_group_id = module.api_service_security_group.id
}


#Allow the SSM Instance to talk to the Redis Cluster

# data "aws_security_group" "ssm_ec2_operator_redis" {
#   filter {
#     name   = "tag:Name"
#     values = "ssm-operator-instance"
#   }
# }

# data "aws_security_group" "ssm_ec2_breakglass_redis" {
#   filter {
#     name   = "tag:Name"
#     values = "ssm-breakglass-instance"
#   }
# }

# resource "aws_security_group_rule" "redis_ssm_egress_operator" {
#   description              = "${var.name}-ssm-operator to api redis - ${var.environment}"
#   type                     = "egress"
#   from_port                = 6379
#   to_port                  = 6379
#   protocol                 = "tcp"
#   source_security_group_id = data.aws_security_group.ssm_ec2_operator_redis.id
#   security_group_id        = data.aws_security_group.cache_api_sg.id
# }

# resource "aws_security_group_rule" "redis_ssm_egress_breakglass" {
#   description              = "${var.name}-ssm-breakglass to api redis - ${var.environment}"
#   type                     = "egress"
#   from_port                = 6379
#   to_port                  = 6379
#   protocol                 = "tcp"
#   source_security_group_id = data.aws_security_group.ssm_ec2_breakglass_redis.id
#   security_group_id        = data.aws_security_group.cache_api_sg.id
# }
