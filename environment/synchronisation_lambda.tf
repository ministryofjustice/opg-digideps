data "aws_ecr_repository" "deputy_reporting" {
  provider = aws.management
  name     = "integrations/deputy-reporting-lambda"
}

locals {
  lambda_env_vars = {
    DIGIDEPS_SYNC_ENDPOINT = "https://${local.api_service_fqdn}"
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

resource "aws_cloudwatch_event_rule" "event_rule" {
  schedule_expression = "rate(3 minutes)"
}

resource "aws_cloudwatch_event_target" "check_at_rate" {
  rule = aws_cloudwatch_event_rule.event_rule.name
  arn  = module.lamdba_synchronisation.lambda.arn
}

resource "aws_lambda_permission" "allow_cloudwatch_to_call_check_foo" {
  statement_id  = "AllowExecutionFromCloudWatch"
  action        = "lambda:InvokeFunction"
  function_name = module.lamdba_synchronisation.lambda.function_name
  principal     = "events.amazonaws.com"
  source_arn    = aws_cloudwatch_event_rule.event_rule.arn
}
