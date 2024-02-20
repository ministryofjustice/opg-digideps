resource "aws_cloudwatch_dashboard" "main" {
  dashboard_name = upper(local.environment)

  dashboard_body = jsonencode({
    "widgets" : [
      {
        "type" : "metric",
        "x" : 15,
        "y" : 0,
        "width" : 9,
        "height" : 3,
        "properties" : {
          "metrics" : [
            ["AWS/Route53", "HealthCheckPercentageHealthy", "HealthCheckId", var.health_check_front.id, { "region" : "us-east-1", "label" : "Public Frontend" }],
            ["AWS/Route53", "HealthCheckPercentageHealthy", "HealthCheckId", var.health_check_admin.id, { "region" : "us-east-1", "label" : "Admin Frontend" }]
          ],
          "region" : "eu-west-1",
          "view" : "singleValue",
          "stacked" : false,
          "start" : "-P28D",
          "end" : "P0D",
          "period" : 60,
          "title" : "Website Availability",
          "stat" : "Average",
          "singleValueFullPrecision" : false,
          "setPeriodToTimeRange" : true
        }
      },
      {
        "type" : "metric",
        "x" : 0,
        "y" : 0,
        "width" : 15,
        "height" : 9,
        "properties" : {
          "metrics" : [
            ["AWS/ApplicationELB", "TargetResponseTime", "LoadBalancer", trimprefix(split(":", aws_lb.front.arn)[5], "loadbalancer/"), { "id" : "m1", "stat" : "Average", "label" : "Frontend Average Response Time" }],
            ["AWS/ApplicationELB", "TargetResponseTime", "LoadBalancer", trimprefix(split(":", aws_lb.admin.arn)[5], "loadbalancer/"), { "id" : "m2", "stat" : "Average", "label" : "Admin Average Response Time" }],
            ["AWS/ApplicationELB", "TargetResponseTime", "LoadBalancer", trimprefix(split(":", aws_lb.front.arn)[5], "loadbalancer/"), { "id" : "m3", "yAxis" : "right", "stat" : "Maximum", "label" : "Frontend Max Response Time" }],
            ["AWS/ApplicationELB", "TargetResponseTime", "LoadBalancer", trimprefix(split(":", aws_lb.admin.arn)[5], "loadbalancer/"), { "id" : "m4", "yAxis" : "right", "stat" : "Maximum", "label" : "Admin Max Response Time" }]
          ],
          "view" : "timeSeries",
          "stacked" : false,
          "region" : "eu-west-1",
          "stat" : "Sum",
          "period" : 60,
          "start" : "-PT3H",
          "end" : "P0D",
          "title" : "ALB Response Times",
          "legend" : {
            "position" : "bottom"
          }
        }
      },
      {
        "type" : "metric",
        "x" : 0,
        "y" : 9,
        "width" : 15,
        "height" : 9,
        "properties" : {
          "metrics" : [
            ["AWS/ApplicationELB", "RequestCount", "LoadBalancer", trimprefix(split(":", aws_lb.front.arn)[5], "loadbalancer/"), { "id" : "m1", "label" : "Frontend Requests Count" }],
            ["AWS/ApplicationELB", "RequestCount", "LoadBalancer", trimprefix(split(":", aws_lb.admin.arn)[5], "loadbalancer/"), { "id" : "m2", "label" : "Admin Request Count" }],
          ],
          "view" : "timeSeries",
          "stacked" : false,
          "region" : "eu-west-1",
          "stat" : "Sum",
          "period" : 60,
          "start" : "-PT3H",
          "end" : "P0D",
          "title" : "ALB Requests per minute",
          "legend" : {
            "position" : "bottom"
          }
        }
      },
      {
        "type" : "metric",
        "x" : 15,
        "y" : 9,
        "width" : 9,
        "height" : 9,
        "properties" : {
          "metrics" : [
            ["AWS/ApplicationELB", "HTTPCode_Target_5XX_Count", "LoadBalancer", trimprefix(split(":", aws_lb.front.arn)[5], "loadbalancer/"), { "id" : "m1", "label" : "Frontend ALB 5XX Errors" }],
            ["AWS/ApplicationELB", "HTTPCode_Target_5XX_Count", "LoadBalancer", trimprefix(split(":", aws_lb.admin.arn)[5], "loadbalancer/"), { "id" : "m2", "label" : "Admin ALB 5XX Errors" }],
            ["DigiDeps/Error", aws_cloudwatch_log_metric_filter.frontend_5xx_errors.name, { "id" : "m3", "label" : "Frontend Service 5XX Errors" }],
            ["DigiDeps/Error", aws_cloudwatch_log_metric_filter.admin_5xx_errors.name, { "id" : "m4", "label" : "Admin Service 5XX Errors" }],
            ["DigiDeps/Error", aws_cloudwatch_log_metric_filter.api_5xx_errors.name, { "id" : "m5", "label" : "API Service 5XX Errors" }]
          ],
          "view" : "timeSeries",
          "stacked" : false,
          "region" : "eu-west-1",
          "stat" : "Sum",
          "period" : 60,
          "start" : "-PT3H",
          "end" : "P0D",
          "title" : "HTTP Request Error Count",
          "legend" : {
            "position" : "bottom"
          }
        }
      },
      {
        "type" : "metric",
        "x" : 15,
        "y" : 3,
        "width" : 9,
        "height" : 6,
        "properties" : {
          "metrics" : [
            ["ECS/ContainerInsights", "RunningTaskCount", "ServiceName", aws_ecs_service.front.name, "ClusterName", aws_ecs_cluster.main.name, { "label" : "Frontend" }],
            ["ECS/ContainerInsights", "RunningTaskCount", "ServiceName", aws_ecs_service.admin.name, "ClusterName", aws_ecs_cluster.main.name, { "label" : "Admin" }],
            ["ECS/ContainerInsights", "RunningTaskCount", "ServiceName", aws_ecs_service.api.name, "ClusterName", aws_ecs_cluster.main.name, { "label" : "API" }],
            ["ECS/ContainerInsights", "RunningTaskCount", "ServiceName", aws_ecs_service.scan.name, "ClusterName", aws_ecs_cluster.main.name, { "label" : "Scan Service" }],
            ["ECS/ContainerInsights", "RunningTaskCount", "ServiceName", aws_ecs_service.htmltopdf.name, "ClusterName", aws_ecs_cluster.main.name, { "label" : "HTML to PDF" }],
            ["ECS/ContainerInsights", "RunningTaskCount", "ServiceName", aws_ecs_service.mock_sirius_integration.name, "ClusterName", aws_ecs_cluster.main.name, { "label" : "Mock Sirius Integration" }]
          ],
          "view" : "singleValue",
          "stacked" : false,
          "region" : "eu-west-1",
          "stat" : "Average",
          "period" : 60,
          "start" : "-PT3H",
          "end" : "P0D",
          "title" : "Average Running Task Count",
          "legend" : {
            "position" : "bottom"
          },
          "setPeriodToTimeRange" : true
        }
      },
      {
        "type" : "metric",
        "x" : 15,
        "y" : 18,
        "width" : 9,
        "height" : 9,
        "properties" : {
          "metrics" : [
            ["AWS/ElastiCache", "CPUUtilization", "CacheClusterId", "${data.aws_elasticache_replication_group.front_cache_cluster.id}-001", { "label" : "Frontend" }],
            ["AWS/ElastiCache", "CPUUtilization", "CacheClusterId", "${data.aws_elasticache_replication_group.api_cache_cluster.id}-001", { "label" : "API" }]
          ],
          "view" : "timeSeries",
          "stacked" : false,
          "region" : "eu-west-1",
          "stat" : "Average",
          "period" : 60,
          "title" : "Elasticache Primary Cluster Average CPU Utilisation"
        }
      },
      {
        "type" : "metric",
        "x" : 0,
        "y" : 18,
        "width" : 15,
        "height" : 9,
        "properties" : {
          "metrics" : [
            ["AWS/ECS", "CPUUtilization", "ServiceName", aws_ecs_service.front.name, "ClusterName", aws_ecs_cluster.main.name, { "label" : "Frontend CPU" }],
            ["AWS/ECS", "CPUUtilization", "ServiceName", aws_ecs_service.admin.name, "ClusterName", aws_ecs_cluster.main.name, { "label" : "Admin CPU" }],
            ["AWS/ECS", "CPUUtilization", "ServiceName", aws_ecs_service.api.name, "ClusterName", aws_ecs_cluster.main.name, { "label" : "API CPU" }],
            ["AWS/ECS", "CPUUtilization", "ServiceName", aws_ecs_service.htmltopdf.name, "ClusterName", aws_ecs_cluster.main.name, { "label" : "HTML2PDF CPU" }],
            ["AWS/ECS", "CPUUtilization", "ServiceName", aws_ecs_service.scan.name, "ClusterName", aws_ecs_cluster.main.name, { "label" : "AV Scan CPU" }],
            ["AWS/ECS", "MemoryUtilization", "ServiceName", aws_ecs_service.front.name, "ClusterName", aws_ecs_cluster.main.name, { "yAxis" : "right", "label" : "Frontend Memory" }],
            ["AWS/ECS", "MemoryUtilization", "ServiceName", aws_ecs_service.admin.name, "ClusterName", aws_ecs_cluster.main.name, { "yAxis" : "right", "label" : "Admin Memory" }],
            ["AWS/ECS", "MemoryUtilization", "ServiceName", aws_ecs_service.api.name, "ClusterName", aws_ecs_cluster.main.name, { "yAxis" : "right", "label" : "API Memory" }],
            ["AWS/ECS", "MemoryUtilization", "ServiceName", aws_ecs_service.htmltopdf.name, "ClusterName", aws_ecs_cluster.main.name, { "yAxis" : "right", "label" : "HTML2PDF Memory" }],
            ["AWS/ECS", "MemoryUtilization", "ServiceName", aws_ecs_service.scan.name, "ClusterName", aws_ecs_cluster.main.name, { "yAxis" : "right", "label" : "AV Scan Memory" }]
          ],
          "view" : "timeSeries",
          "stacked" : false,
          "region" : "eu-west-1",
          "stat" : "Average",
          "period" : 60,
          "title" : "ECS CPU & Memory Utilisation"
        }
      }
    ]
  })
}
