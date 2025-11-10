## Compare two APIs

Under some circumstances we may need to write some new APIs and we may want to check that they
return the expected results with live like data.

We have developed a methodology to do this. We can use the following command (giving the old route and the new route):

```
php app/console digideps:api:api-comparison "/client/{client_id}" "/v2/client/{client_id}"
```

To accomplish this, we pass an interable comparer selector and select which one to use based on the routes provided.

As such if you want to set up some new routes to compare then you will need to create a new concrete class in
ResponseComparison and add a SQL statement for the selection of IDs to test and what you intend to validate on.

This can then be run overnight in preproduction to give confidence in our API changes.

## Terraform for the required job to run the API comparison

Here is some example terraform that we can set up to create a runnable job for performing the API comparisons.
It's a bit extra work but we don't want to change our base infra to make this temporary job work.

```
resource "aws_cloudwatch_event_rule" "court_order_api_comparison" {
  name                = "court-order-api-compare-${local.environment}"
  description         = "Test out our API comparer ${terraform.workspace}"
  schedule_expression = "cron(0 5 * * ? *)"
  tags                = var.default_tags
}

resource "aws_cloudwatch_event_target" "court_order_api_comparison" {
  rule     = aws_cloudwatch_event_rule.court_order_api_comparison.name
  arn      = aws_ecs_cluster.main.arn
  role_arn = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.api_high_memory.arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"

    network_configuration {
      security_groups  = [module.api_service_security_group.id]
      subnets          = data.aws_subnet.private[*].id
      assign_public_ip = false
    }
  }
  input = jsonencode(
    {
      "containerOverrides" : [
        {
          "name" : "api_app",
          "command" : ["sh", "scripts/task_run_console_command.sh", "digideps:api:api-comparison", "client/get-all-clients-by-deputy-uid/{deputy_uid}", "v2/deputy/{deputy_id}/courtorders", "all"]
        }
      ]
    }
  )
}
```

Because we use service connect, there's some other workarounds we need to perform to get this working!

In the `api_service_app_variables` variable we need to add:

```
    {
      name  = "API_URL",
      value = "http://api.${aws_service_discovery_http_namespace.cloudmap_namespace.name}.local"
    },
```

We then have to set up private DNS for API!
In `resource "aws_ecs_service" "api"` block:

```
service_registries {
  registry_arn = aws_service_discovery_service.api_dns.arn
}
```

then:

```
# Private DNS namespace
resource "aws_service_discovery_private_dns_namespace" "cloudmap_dns_namespace" {
  name        = "digideps-preproduction.local"
  vpc         = data.aws_vpc.vpc.id
  description = "Private DNS namespace for ECS service discovery"
}

# Register API service into both namespaces
resource "aws_service_discovery_service" "api_dns" {
  name         = "api"
  namespace_id = aws_service_discovery_private_dns_namespace.cloudmap_dns_namespace.id

  dns_config {
    namespace_id = aws_service_discovery_private_dns_namespace.cloudmap_dns_namespace.id
    dns_records {
      ttl  = 10
      type = "A"
    }
    routing_policy = "MULTIVALUE"
  }

  health_check_custom_config {
    failure_threshold = 1
  }
}
```
