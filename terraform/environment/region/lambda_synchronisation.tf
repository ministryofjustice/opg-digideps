locals {
  sync_lambda_env_vars = {
    DIGIDEPS_SYNC_ENDPOINT = "https://${var.front_fully_qualified_domain_name}"
    SECRETS_PREFIX         = var.secrets_prefix
  }
}

module "lamdba_synchronisation" {
  source                = "./modules/lambda"
  lambda_name           = "synchronise-to-sirius-${local.environment}"
  description           = "Function to kick off document and checklist sync from digideps to sirius"
  environment_variables = local.sync_lambda_env_vars
  image_uri             = local.images.synchronise
  ecr_arn               = data.aws_ecr_repository.images["synchronise-lambda"].arn
  tags                  = var.default_tags
  account               = var.account
  environment           = local.environment
  aws_subnet_ids        = data.aws_subnet.private[*].id
  memory                = 512
  vpc_id                = data.aws_vpc.vpc.id
  secrets               = [data.aws_secretsmanager_secret.jwt_token_synchronisation.arn]
  logs_kms_key_arn      = aws_kms_key.cloudwatch_logs.arn
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

resource "aws_security_group_rule" "lambda_sync_to_secrets_endpoint" {
  type                     = "egress"
  protocol                 = "tcp"
  from_port                = 443
  to_port                  = 443
  source_security_group_id = data.aws_security_group.secrets_endpoint.id
  security_group_id        = module.lamdba_synchronisation.lambda_sg.id
  description              = "Outbound lambda to secrets endpoint"
}

resource "aws_cloudwatch_event_rule" "sync_documents" {
  name                = "synchronise-documents-schedule-${local.environment}"
  description         = "Kicks off document synch to sirius in ${terraform.workspace}"
  schedule_expression = "rate(24 hours)"
  state               = "DISABLED"
  tags                = var.default_tags
}

resource "aws_cloudwatch_event_rule" "sync_checklists" {
  name                = "synchronise-checklists-schedule-${local.environment}"
  description         = "Kicks off checklist synch to sirius in ${terraform.workspace}"
  schedule_expression = "rate(24 hours)"
  state               = "DISABLED"
  tags                = var.default_tags
}

resource "aws_cloudwatch_event_target" "sync_documents" {
  rule  = aws_cloudwatch_event_rule.sync_documents.name
  arn   = module.lamdba_synchronisation.lambda.arn
  input = jsonencode({ "command" : "documents" })
}

resource "aws_cloudwatch_event_target" "sync_checklists" {
  rule  = aws_cloudwatch_event_rule.sync_checklists.name
  arn   = module.lamdba_synchronisation.lambda.arn
  input = jsonencode({ "command" : "checklists" })
}

resource "aws_lambda_permission" "allow_cloudwatch_checklists_to_call_synchronise_lambda" {
  statement_id  = "AllowExecutionFromCloudWatchChecklists"
  action        = "lambda:InvokeFunction"
  function_name = module.lamdba_synchronisation.lambda.function_name
  principal     = "events.amazonaws.com"
  source_arn    = aws_cloudwatch_event_rule.sync_checklists.arn
}

resource "aws_lambda_permission" "allow_cloudwatch_documents_to_call_synchronise_lambda" {
  statement_id  = "AllowExecutionFromCloudWatchDocuments"
  action        = "lambda:InvokeFunction"
  function_name = module.lamdba_synchronisation.lambda.function_name
  principal     = "events.amazonaws.com"
  source_arn    = aws_cloudwatch_event_rule.sync_documents.arn
}
