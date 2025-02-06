output "aws_lb_admin" {
  value = aws_lb.admin
}

output "aws_lb_front" {
  value = aws_lb.front
}

output "Services" {
  value = {
    Cluster = aws_ecs_cluster.main.name
    Services = [
      aws_ecs_service.admin.name,
      aws_ecs_service.api.name,
      aws_ecs_service.front.name,
      aws_ecs_service.scan.name,
      aws_ecs_service.htmltopdf.name,
    ]
  }
}

output "Tasks" {
  value = {
    backup                  = module.backup.render
    reset_database          = module.reset_database.render
    restore                 = module.restore.render
    restore_from_production = module.restore_from_production.render
    end_to_end_test_v2      = module.integration_tests.render_with_override
    smoke_tests             = module.smoke_tests.render
    resilience_tests        = module.resilience_tests.render
  }
}
