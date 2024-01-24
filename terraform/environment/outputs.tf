output "Role" {
  value = "arn:aws:iam::${local.account["account_id"]}:role/${var.DEFAULT_ROLE}"
}

output "Services" {
  value = module.eu_west_1[0].Services
}

output "Tasks" {
  value = module.eu_west_1[0].Tasks
}

output "opg_docker_tag" {
  value = var.OPG_DOCKER_TAG
}
