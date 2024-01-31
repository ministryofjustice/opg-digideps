moved {
  from = aws_cloud9_environment_membership.shared
  to   = module.eu_west_1[0].aws_cloud9_environment_membership.shared
}
moved {
  from = data.aws_iam_policy_document.lambda_slack
  to   = module.eu_west_1[0].data.aws_iam_policy_document.lambda_slack
}
moved {
  from = data.aws_iam_policy_document.pa_uploads_branch_replication[0]
  to   = module.eu_west_1[0].data.aws_iam_policy_document.pa_uploads_branch_replication[0]
}
moved {
  from = data.aws_secretsmanager_secret_version.cloud9_users
  to   = module.eu_west_1[0].data.aws_secretsmanager_secret_version.cloud9_users
}
moved {
  from = data.aws_secretsmanager_secret_version.slack_webhook_url
  to   = module.eu_west_1[0].data.aws_secretsmanager_secret_version.slack_webhook_url
}
moved {
  from = aws_acm_certificate.digideps_service_justice
  to   = module.eu_west_1[0].aws_acm_certificate.digideps_service_justice
}
moved {
  from = aws_acm_certificate.digideps_service_justice_admin
  to   = module.eu_west_1[0].aws_acm_certificate.digideps_service_justice_admin
}
moved {
  from = aws_acm_certificate_validation.digideps_service_justice
  to   = module.eu_west_1[0].aws_acm_certificate_validation.digideps_service_justice
}
moved {
  from = aws_acm_certificate_validation.digideps_service_justice_admin
  to   = module.eu_west_1[0].aws_acm_certificate_validation.digideps_service_justice_admin
}
moved {
  from = aws_cloud9_environment_ec2.shared
  to   = module.eu_west_1[0].aws_cloud9_environment_ec2.shared
}
moved {
  from = aws_cloudwatch_event_rule.nightly
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.nightly
}
moved {
  from = aws_cloudwatch_log_group.redeployer_lambda
  to   = module.eu_west_1[0].aws_cloudwatch_log_group.redeployer_lambda
}
moved {
  from = aws_cloudwatch_log_group.slack_lambda
  to   = module.eu_west_1[0].aws_cloudwatch_log_group.slack_lambda
}
moved {
  from = aws_cloudwatch_log_group.vpc_flow_logs
  to   = module.eu_west_1[0].aws_cloudwatch_log_group.vpc_flow_logs
}
moved {
  from = aws_cloudwatch_log_group.vpc_flow_logs_default
  to   = module.eu_west_1[0].aws_cloudwatch_log_group.vpc_flow_logs_default
}
moved {
  from = aws_cloudwatch_log_group.waf_web_acl
  to   = module.eu_west_1[0].aws_cloudwatch_log_group.waf_web_acl
}
moved {
  from = aws_cloudwatch_query_definition.container_cpu_memory
  to   = module.eu_west_1[0].aws_cloudwatch_query_definition.container_cpu_memory
}
moved {
  from = aws_cloudwatch_query_definition.container_high_cpu_memory_only
  to   = module.eu_west_1[0].aws_cloudwatch_query_definition.container_high_cpu_memory_only
}
moved {
  from = aws_cloudwatch_query_definition.container_turnover
  to   = module.eu_west_1[0].aws_cloudwatch_query_definition.container_turnover
}
moved {
  from = aws_cloudwatch_query_definition.exceptions_and_errors
  to   = module.eu_west_1[0].aws_cloudwatch_query_definition.exceptions_and_errors
}
moved {
  from = aws_cloudwatch_query_definition.non_healthcheck_requests
  to   = module.eu_west_1[0].aws_cloudwatch_query_definition.non_healthcheck_requests
}
moved {
  from = aws_cloudwatch_query_definition.response_distribution
  to   = module.eu_west_1[0].aws_cloudwatch_query_definition.response_distribution
}
moved {
  from = aws_cloudwatch_query_definition.slow_response_times
  to   = module.eu_west_1[0].aws_cloudwatch_query_definition.slow_response_times
}
moved {
  from = aws_cloudwatch_query_definition.status_4xx
  to   = module.eu_west_1[0].aws_cloudwatch_query_definition.status_4xx
}
moved {
  from = aws_cloudwatch_query_definition.status_5xx
  to   = module.eu_west_1[0].aws_cloudwatch_query_definition.status_5xx
}
moved {
  from = aws_cloudwatch_query_definition.sync_logs
  to   = module.eu_west_1[0].aws_cloudwatch_query_definition.sync_logs
}
moved {
  from = aws_db_subnet_group.private
  to   = module.eu_west_1[0].aws_db_subnet_group.private
}
moved {
  from = aws_eip.nat[0]
  to   = module.eu_west_1[0].aws_eip.nat[0]
}
moved {
  from = aws_eip.nat[1]
  to   = module.eu_west_1[0].aws_eip.nat[1]
}
moved {
  from = aws_eip.nat[2]
  to   = module.eu_west_1[0].aws_eip.nat[2]
}
moved {
  from = aws_elasticache_parameter_group.digideps
  to   = module.eu_west_1[0].aws_elasticache_parameter_group.digideps
}
moved {
  from = aws_elasticache_replication_group.cache_api
  to   = module.eu_west_1[0].aws_elasticache_replication_group.cache_api
}
moved {
  from = aws_elasticache_replication_group.front_api
  to   = module.eu_west_1[0].aws_elasticache_replication_group.front_api
}
moved {
  from = aws_elasticache_subnet_group.private
  to   = module.eu_west_1[0].aws_elasticache_subnet_group.private
}
moved {
  from = aws_flow_log.vpc_flow_logs
  to   = module.eu_west_1[0].aws_flow_log.vpc_flow_logs
}
moved {
  from = aws_flow_log.vpc_flow_logs_default
  to   = module.eu_west_1[0].aws_flow_log.vpc_flow_logs_default
}
moved {
  from = aws_iam_policy.integrations_s3_read
  to   = module.eu_west_1[0].aws_iam_policy.integrations_s3_read
}
moved {
  from = aws_iam_policy.replication[0]
  to   = module.eu_west_1[0].aws_iam_policy.replication[0]
}
moved {
  from = aws_iam_role.enhanced_monitoring
  to   = module.eu_west_1[0].aws_iam_role.enhanced_monitoring
}
moved {
  from = aws_iam_role.events_task_runner
  to   = module.eu_west_1[0].aws_iam_role.events_task_runner
}
moved {
  from = aws_iam_role.integrations_s3_read
  to   = module.eu_west_1[0].aws_iam_role.integrations_s3_read
}
moved {
  from = aws_iam_role.lambda_redeployer
  to   = module.eu_west_1[0].aws_iam_role.lambda_redeployer
}
moved {
  from = aws_iam_role.lambda_slack
  to   = module.eu_west_1[0].aws_iam_role.lambda_slack
}
moved {
  from = aws_iam_role.replication[0]
  to   = module.eu_west_1[0].aws_iam_role.replication[0]
}
moved {
  from = aws_iam_role.sync
  to   = module.eu_west_1[0].aws_iam_role.sync
}
moved {
  from = aws_iam_role.vpc_flow_logs
  to   = module.eu_west_1[0].aws_iam_role.vpc_flow_logs
}
moved {
  from = aws_iam_role_policy.events_task_runner
  to   = module.eu_west_1[0].aws_iam_role_policy.events_task_runner
}
moved {
  from = aws_iam_role_policy.lambda_redeployer
  to   = module.eu_west_1[0].aws_iam_role_policy.lambda_redeployer
}
moved {
  from = aws_iam_role_policy.lambda_slack
  to   = module.eu_west_1[0].aws_iam_role_policy.lambda_slack
}
moved {
  from = aws_iam_role_policy.sync
  to   = module.eu_west_1[0].aws_iam_role_policy.sync
}
moved {
  from = aws_iam_role_policy.vpc_flow_logs
  to   = module.eu_west_1[0].aws_iam_role_policy.vpc_flow_logs
}
moved {
  from = aws_iam_role_policy_attachment.access_policy_attachment
  to   = module.eu_west_1[0].aws_iam_role_policy_attachment.access_policy_attachment
}
moved {
  from = aws_iam_role_policy_attachment.enhanced_monitoring
  to   = module.eu_west_1[0].aws_iam_role_policy_attachment.enhanced_monitoring
}
moved {
  from = aws_iam_role_policy_attachment.replication[0]
  to   = module.eu_west_1[0].aws_iam_role_policy_attachment.replication[0]
}
moved {
  from = aws_internet_gateway.igw
  to   = module.eu_west_1[0].aws_internet_gateway.igw
}
moved {
  from = aws_kms_alias.cloudwatch_logs_alias
  to   = module.eu_west_1[0].aws_kms_alias.cloudwatch_logs_alias
}
moved {
  from = aws_kms_alias.waf_cloudwatch_log_encryption
  to   = module.eu_west_1[0].aws_kms_alias.waf_cloudwatch_log_encryption
}
moved {
  from = aws_kms_key.cloudwatch_logs
  to   = module.eu_west_1[0].aws_kms_key.cloudwatch_logs
}
moved {
  from = aws_kms_key.waf_cloudwatch_log_encryption
  to   = module.eu_west_1[0].aws_kms_key.waf_cloudwatch_log_encryption
}
moved {
  from = aws_lambda_function.redeployer_lambda
  to   = module.eu_west_1[0].aws_lambda_function.redeployer_lambda
}
moved {
  from = aws_lambda_function.slack_lambda
  to   = module.eu_west_1[0].aws_lambda_function.slack_lambda
}
moved {
  from = aws_lambda_layer_version.lambda_layer
  to   = module.eu_west_1[0].aws_lambda_layer_version.lambda_layer
}
moved {
  from = aws_lambda_permission.scheduled_checks
  to   = module.eu_west_1[0].aws_lambda_permission.scheduled_checks
}
moved {
  from = aws_lambda_permission.sns
  to   = module.eu_west_1[0].aws_lambda_permission.sns
}
moved {
  from = aws_lambda_permission.sns_availability
  to   = module.eu_west_1[0].aws_lambda_permission.sns_availability
}
moved {
  from = aws_nat_gateway.nat[0]
  to   = module.eu_west_1[0].aws_nat_gateway.nat[0]
}
moved {
  from = aws_nat_gateway.nat[1]
  to   = module.eu_west_1[0].aws_nat_gateway.nat[1]
}
moved {
  from = aws_nat_gateway.nat[2]
  to   = module.eu_west_1[0].aws_nat_gateway.nat[2]
}
moved {
  from = aws_route53_record.certificate_validation_admin
  to   = module.eu_west_1[0].aws_route53_record.certificate_validation_admin
}
moved {
  from = aws_route53_record.certificate_validation_app
  to   = module.eu_west_1[0].aws_route53_record.certificate_validation_app
}
moved {
  from = aws_route_table.private[0]
  to   = module.eu_west_1[0].aws_route_table.private[0]
}
moved {
  from = aws_route_table.private[1]
  to   = module.eu_west_1[0].aws_route_table.private[1]
}
moved {
  from = aws_route_table.private[2]
  to   = module.eu_west_1[0].aws_route_table.private[2]
}
moved {
  from = aws_route_table.public[0]
  to   = module.eu_west_1[0].aws_route_table.public[0]
}
moved {
  from = aws_route_table.public[1]
  to   = module.eu_west_1[0].aws_route_table.public[1]
}
moved {
  from = aws_route_table.public[2]
  to   = module.eu_west_1[0].aws_route_table.public[2]
}
moved {
  from = aws_route_table_association.private[0]
  to   = module.eu_west_1[0].aws_route_table_association.private[0]
}
moved {
  from = aws_route_table_association.private[1]
  to   = module.eu_west_1[0].aws_route_table_association.private[1]
}
moved {
  from = aws_route_table_association.private[2]
  to   = module.eu_west_1[0].aws_route_table_association.private[2]
}
moved {
  from = aws_route_table_association.public[0]
  to   = module.eu_west_1[0].aws_route_table_association.public[0]
}
moved {
  from = aws_route_table_association.public[1]
  to   = module.eu_west_1[0].aws_route_table_association.public[1]
}
moved {
  from = aws_route_table_association.public[2]
  to   = module.eu_west_1[0].aws_route_table_association.public[2]
}
moved {
  from = aws_s3_bucket.pa_uploads_branch_replication[0]
  to   = module.eu_west_1[0].aws_s3_bucket.pa_uploads_branch_replication[0]
}
moved {
  from = aws_s3_bucket_lifecycle_configuration.pa_uploads_branch_replication[0]
  to   = module.eu_west_1[0].aws_s3_bucket_lifecycle_configuration.pa_uploads_branch_replication[0]
}
moved {
  from = aws_s3_bucket_ownership_controls.pa_uploads_branch_replication[0]
  to   = module.eu_west_1[0].aws_s3_bucket_ownership_controls.pa_uploads_branch_replication[0]
}
moved {
  from = aws_s3_bucket_policy.pa_uploads_branch_replication[0]
  to   = module.eu_west_1[0].aws_s3_bucket_policy.pa_uploads_branch_replication[0]
}
moved {
  from = aws_s3_bucket_public_access_block.pa_uploads_branch_replication[0]
  to   = module.eu_west_1[0].aws_s3_bucket_public_access_block.pa_uploads_branch_replication[0]
}
moved {
  from = aws_s3_bucket_server_side_encryption_configuration.pa_uploads_branch_replication[0]
  to   = module.eu_west_1[0].aws_s3_bucket_server_side_encryption_configuration.pa_uploads_branch_replication[0]
}
moved {
  from = aws_s3_bucket_versioning.pa_uploads_branch_replication[0]
  to   = module.eu_west_1[0].aws_s3_bucket_versioning.pa_uploads_branch_replication[0]
}
moved {
  from = aws_secretsmanager_secret.cloud9_users
  to   = module.eu_west_1[0].aws_secretsmanager_secret.cloud9_users
}
moved {
  from = aws_secretsmanager_secret.slack_webhook_url
  to   = module.eu_west_1[0].aws_secretsmanager_secret.slack_webhook_url
}
moved {
  from = aws_security_group.cache_api_sg
  to   = module.eu_west_1[0].aws_security_group.cache_api_sg
}
moved {
  from = aws_security_group.cache_front_sg
  to   = module.eu_west_1[0].aws_security_group.cache_front_sg
}
moved {
  from = aws_sns_topic.alerts
  to   = module.eu_west_1[0].aws_sns_topic.alerts
}
moved {
  from = aws_sns_topic.availability-alert
  to   = module.eu_west_1[0].aws_sns_topic.availability-alert
}
moved {
  from = aws_sns_topic_subscription.subscription
  to   = module.eu_west_1[0].aws_sns_topic_subscription.subscription
}
moved {
  from = aws_sns_topic_subscription.subscription_availability
  to   = module.eu_west_1[0].aws_sns_topic_subscription.subscription_availability
}
moved {
  from = aws_subnet.private[0]
  to   = module.eu_west_1[0].aws_subnet.private[0]
}
moved {
  from = aws_subnet.private[1]
  to   = module.eu_west_1[0].aws_subnet.private[1]
}
moved {
  from = aws_subnet.private[2]
  to   = module.eu_west_1[0].aws_subnet.private[2]
}
moved {
  from = aws_subnet.public[0]
  to   = module.eu_west_1[0].aws_subnet.public[0]
}
moved {
  from = aws_subnet.public[1]
  to   = module.eu_west_1[0].aws_subnet.public[1]
}
moved {
  from = aws_subnet.public[2]
  to   = module.eu_west_1[0].aws_subnet.public[2]
}
moved {
  from = aws_vpc.main
  to   = module.eu_west_1[0].aws_vpc.main
}
moved {
  from = aws_vpc_endpoint.s3
  to   = module.eu_west_1[0].aws_vpc_endpoint.s3
}
moved {
  from = aws_wafv2_regex_pattern_set.allow_uris
  to   = module.eu_west_1[0].aws_wafv2_regex_pattern_set.allow_uris
}
moved {
  from = aws_wafv2_regex_pattern_set.block_uris
  to   = module.eu_west_1[0].aws_wafv2_regex_pattern_set.block_uris
}
moved {
  from = aws_wafv2_web_acl.main
  to   = module.eu_west_1[0].aws_wafv2_web_acl.main
}
moved {
  from = aws_wafv2_web_acl_logging_configuration.main
  to   = module.eu_west_1[0].aws_wafv2_web_acl_logging_configuration.main
}
moved {
  from = module.development_environment_secrets[0].aws_secretsmanager_secret.secret
  to   = module.eu_west_1[0].module.development_environment_secrets[0].aws_secretsmanager_secret.secret
}
moved {
  from = module.ecr_api_vpc_endpoint.aws_security_group.vpc_endpoint
  to   = module.eu_west_1[0].module.ecr_api_vpc_endpoint.aws_security_group.vpc_endpoint
}
moved {
  from = module.ecr_api_vpc_endpoint.aws_security_group_rule.vpc_endpoint_https_in
  to   = module.eu_west_1[0].module.ecr_api_vpc_endpoint.aws_security_group_rule.vpc_endpoint_https_in
}
moved {
  from = module.ecr_api_vpc_endpoint.aws_vpc_endpoint.vpc_endpoint
  to   = module.eu_west_1[0].module.ecr_api_vpc_endpoint.aws_vpc_endpoint.vpc_endpoint
}
moved {
  from = module.ecr_vpc_endpoint.aws_security_group.vpc_endpoint
  to   = module.eu_west_1[0].module.ecr_vpc_endpoint.aws_security_group.vpc_endpoint
}
moved {
  from = module.ecr_vpc_endpoint.aws_security_group_rule.vpc_endpoint_https_in
  to   = module.eu_west_1[0].module.ecr_vpc_endpoint.aws_security_group_rule.vpc_endpoint_https_in
}
moved {
  from = module.ecr_vpc_endpoint.aws_vpc_endpoint.vpc_endpoint
  to   = module.eu_west_1[0].module.ecr_vpc_endpoint.aws_vpc_endpoint.vpc_endpoint
}
moved {
  from = module.environment_secrets["default"].aws_secretsmanager_secret.secret
  to   = module.eu_west_1[0].module.environment_secrets["default"].aws_secretsmanager_secret.secret
}
moved {
  from = module.environment_secrets["development"].aws_secretsmanager_secret.secret
  to   = module.eu_west_1[0].module.environment_secrets["development"].aws_secretsmanager_secret.secret
}
moved {
  from = module.logs_vpc_endpoint.aws_security_group.vpc_endpoint
  to   = module.eu_west_1[0].module.logs_vpc_endpoint.aws_security_group.vpc_endpoint
}
moved {
  from = module.logs_vpc_endpoint.aws_security_group_rule.vpc_endpoint_https_in
  to   = module.eu_west_1[0].module.logs_vpc_endpoint.aws_security_group_rule.vpc_endpoint_https_in
}
moved {
  from = module.logs_vpc_endpoint.aws_vpc_endpoint.vpc_endpoint
  to   = module.eu_west_1[0].module.logs_vpc_endpoint.aws_vpc_endpoint.vpc_endpoint
}
moved {
  from = module.secrets_vpc_endpoint.aws_security_group.vpc_endpoint
  to   = module.eu_west_1[0].module.secrets_vpc_endpoint.aws_security_group.vpc_endpoint
}
moved {
  from = module.secrets_vpc_endpoint.aws_security_group_rule.vpc_endpoint_https_in
  to   = module.eu_west_1[0].module.secrets_vpc_endpoint.aws_security_group_rule.vpc_endpoint_https_in
}
moved {
  from = module.secrets_vpc_endpoint.aws_vpc_endpoint.vpc_endpoint
  to   = module.eu_west_1[0].module.secrets_vpc_endpoint.aws_vpc_endpoint.vpc_endpoint
}
moved {
  from = module.ssm_vpc_endpoint.aws_security_group.vpc_endpoint
  to   = module.eu_west_1[0].module.ssm_vpc_endpoint.aws_security_group.vpc_endpoint
}
moved {
  from = module.ssm_vpc_endpoint.aws_security_group_rule.vpc_endpoint_https_in
  to   = module.eu_west_1[0].module.ssm_vpc_endpoint.aws_security_group_rule.vpc_endpoint_https_in
}
moved {
  from = module.ssm_vpc_endpoint.aws_vpc_endpoint.vpc_endpoint
  to   = module.eu_west_1[0].module.ssm_vpc_endpoint.aws_vpc_endpoint.vpc_endpoint
}
moved {
  from = module.workspace-cleanup.aws_dynamodb_table.workspace_cleanup_table[0]
  to   = module.eu_west_1[0].module.workspace-cleanup.aws_dynamodb_table.workspace_cleanup_table[0]
}
moved {
  from = aws_cloudwatch_log_group.aws_route53_resolver_query_log[0]
  to   = module.eu_west_1[0].aws_cloudwatch_log_group.aws_route53_resolver_query_log[0]
}
moved {
  from = aws_cloudwatch_query_definition.dns_firewall_statistics[0]
  to   = module.eu_west_1[0].aws_cloudwatch_query_definition.dns_firewall_statistics[0]
}
moved {
  from = aws_route53_resolver_firewall_domain_list.egress_allow[0]
  to   = module.eu_west_1[0].aws_route53_resolver_firewall_domain_list.egress_allow[0]
}
moved {
  from = aws_route53_resolver_firewall_domain_list.egress_block[0]
  to   = module.eu_west_1[0].aws_route53_resolver_firewall_domain_list.egress_block[0]
}
moved {
  from = aws_route53_resolver_firewall_rule.egress_allow[0]
  to   = module.eu_west_1[0].aws_route53_resolver_firewall_rule.egress_allow[0]
}
moved {
  from = aws_route53_resolver_firewall_rule.egress_block[0]
  to   = module.eu_west_1[0].aws_route53_resolver_firewall_rule.egress_block[0]
}
moved {
  from = aws_route53_resolver_firewall_rule_group.egress[0]
  to   = module.eu_west_1[0].aws_route53_resolver_firewall_rule_group.egress[0]
}
moved {
  from = aws_route53_resolver_firewall_rule_group_association.egress[0]
  to   = module.eu_west_1[0].aws_route53_resolver_firewall_rule_group_association.egress[0]
}
moved {
  from = aws_route53_resolver_query_log_config.egress[0]
  to   = module.eu_west_1[0].aws_route53_resolver_query_log_config.egress[0]
}
moved {
  from = aws_route53_resolver_query_log_config_association.egress[0]
  to   = module.eu_west_1[0].aws_route53_resolver_query_log_config_association.egress[0]
}
moved {
  from = module.environment_secrets["production02"].aws_secretsmanager_secret.secret
  to   = module.eu_west_1[0].module.environment_secrets["production02"].aws_secretsmanager_secret.secret
}
moved {
  from = module.environment_secrets["integration"].aws_secretsmanager_secret.secret
  to   = module.eu_west_1[0].module.environment_secrets["integration"].aws_secretsmanager_secret.secret
}
moved {
  from = module.environment_secrets["preproduction"].aws_secretsmanager_secret.secret
  to   = module.eu_west_1[0].module.environment_secrets["preproduction"].aws_secretsmanager_secret.secret
}
moved {
  from = module.environment_secrets["training"].aws_secretsmanager_secret.secret
  to   = module.eu_west_1[0].module.environment_secrets["training"].aws_secretsmanager_secret.secret
}
moved {
  from = module.eu_west_1[0].module.workspace-cleanup.aws_dynamodb_table.workspace_cleanup_table[0]
  to   = module.eu_west_1[0].aws_dynamodb_table.workspace_cleanup_table[0]
}
