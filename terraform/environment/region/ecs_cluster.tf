# ECS Cluster
resource "aws_service_discovery_http_namespace" "cloudmap_namespace" {
  name        = "digideps-${local.environment}"
  description = "Namespace for Service Discovery"
}

resource "aws_ecs_cluster" "main" {
  name = local.environment
  tags = var.default_tags
  setting {
    name  = "containerInsights"
    value = "enabled"
  }
  depends_on = [aws_cloudwatch_log_group.container_insights]
}

resource "aws_cloudwatch_log_group" "container_insights" {
  name              = "/aws/ecs/containerinsights/${local.environment}/performance"
  retention_in_days = 1
  kms_key_id        = aws_kms_key.cloudwatch_logs.arn
  tags              = var.default_tags
}
