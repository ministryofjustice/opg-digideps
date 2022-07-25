data "aws_ecr_repository" "deputy_reporting" {
  provider = aws.management
  name     = "integrations/deputy-reporting-lambda"
}

locals {
  lambda_env_vars = {
    DIGIDEPS_SYNC_ENDPOINT = "https://${local.front_service_fqdn}/synchronise/documents"
  }
}

module "lamdba_synchronisation" {
  source                = "./lambda"
  lambda_name           = "synchronise-to-sirius-${local.environment}"
  description           = "Function to kick off document and checklist sync from digideps to sirius"
  working_directory     = "/var/task"
  environment_variables = local.lambda_env_vars
  image_uri             = local.images.synchronise
  ecr_arn               = data.aws_ecr_repository.images["test"].arn
  tags                  = local.default_tags
  account               = local.account
  environment           = local.environment
  aws_subnet_ids        = data.aws_subnet.private.*.id
  memory                = 512
  vpc_id                = data.aws_vpc.vpc.id
}

resource "aws_security_group_rule" "lambda_sync_to_front" {
  type                     = "egress"
  protocol                 = "tcp"
  from_port                = 443
  to_port                  = 443
  source_security_group_id = module.front_service_security_group.id
  security_group_id        = module.lamdba_synchronisation.lambda_sg.id
  description              = "Outbound lambda sync to front"
}

resource "aws_cloudwatch_event_rule" "event_rule" {
  name                = "${module.lamdba_synchronisation.lambda.function_name}-schedule"
  description         = "Kicks off document and checklist synch to sirius in ${terraform.workspace}"
  schedule_expression = "rate(3 minutes)"
  tags                = local.default_tags
}

resource "aws_cloudwatch_event_target" "check_at_rate" {
  rule  = aws_cloudwatch_event_rule.event_rule.name
  arn   = module.lamdba_synchronisation.lambda.arn
  input = "{\"commands\":[\"document\"]}"
}

resource "aws_lambda_permission" "allow_cloudwatch_to_call_synchronise_lambda" {
  statement_id  = "AllowExecutionFromCloudWatch"
  action        = "lambda:InvokeFunction"
  function_name = module.lamdba_synchronisation.lambda.function_name
  principal     = "events.amazonaws.com"
  source_arn    = aws_cloudwatch_event_rule.event_rule.arn
}
