output "Role" {
  value = "arn:aws:iam::${local.account["account_id"]}:role/${var.DEFAULT_ROLE}"
}

output "Services" {
  value = {
    Cluster = aws_ecs_cluster.main.name
    Services = [
      aws_ecs_service.admin.name,
      aws_ecs_service.api.name,
      aws_ecs_service.front.name,
      aws_ecs_service.scan.name,
      aws_ecs_service.wkhtmltopdf.name,
    ]
  }
}

output "Tasks" {
  value = {
    backup                  = module.backup.render
    reset_database          = module.reset_database.render
    restore                 = module.restore.render
    restore_from_production = module.restore_from_production.render
    integration_test        = module.integration_test.render
  }
}

output "opg_docker_tag" {
  value = var.OPG_DOCKER_TAG
}

output "db_engine_version" {
  value = local.account.always_on ? aws_db_instance.api[0].engine_version : aws_rds_cluster.api[0].engine_version
}
