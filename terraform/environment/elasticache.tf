## Frontend Elasticache
#
#data "aws_elasticache_replication_group" "front_cache_cluster" {
#  replication_group_id = "frontend-redis-${local.account.name}"
#}
#
#data "aws_security_group" "front_cache_sg" {
#  name = "${local.account.name}-account-cache-frontend"
#}
#
#resource "aws_security_group_rule" "admin_to_redis" {
#  description              = "Admin to to front cache cluster"
#  from_port                = 6379
#  to_port                  = 6379
#  type                     = "ingress"
#  protocol                 = "tcp"
#  security_group_id        = data.aws_security_group.front_cache_sg.id
#  source_security_group_id = module.admin_service_security_group.id
#}
#
#resource "aws_security_group_rule" "front_to_redis" {
#  description              = "Frontend to front cache cluster"
#  from_port                = 6379
#  to_port                  = 6379
#  type                     = "ingress"
#  protocol                 = "tcp"
#  security_group_id        = data.aws_security_group.front_cache_sg.id
#  source_security_group_id = module.front_service_security_group.id
#}
#
## API Elasticache
#
#data "aws_elasticache_replication_group" "api_cache_cluster" {
#  replication_group_id = "api-redis-${local.account.name}"
#}
#
#data "aws_security_group" "api_cache_sg" {
#  name = "${local.account.name}-account-cache-api"
#}
#
#resource "aws_security_group_rule" "api_to_redis" {
#  description              = "Api to Api cache cluster"
#  from_port                = 6379
#  to_port                  = 6379
#  type                     = "ingress"
#  protocol                 = "tcp"
#  security_group_id        = data.aws_security_group.api_cache_sg.id
#  source_security_group_id = module.api_service_security_group.id
#}
