module "front_ecs_autoscaling" {
  source                           = "./modules/ecs_autoscaling"
  environment                      = local.environment
  aws_ecs_cluster_name             = aws_ecs_cluster.main.name
  aws_ecs_service_name             = aws_ecs_service.front.name
  ecs_autoscaling_service_role_arn = data.aws_iam_role.ecs_autoscaling_service_role.arn
  ecs_task_autoscaling_minimum     = var.account.ecs_scale_min
  ecs_task_autoscaling_maximum     = var.account.ecs_scale_max
}

module "api_ecs_autoscaling" {
  source                           = "./modules/ecs_autoscaling"
  environment                      = local.environment
  aws_ecs_cluster_name             = aws_ecs_cluster.main.name
  aws_ecs_service_name             = aws_ecs_service.api.name
  ecs_autoscaling_service_role_arn = data.aws_iam_role.ecs_autoscaling_service_role.arn
  ecs_task_autoscaling_minimum     = var.account.ecs_scale_min
  ecs_task_autoscaling_maximum     = var.account.ecs_scale_max
}
