moved {
  from = aws_cloudwatch_dashboard.main
  to   = module.eu_west_1[0].aws_cloudwatch_dashboard.main
}
moved {
  from = aws_cloudwatch_event_rule.check_csv_uploaded_cron_rule
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.check_csv_uploaded_cron_rule
}
moved {
  from = aws_cloudwatch_event_rule.checklist_sync[0]
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.checklist_sync[0]
}
moved {
  from = aws_cloudwatch_event_rule.cross_account_backup_check
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.cross_account_backup_check
}
moved {
  from = aws_cloudwatch_event_rule.db_analyse_command
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.db_analyse_command
}
moved {
  from = aws_cloudwatch_event_rule.db_analyse_command_check
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.db_analyse_command_check
}
moved {
  from = aws_cloudwatch_event_rule.delete_inactive_users
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.delete_inactive_users
}
moved {
  from = aws_cloudwatch_event_rule.delete_inactive_users_check
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.delete_inactive_users_check
}
moved {
  from = aws_cloudwatch_event_rule.delete_zero_activity_users
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.delete_zero_activity_users
}
moved {
  from = aws_cloudwatch_event_rule.delete_zero_activity_users_check
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.delete_zero_activity_users_check
}
moved {
  from = aws_cloudwatch_event_rule.document_sync[0]
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.document_sync[0]
}
moved {
  from = aws_cloudwatch_event_rule.redeploy_file_scanner
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.redeploy_file_scanner
}
moved {
  from = aws_cloudwatch_event_rule.resubmit_error_documents
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.resubmit_error_documents
}
moved {
  from = aws_cloudwatch_event_rule.resubmit_error_documents_check
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.resubmit_error_documents_check
}
moved {
  from = aws_cloudwatch_event_rule.sync_checklists
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.sync_checklists
}
moved {
  from = aws_cloudwatch_event_rule.sync_checklists_check
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.sync_checklists_check
}
moved {
  from = aws_cloudwatch_event_rule.sync_documents
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.sync_documents
}
moved {
  from = aws_cloudwatch_event_rule.sync_documents_check
  to   = module.eu_west_1[0].aws_cloudwatch_event_rule.sync_documents_check
}
moved {
  from = aws_cloudwatch_event_target.check_csv_uploaded_scheduled_task
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.check_csv_uploaded_scheduled_task
}
moved {
  from = aws_cloudwatch_event_target.checklist_sync[0]
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.checklist_sync[0]
}
moved {
  from = aws_cloudwatch_event_target.cross_account_backup_check
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.cross_account_backup_check
}
moved {
  from = aws_cloudwatch_event_target.db_analyse_command
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.db_analyse_command
}
moved {
  from = aws_cloudwatch_event_target.db_analyse_command_check
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.db_analyse_command_check
}
moved {
  from = aws_cloudwatch_event_target.delete_inactive_users
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.delete_inactive_users
}
moved {
  from = aws_cloudwatch_event_target.delete_inactive_users_check
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.delete_inactive_users_check
}
moved {
  from = aws_cloudwatch_event_target.delete_zero_activity_users
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.delete_zero_activity_users
}
moved {
  from = aws_cloudwatch_event_target.delete_zero_activity_users_check
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.delete_zero_activity_users_check
}
moved {
  from = aws_cloudwatch_event_target.document_sync[0]
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.document_sync[0]
}
moved {
  from = aws_cloudwatch_event_target.redeploy_file_scanner
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.redeploy_file_scanner
}
moved {
  from = aws_cloudwatch_event_target.resubmit_error_documents
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.resubmit_error_documents
}
moved {
  from = aws_cloudwatch_event_target.resubmit_error_documents_check
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.resubmit_error_documents_check
}
moved {
  from = aws_cloudwatch_event_target.sync_checklists
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.sync_checklists
}
moved {
  from = aws_cloudwatch_event_target.sync_checklists_check
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.sync_checklists_check
}
moved {
  from = aws_cloudwatch_event_target.sync_documents
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.sync_documents
}
moved {
  from = aws_cloudwatch_event_target.sync_documents_check
  to   = module.eu_west_1[0].aws_cloudwatch_event_target.sync_documents_check
}
moved {
  from = aws_cloudwatch_log_group.api_cluster
  to   = module.eu_west_1[0].aws_cloudwatch_log_group.api_cluster
}
moved {
  from = aws_cloudwatch_log_group.audit
  to   = module.eu_west_1[0].aws_cloudwatch_log_group.audit
}
moved {
  from = aws_cloudwatch_log_group.container_insights
  to   = module.eu_west_1[0].aws_cloudwatch_log_group.container_insights
}
moved {
  from = aws_cloudwatch_log_group.opg_digi_deps
  to   = module.eu_west_1[0].aws_cloudwatch_log_group.opg_digi_deps
}
moved {
  from = aws_cloudwatch_log_metric_filter.admin_5xx_errors
  to   = module.eu_west_1[0].aws_cloudwatch_log_metric_filter.admin_5xx_errors
}
moved {
  from = aws_cloudwatch_log_metric_filter.api_5xx_errors
  to   = module.eu_west_1[0].aws_cloudwatch_log_metric_filter.api_5xx_errors
}
moved {
  from = aws_cloudwatch_log_metric_filter.document_in_progress_more_than_hour
  to   = module.eu_west_1[0].aws_cloudwatch_log_metric_filter.document_in_progress_more_than_hour
}
moved {
  from = aws_cloudwatch_log_metric_filter.document_permanent_error
  to   = module.eu_west_1[0].aws_cloudwatch_log_metric_filter.document_permanent_error
}
moved {
  from = aws_cloudwatch_log_metric_filter.document_queued_more_than_hour
  to   = module.eu_west_1[0].aws_cloudwatch_log_metric_filter.document_queued_more_than_hour
}
moved {
  from = aws_cloudwatch_log_metric_filter.document_temporary_error
  to   = module.eu_west_1[0].aws_cloudwatch_log_metric_filter.document_temporary_error
}
moved {
  from = aws_cloudwatch_log_metric_filter.frontend_5xx_errors
  to   = module.eu_west_1[0].aws_cloudwatch_log_metric_filter.frontend_5xx_errors
}
moved {
  from = aws_cloudwatch_log_metric_filter.php_critical_errors
  to   = module.eu_west_1[0].aws_cloudwatch_log_metric_filter.php_critical_errors
}
moved {
  from = aws_cloudwatch_log_metric_filter.php_errors
  to   = module.eu_west_1[0].aws_cloudwatch_log_metric_filter.php_errors
}
moved {
  from = aws_cloudwatch_log_metric_filter.pre_registration_add_in_progress
  to   = module.eu_west_1[0].aws_cloudwatch_log_metric_filter.pre_registration_add_in_progress
}
moved {
  from = aws_cloudwatch_metric_alarm.admin_5xx_errors
  to   = module.eu_west_1[0].aws_cloudwatch_metric_alarm.admin_5xx_errors
}
moved {
  from = aws_cloudwatch_metric_alarm.admin_alb_5xx_errors
  to   = module.eu_west_1[0].aws_cloudwatch_metric_alarm.admin_alb_5xx_errors
}
moved {
  from = aws_cloudwatch_metric_alarm.admin_alb_average_response_time
  to   = module.eu_west_1[0].aws_cloudwatch_metric_alarm.admin_alb_average_response_time
}
moved {
  from = aws_cloudwatch_metric_alarm.admin_ddos_attack_external
  to   = module.eu_west_1[0].aws_cloudwatch_metric_alarm.admin_ddos_attack_external
}
moved {
  from = aws_cloudwatch_metric_alarm.api_5xx_errors
  to   = module.eu_west_1[0].aws_cloudwatch_metric_alarm.api_5xx_errors
}
moved {
  from = aws_cloudwatch_metric_alarm.document_permanent_error
  to   = module.eu_west_1[0].aws_cloudwatch_metric_alarm.document_permanent_error
}
moved {
  from = aws_cloudwatch_metric_alarm.document_progress_more_than_hour
  to   = module.eu_west_1[0].aws_cloudwatch_metric_alarm.document_progress_more_than_hour
}
moved {
  from = aws_cloudwatch_metric_alarm.document_queued_more_than_hour
  to   = module.eu_west_1[0].aws_cloudwatch_metric_alarm.document_queued_more_than_hour
}
moved {
  from = aws_cloudwatch_metric_alarm.document_temporary_error
  to   = module.eu_west_1[0].aws_cloudwatch_metric_alarm.document_temporary_error
}
moved {
  from = aws_cloudwatch_metric_alarm.front_ddos_attack_external
  to   = module.eu_west_1[0].aws_cloudwatch_metric_alarm.front_ddos_attack_external
}
moved {
  from = aws_cloudwatch_metric_alarm.frontend_5xx_errors
  to   = module.eu_west_1[0].aws_cloudwatch_metric_alarm.frontend_5xx_errors
}
moved {
  from = aws_cloudwatch_metric_alarm.frontend_alb_5xx_errors
  to   = module.eu_west_1[0].aws_cloudwatch_metric_alarm.frontend_alb_5xx_errors
}
moved {
  from = aws_cloudwatch_metric_alarm.frontend_alb_average_response_time
  to   = module.eu_west_1[0].aws_cloudwatch_metric_alarm.frontend_alb_average_response_time
}
moved {
  from = aws_cloudwatch_metric_alarm.php_critical_errors
  to   = module.eu_west_1[0].aws_cloudwatch_metric_alarm.php_critical_errors
}
moved {
  from = aws_cloudwatch_metric_alarm.php_errors
  to   = module.eu_west_1[0].aws_cloudwatch_metric_alarm.php_errors
}
moved {
  from = aws_cloudwatch_query_definition.sign_up_matching_errors
  to   = module.eu_west_1[0].aws_cloudwatch_query_definition.sign_up_matching_errors
}
moved {
  from = aws_ecs_cluster.main
  to   = module.eu_west_1[0].aws_ecs_cluster.main
}
moved {
  from = aws_ecs_service.admin
  to   = module.eu_west_1[0].aws_ecs_service.admin
}
moved {
  from = aws_ecs_service.api
  to   = module.eu_west_1[0].aws_ecs_service.api
}
moved {
  from = aws_ecs_service.checklist_sync
  to   = module.eu_west_1[0].aws_ecs_service.checklist_sync
}
moved {
  from = aws_ecs_service.document_sync
  to   = module.eu_west_1[0].aws_ecs_service.document_sync
}
moved {
  from = aws_ecs_service.front
  to   = module.eu_west_1[0].aws_ecs_service.front
}
moved {
  from = aws_ecs_service.htmltopdf
  to   = module.eu_west_1[0].aws_ecs_service.htmltopdf
}
moved {
  from = aws_ecs_service.mock_sirius_integration
  to   = module.eu_west_1[0].aws_ecs_service.mock_sirius_integration
}
moved {
  from = aws_ecs_service.scan
  to   = module.eu_west_1[0].aws_ecs_service.scan
}
moved {
  from = aws_ecs_task_definition.admin
  to   = module.eu_west_1[0].aws_ecs_task_definition.admin
}
moved {
  from = aws_ecs_task_definition.api
  to   = module.eu_west_1[0].aws_ecs_task_definition.api
}
moved {
  from = aws_ecs_task_definition.check_csv_uploaded
  to   = module.eu_west_1[0].aws_ecs_task_definition.check_csv_uploaded
}
moved {
  from = aws_ecs_task_definition.checklist_sync
  to   = module.eu_west_1[0].aws_ecs_task_definition.checklist_sync
}
moved {
  from = aws_ecs_task_definition.document_sync
  to   = module.eu_west_1[0].aws_ecs_task_definition.document_sync
}
moved {
  from = aws_ecs_task_definition.front
  to   = module.eu_west_1[0].aws_ecs_task_definition.front
}
moved {
  from = aws_ecs_task_definition.htmltopdf
  to   = module.eu_west_1[0].aws_ecs_task_definition.htmltopdf
}
moved {
  from = aws_ecs_task_definition.mock_sirius_integration
  to   = module.eu_west_1[0].aws_ecs_task_definition.mock_sirius_integration
}
moved {
  from = aws_ecs_task_definition.scan
  to   = module.eu_west_1[0].aws_ecs_task_definition.scan
}
moved {
  from = aws_iam_instance_profile.backup
  to   = module.eu_west_1[0].aws_iam_instance_profile.backup
}
moved {
  from = aws_iam_policy.backup_policy
  to   = module.eu_west_1[0].aws_iam_policy.backup_policy
}
moved {
  from = aws_iam_role.admin
  to   = module.eu_west_1[0].aws_iam_role.admin
}
moved {
  from = aws_iam_role.api
  to   = module.eu_west_1[0].aws_iam_role.api
}
moved {
  from = aws_iam_role.backup_role
  to   = module.eu_west_1[0].aws_iam_role.backup_role
}
moved {
  from = aws_iam_role.events_task_runner
  to   = module.eu_west_1[0].aws_iam_role.events_task_runner
}
moved {
  from = aws_iam_role.execution_role
  to   = module.eu_west_1[0].aws_iam_role.execution_role
}
moved {
  from = aws_iam_role.front
  to   = module.eu_west_1[0].aws_iam_role.front
}
moved {
  from = aws_iam_role.htmltopdf
  to   = module.eu_west_1[0].aws_iam_role.htmltopdf
}
moved {
  from = aws_iam_role.mock_sirius_integration
  to   = module.eu_west_1[0].aws_iam_role.mock_sirius_integration
}
moved {
  from = aws_iam_role.scan
  to   = module.eu_west_1[0].aws_iam_role.scan
}
moved {
  from = aws_iam_role.test
  to   = module.eu_west_1[0].aws_iam_role.test
}
moved {
  from = aws_iam_role_policy.admin_put_parameter_ssm_integration_tests
  to   = module.eu_west_1[0].aws_iam_role_policy.admin_put_parameter_ssm_integration_tests
}
moved {
  from = aws_iam_role_policy.admin_query_ssm
  to   = module.eu_west_1[0].aws_iam_role_policy.admin_query_ssm
}
moved {
  from = aws_iam_role_policy.admin_s3
  to   = module.eu_west_1[0].aws_iam_role_policy.admin_s3
}
moved {
  from = aws_iam_role_policy.admin_task_logs
  to   = module.eu_west_1[0].aws_iam_role_policy.admin_task_logs
}
moved {
  from = aws_iam_role_policy.api_query_ssm
  to   = module.eu_west_1[0].aws_iam_role_policy.api_query_ssm
}
moved {
  from = aws_iam_role_policy.api_task_logs
  to   = module.eu_west_1[0].aws_iam_role_policy.api_task_logs
}
moved {
  from = aws_iam_role_policy.events_task_runner
  to   = module.eu_west_1[0].aws_iam_role_policy.events_task_runner
}
moved {
  from = aws_iam_role_policy.execution_role
  to   = module.eu_west_1[0].aws_iam_role_policy.execution_role
}
moved {
  from = aws_iam_role_policy.front_get_log_events
  to   = module.eu_west_1[0].aws_iam_role_policy.front_get_log_events
}
moved {
  from = aws_iam_role_policy.front_query_secretsmanager
  to   = module.eu_west_1[0].aws_iam_role_policy.front_query_secretsmanager
}
moved {
  from = aws_iam_role_policy.front_query_ssm
  to   = module.eu_west_1[0].aws_iam_role_policy.front_query_ssm
}
moved {
  from = aws_iam_role_policy.front_s3
  to   = module.eu_west_1[0].aws_iam_role_policy.front_s3
}
moved {
  from = aws_iam_role_policy.front_task_logs
  to   = module.eu_west_1[0].aws_iam_role_policy.front_task_logs
}
moved {
  from = aws_iam_role_policy.invoke_dep_rep_api
  to   = module.eu_west_1[0].aws_iam_role_policy.invoke_dep_rep_api
}
moved {
  from = aws_iam_role_policy_attachment.backup_policy_attachment
  to   = module.eu_west_1[0].aws_iam_role_policy_attachment.backup_policy_attachment
}
moved {
  from = aws_iam_role_policy_attachment.replication
  to   = module.eu_west_1[0].aws_iam_role_policy_attachment.replication
}
moved {
  from = aws_kms_alias.cloudwatch_logs_alias
  to   = module.eu_west_1[0].aws_kms_alias.cloudwatch_logs_alias
}
moved {
  from = aws_kms_alias.s3
  to   = module.eu_west_1[0].aws_kms_alias.s3
}
moved {
  from = aws_kms_key.cloudwatch_logs
  to   = module.eu_west_1[0].aws_kms_key.cloudwatch_logs
}
moved {
  from = aws_kms_key.s3
  to   = module.eu_west_1[0].aws_kms_key.s3
}
moved {
  from = aws_lambda_permission.allow_cloudwatch_call_lambda
  to   = module.eu_west_1[0].aws_lambda_permission.allow_cloudwatch_call_lambda
}
moved {
  from = aws_lambda_permission.allow_cloudwatch_checklists_to_call_synchronise_lambda
  to   = module.eu_west_1[0].aws_lambda_permission.allow_cloudwatch_checklists_to_call_synchronise_lambda
}
moved {
  from = aws_lambda_permission.allow_cloudwatch_documents_to_call_synchronise_lambda
  to   = module.eu_west_1[0].aws_lambda_permission.allow_cloudwatch_documents_to_call_synchronise_lambda
}
moved {
  from = aws_lb.admin
  to   = module.eu_west_1[0].aws_lb.admin
}
moved {
  from = aws_lb.front
  to   = module.eu_west_1[0].aws_lb.front
}
moved {
  from = aws_lb_listener.admin
  to   = module.eu_west_1[0].aws_lb_listener.admin
}
moved {
  from = aws_lb_listener.admin_http
  to   = module.eu_west_1[0].aws_lb_listener.admin_http
}
moved {
  from = aws_lb_listener.front_http
  to   = module.eu_west_1[0].aws_lb_listener.front_http
}
moved {
  from = aws_lb_listener.front_https
  to   = module.eu_west_1[0].aws_lb_listener.front_https
}
moved {
  from = aws_lb_listener_certificate.admin_loadbalancer_service_admin_certificate
  to   = module.eu_west_1[0].aws_lb_listener_certificate.admin_loadbalancer_service_admin_certificate
}
moved {
  from = aws_lb_listener_certificate.admin_loadbalancer_service_certificate
  to   = module.eu_west_1[0].aws_lb_listener_certificate.admin_loadbalancer_service_certificate
}
moved {
  from = aws_lb_listener_certificate.front_loadbalancer_service_certificate
  to   = module.eu_west_1[0].aws_lb_listener_certificate.front_loadbalancer_service_certificate
}
moved {
  from = aws_lb_listener_rule.admin_maintenance
  to   = module.eu_west_1[0].aws_lb_listener_rule.admin_maintenance
}
moved {
  from = aws_lb_listener_rule.front_maintenance
  to   = module.eu_west_1[0].aws_lb_listener_rule.front_maintenance
}
moved {
  from = aws_lb_target_group.admin
  to   = module.eu_west_1[0].aws_lb_target_group.admin
}
moved {
  from = aws_lb_target_group.front
  to   = module.eu_west_1[0].aws_lb_target_group.front
}
moved {
  from = aws_route53_record.api_postgres
  to   = module.eu_west_1[0].aws_route53_record.api_postgres
}
moved {
  from = aws_route53_record.api_redis
  to   = module.eu_west_1[0].aws_route53_record.api_redis
}
moved {
  from = aws_route53_record.frontend_redis
  to   = module.eu_west_1[0].aws_route53_record.frontend_redis
}
moved {
  from = aws_route53_zone.internal
  to   = module.eu_west_1[0].aws_route53_zone.internal
}
moved {
  from = aws_security_group_rule.admin_elb_http_in
  to   = module.eu_west_1[0].aws_security_group_rule.admin_elb_http_in
}
moved {
  from = aws_security_group_rule.admin_elb_https_in
  to   = module.eu_west_1[0].aws_security_group_rule.admin_elb_https_in
}
moved {
  from = aws_security_group_rule.admin_elb_route53_hc_in
  to   = module.eu_west_1[0].aws_security_group_rule.admin_elb_route53_hc_in
}
moved {
  from = aws_security_group_rule.admin_to_redis
  to   = module.eu_west_1[0].aws_security_group_rule.admin_to_redis
}
moved {
  from = aws_security_group_rule.api_to_redis
  to   = module.eu_west_1[0].aws_security_group_rule.api_to_redis
}
moved {
  from = aws_security_group_rule.front_elb_http_in
  to   = module.eu_west_1[0].aws_security_group_rule.front_elb_http_in
}
moved {
  from = aws_security_group_rule.front_elb_https_in
  to   = module.eu_west_1[0].aws_security_group_rule.front_elb_https_in
}
moved {
  from = aws_security_group_rule.front_elb_route53_hc_in
  to   = module.eu_west_1[0].aws_security_group_rule.front_elb_route53_hc_in
}
moved {
  from = aws_security_group_rule.front_to_redis
  to   = module.eu_west_1[0].aws_security_group_rule.front_to_redis
}
moved {
  from = aws_security_group_rule.lambda_sync_to_front
  to   = module.eu_west_1[0].aws_security_group_rule.lambda_sync_to_front
}
moved {
  from = aws_security_group_rule.lambda_sync_to_secrets_endpoint
  to   = module.eu_west_1[0].aws_security_group_rule.lambda_sync_to_secrets_endpoint
}
moved {
  from = aws_service_discovery_http_namespace.cloudmap_namespace
  to   = module.eu_west_1[0].aws_service_discovery_http_namespace.cloudmap_namespace
}
moved {
  from = aws_shield_protection.admin_alb_protection
  to   = module.eu_west_1[0].aws_shield_protection.admin_alb_protection
}
moved {
  from = aws_shield_protection.front_alb_protection
  to   = module.eu_west_1[0].aws_shield_protection.front_alb_protection
}
moved {
  from = aws_shield_protection_health_check_association.admin
  to   = module.eu_west_1[0].aws_shield_protection_health_check_association.admin
}
moved {
  from = aws_shield_protection_health_check_association.front
  to   = module.eu_west_1[0].aws_shield_protection_health_check_association.front
}
moved {
  from = aws_ssm_parameter.checklist_sync_row_limit
  to   = module.eu_west_1[0].aws_ssm_parameter.checklist_sync_row_limit
}
moved {
  from = aws_ssm_parameter.document_sync_row_limit
  to   = module.eu_west_1[0].aws_ssm_parameter.document_sync_row_limit
}
moved {
  from = aws_ssm_parameter.flag_checklist_sync
  to   = module.eu_west_1[0].aws_ssm_parameter.flag_checklist_sync
}
moved {
  from = aws_ssm_parameter.flag_document_sync
  to   = module.eu_west_1[0].aws_ssm_parameter.flag_document_sync
}
moved {
  from = aws_ssm_parameter.flag_paper_reports
  to   = module.eu_west_1[0].aws_ssm_parameter.flag_paper_reports
}
moved {
  from = aws_ssm_parameter.sirius_api_base_uri
  to   = module.eu_west_1[0].aws_ssm_parameter.sirius_api_base_uri
}
moved {
  from = aws_wafv2_web_acl_association.admin[0]
  to   = module.eu_west_1[0].aws_wafv2_web_acl_association.admin[0]
}
moved {
  from = aws_wafv2_web_acl_association.front[0]
  to   = module.eu_west_1[0].aws_wafv2_web_acl_association.front[0]
}
moved {
  from = data.aws_iam_policy_document.admin_put_parameter_ssm
  to   = module.eu_west_1[0].data.aws_iam_policy_document.admin_put_parameter_ssm
}
moved {
  from = data.aws_iam_policy_document.admin_s3
  to   = module.eu_west_1[0].data.aws_iam_policy_document.admin_s3
}
moved {
  from = data.aws_iam_policy_document.api_permissions
  to   = module.eu_west_1[0].data.aws_iam_policy_document.api_permissions
}
moved {
  from = data.aws_iam_policy_document.ecs_task_logs
  to   = module.eu_west_1[0].data.aws_iam_policy_document.ecs_task_logs
}
moved {
  from = data.aws_iam_policy_document.events_task_runner_policy
  to   = module.eu_west_1[0].data.aws_iam_policy_document.events_task_runner_policy
}
moved {
  from = data.aws_iam_policy_document.front_get_log_events
  to   = module.eu_west_1[0].data.aws_iam_policy_document.front_get_log_events
}
moved {
  from = data.aws_iam_policy_document.front_query_ssm
  to   = module.eu_west_1[0].data.aws_iam_policy_document.front_query_ssm
}
moved {
  from = data.aws_iam_policy_document.front_s3
  to   = module.eu_west_1[0].data.aws_iam_policy_document.front_s3
}
moved {
  from = data.aws_iam_policy_document.replication_policy
  to   = module.eu_west_1[0].data.aws_iam_policy_document.replication_policy
}
moved {
  from = module.admin_elb_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.admin_elb_security_group.aws_security_group.group
}
moved {
  from = module.admin_elb_security_group.aws_security_group_rule.rules["admin_service_http"]
  to   = module.eu_west_1[0].module.admin_elb_security_group.aws_security_group_rule.rules["admin_service_http"]
}
moved {
  from = module.admin_elb_security_group_route53_hc.aws_security_group.group
  to   = module.eu_west_1[0].module.admin_elb_security_group_route53_hc.aws_security_group.group
}
moved {
  from = module.admin_elb_security_group_route53_hc.aws_security_group_rule.rules["admin_service_http"]
  to   = module.eu_west_1[0].module.admin_elb_security_group_route53_hc.aws_security_group_rule.rules["admin_service_http"]
}
moved {
  from = module.admin_service_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.admin_service_security_group.aws_security_group.group
}
moved {
  from = module.admin_service_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.admin_service_security_group.aws_security_group_rule.rules
}
moved {
  from = module.analyse.aws_ecs_task_definition.task
  to   = module.eu_west_1[0].module.analyse.aws_ecs_task_definition.task
}
moved {
  from = module.api_aurora[0].aws_rds_cluster.cluster[0]
  to   = module.eu_west_1[0].module.api_aurora[0].aws_rds_cluster.cluster[0]
}
moved {
  from = module.api_aurora[0].aws_rds_cluster_instance.cluster_instances[0]
  to   = module.eu_west_1[0].module.api_aurora[0].aws_rds_cluster_instance.cluster_instances[0]
}
moved {
  from = module.api_aurora[0].aws_rds_cluster_instance.cluster_instances[1]
  to   = module.eu_west_1[0].module.api_aurora[0].aws_rds_cluster_instance.cluster_instances[1]
}
moved {
  from = module.api_ecs_autoscaling.aws_appautoscaling_policy.down
  to   = module.eu_west_1[0].module.api_ecs_autoscaling.aws_appautoscaling_policy.down
}
moved {
  from = module.api_ecs_autoscaling.aws_appautoscaling_policy.up
  to   = module.eu_west_1[0].module.api_ecs_autoscaling.aws_appautoscaling_policy.up
}
moved {
  from = module.api_ecs_autoscaling.aws_appautoscaling_target.target
  to   = module.eu_west_1[0].module.api_ecs_autoscaling.aws_appautoscaling_target.target
}
moved {
  from = module.api_ecs_autoscaling.aws_cloudwatch_metric_alarm.scale_down
  to   = module.eu_west_1[0].module.api_ecs_autoscaling.aws_cloudwatch_metric_alarm.scale_down
}
moved {
  from = module.api_ecs_autoscaling.aws_cloudwatch_metric_alarm.scale_up
  to   = module.eu_west_1[0].module.api_ecs_autoscaling.aws_cloudwatch_metric_alarm.scale_up
}
moved {
  from = module.api_rds_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.api_rds_security_group.aws_security_group.group
}
moved {
  from = module.api_rds_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.api_rds_security_group.aws_security_group_rule.rules
}
moved {
  from = module.api_service_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.api_service_security_group.aws_security_group.group
}
moved {
  from = module.api_service_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.api_service_security_group.aws_security_group_rule.rules
}
moved {
  from = module.backup.aws_ecs_task_definition.task
  to   = module.eu_west_1[0].module.backup.aws_ecs_task_definition.task
}
moved {
  from = module.backup_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.backup_security_group.aws_security_group.group
}
moved {
  from = module.backup_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.backup_security_group.aws_security_group_rule.rules
}
moved {
  from = module.check_csv_uploaded_service_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.check_csv_uploaded_service_security_group.aws_security_group.group
}
moved {
  from = module.check_csv_uploaded_service_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.check_csv_uploaded_service_security_group.aws_security_group_rule.rules
}
moved {
  from = module.checklist_sync_service_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.checklist_sync_service_security_group.aws_security_group.group
}
moved {
  from = module.checklist_sync_service_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.checklist_sync_service_security_group.aws_security_group_rule.rules
}
moved {
  from = module.document_sync_service_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.document_sync_service_security_group.aws_security_group.group
}
moved {
  from = module.document_sync_service_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.document_sync_service_security_group.aws_security_group_rule.rules
}
moved {
  from = module.front_ecs_autoscaling.aws_appautoscaling_policy.down
  to   = module.eu_west_1[0].module.front_ecs_autoscaling.aws_appautoscaling_policy.down
}
moved {
  from = module.front_ecs_autoscaling.aws_appautoscaling_policy.up
  to   = module.eu_west_1[0].module.front_ecs_autoscaling.aws_appautoscaling_policy.up
}
moved {
  from = module.front_ecs_autoscaling.aws_appautoscaling_target.target
  to   = module.eu_west_1[0].module.front_ecs_autoscaling.aws_appautoscaling_target.target
}
moved {
  from = module.front_ecs_autoscaling.aws_cloudwatch_metric_alarm.scale_down
  to   = module.eu_west_1[0].module.front_ecs_autoscaling.aws_cloudwatch_metric_alarm.scale_down
}
moved {
  from = module.front_ecs_autoscaling.aws_cloudwatch_metric_alarm.scale_up
  to   = module.eu_west_1[0].module.front_ecs_autoscaling.aws_cloudwatch_metric_alarm.scale_up
}
moved {
  from = module.front_elb_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.front_elb_security_group.aws_security_group.group
}
moved {
  from = module.front_elb_security_group.aws_security_group_rule.rules["front_service_http"]
  to   = module.eu_west_1[0].module.front_elb_security_group.aws_security_group_rule.rules["front_service_http"]
}
moved {
  from = module.front_elb_security_group_route53_hc.aws_security_group.group
  to   = module.eu_west_1[0].module.front_elb_security_group_route53_hc.aws_security_group.group
}
moved {
  from = module.front_elb_security_group_route53_hc.aws_security_group_rule.rules["front_service_http"]
  to   = module.eu_west_1[0].module.front_elb_security_group_route53_hc.aws_security_group_rule.rules["front_service_http"]
}
moved {
  from = module.front_service_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.front_service_security_group.aws_security_group.group
}
moved {
  from = module.front_service_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.front_service_security_group.aws_security_group_rule.rules
}
moved {
  from = module.htmltopdf_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.htmltopdf_security_group.aws_security_group.group
}
moved {
  from = module.htmltopdf_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.htmltopdf_security_group.aws_security_group_rule.rules
}
moved {
  from = module.integration_test_v2_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.integration_test_v2_security_group.aws_security_group.group
}
moved {
  from = module.integration_test_v2_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.integration_test_v2_security_group.aws_security_group_rule.rules
}
moved {
  from = module.integration_test_v2.aws_ecs_task_definition.task
  to   = module.eu_west_1[0].module.integration_tests.aws_ecs_task_definition.task
}
moved {
  from = module.eu_west_1[0].module.integration_test_v2_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.integration_tests_security_group.aws_security_group.group
}
moved {
  from = module.eu_west_1[0].module.integration_test_v2_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.integration_tests_security_group.aws_security_group_rule.rules
}
moved {
  from = module.lamdba_synchronisation.aws_cloudwatch_log_group.lambda
  to   = module.eu_west_1[0].module.lamdba_synchronisation.aws_cloudwatch_log_group.lambda
}
moved {
  from = module.lamdba_synchronisation.aws_iam_role.lambda_role
  to   = module.eu_west_1[0].module.lamdba_synchronisation.aws_iam_role.lambda_role
}
moved {
  from = module.lamdba_synchronisation.aws_iam_role_policy.lambda
  to   = module.eu_west_1[0].module.lamdba_synchronisation.aws_iam_role_policy.lambda
}
moved {
  from = module.lamdba_synchronisation.aws_iam_role_policy_attachment.aws_xray_write_only_access
  to   = module.eu_west_1[0].module.lamdba_synchronisation.aws_iam_role_policy_attachment.aws_xray_write_only_access
}
moved {
  from = module.lamdba_synchronisation.aws_iam_role_policy_attachment.vpc_access_execution_role
  to   = module.eu_west_1[0].module.lamdba_synchronisation.aws_iam_role_policy_attachment.vpc_access_execution_role
}
moved {
  from = module.lamdba_synchronisation.aws_lambda_function.lambda_function
  to   = module.eu_west_1[0].module.lamdba_synchronisation.aws_lambda_function.lambda_function
}
moved {
  from = module.lamdba_synchronisation.aws_security_group.lambda
  to   = module.eu_west_1[0].module.lamdba_synchronisation.aws_security_group.lambda
}
moved {
  from = module.lamdba_synchronisation.data.aws_iam_policy_document.lambda
  to   = module.eu_west_1[0].module.lamdba_synchronisation.data.aws_iam_policy_document.lambda
}
moved {
  from = module.mock_sirius_integration_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.mock_sirius_integration_security_group.aws_security_group.group
}
moved {
  from = module.mock_sirius_integration_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.mock_sirius_integration_security_group.aws_security_group_rule.rules
}
moved {
  from = module.pa_uploads.aws_s3_bucket.bucket
  to   = module.eu_west_1[0].module.pa_uploads.aws_s3_bucket.bucket
}
moved {
  from = module.pa_uploads.aws_s3_bucket_lifecycle_configuration.bucket[0]
  to   = module.eu_west_1[0].module.pa_uploads.aws_s3_bucket_lifecycle_configuration.bucket[0]
}
moved {
  from = module.pa_uploads.aws_s3_bucket_logging.bucket
  to   = module.eu_west_1[0].module.pa_uploads.aws_s3_bucket_logging.bucket
}
moved {
  from = module.pa_uploads.aws_s3_bucket_ownership_controls.bucket_object_ownership
  to   = module.eu_west_1[0].module.pa_uploads.aws_s3_bucket_ownership_controls.bucket_object_ownership
}
moved {
  from = module.pa_uploads.aws_s3_bucket_policy.bucket
  to   = module.eu_west_1[0].module.pa_uploads.aws_s3_bucket_policy.bucket
}
moved {
  from = module.pa_uploads.aws_s3_bucket_public_access_block.public_access_policy
  to   = module.eu_west_1[0].module.pa_uploads.aws_s3_bucket_public_access_block.public_access_policy
}
moved {
  from = module.pa_uploads.aws_s3_bucket_replication_configuration.bucket_replication
  to   = module.eu_west_1[0].module.pa_uploads.aws_s3_bucket_replication_configuration.bucket_replication
}
moved {
  from = module.pa_uploads.aws_s3_bucket_server_side_encryption_configuration.bucket_encryption_configuration
  to   = module.eu_west_1[0].module.pa_uploads.aws_s3_bucket_server_side_encryption_configuration.bucket_encryption_configuration
}
moved {
  from = module.pa_uploads.aws_s3_bucket_versioning.bucket_versioning
  to   = module.eu_west_1[0].module.pa_uploads.aws_s3_bucket_versioning.bucket_versioning
}
moved {
  from = module.pa_uploads.data.aws_iam_policy_document.bucket
  to   = module.eu_west_1[0].module.pa_uploads.data.aws_iam_policy_document.bucket
}
moved {
  from = module.reset_database.aws_ecs_task_definition.task
  to   = module.eu_west_1[0].module.reset_database.aws_ecs_task_definition.task
}
moved {
  from = module.reset_database_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.reset_database_security_group.aws_security_group.group
}
moved {
  from = module.reset_database_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.reset_database_security_group.aws_security_group_rule.rules
}
moved {
  from = module.restore.aws_ecs_task_definition.task
  to   = module.eu_west_1[0].module.restore.aws_ecs_task_definition.task
}
moved {
  from = module.restore_from_production.aws_ecs_task_definition.task
  to   = module.eu_west_1[0].module.restore_from_production.aws_ecs_task_definition.task
}
moved {
  from = module.restore_from_production_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.restore_from_production_security_group.aws_security_group.group
}
moved {
  from = module.restore_from_production_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.restore_from_production_security_group.aws_security_group_rule.rules
}
moved {
  from = module.restore_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.restore_security_group.aws_security_group.group
}
moved {
  from = module.restore_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.restore_security_group.aws_security_group_rule.rules
}
moved {
  from = module.scan_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.scan_security_group.aws_security_group.group
}
moved {
  from = module.scan_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.scan_security_group.aws_security_group_rule.rules
}
moved {
  from = module.smoke_test.aws_ecs_task_definition.task
  to   = module.eu_west_1[0].module.smoke_test.aws_ecs_task_definition.task
}
moved {
  from = module.smoke_test_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.smoke_test_security_group.aws_security_group.group
}
moved {
  from = module.smoke_test_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.smoke_test_security_group.aws_security_group_rule.rules
}
moved {
  from = module.api_aurora[0].aws_rds_cluster_instance.cluster_instances[2]
  to   = module.eu_west_1[0].module.api_aurora[0].aws_rds_cluster_instance.cluster_instances[2]
}
moved {
  from = module.disaster_recovery_backup[0].aws_cloudwatch_event_rule.dr_backup
  to   = module.eu_west_1[0].module.disaster_recovery_backup[0].aws_cloudwatch_event_rule.dr_backup
}
moved {
  from = module.disaster_recovery_backup[0].aws_cloudwatch_event_target.dr_backup
  to   = module.eu_west_1[0].module.disaster_recovery_backup[0].aws_cloudwatch_event_target.dr_backup
}
moved {
  from = module.disaster_recovery_backup[0].aws_cloudwatch_log_group.dr_backup
  to   = module.eu_west_1[0].module.disaster_recovery_backup[0].aws_cloudwatch_log_group.dr_backup
}
moved {
  from = module.disaster_recovery_backup[0].aws_ecs_task_definition.dr_backup
  to   = module.eu_west_1[0].module.disaster_recovery_backup[0].aws_ecs_task_definition.dr_backup
}
moved {
  from = module.disaster_recovery_backup[0].aws_iam_role.dr_backup
  to   = module.eu_west_1[0].module.disaster_recovery_backup[0].aws_iam_role.dr_backup
}
moved {
  from = module.disaster_recovery_backup[0].aws_iam_role_policy.dr_backup
  to   = module.eu_west_1[0].module.disaster_recovery_backup[0].aws_iam_role_policy.dr_backup
}
moved {
  from = module.disaster_recovery_backup[0].aws_kms_alias.backup_kms_alias
  to   = module.eu_west_1[0].module.disaster_recovery_backup[0].aws_kms_alias.backup_kms_alias
}
moved {
  from = module.disaster_recovery_backup[0].aws_kms_key.db_backup
  to   = module.eu_west_1[0].module.disaster_recovery_backup[0].aws_kms_key.db_backup
}
moved {
  from = module.disaster_recovery_backup[0].module.dr_backup_security_group.aws_security_group.group
  to   = module.eu_west_1[0].module.disaster_recovery_backup[0].module.dr_backup_security_group.aws_security_group.group
}
moved {
  from = module.disaster_recovery_backup[0].module.dr_backup_security_group.aws_security_group_rule.rules
  to   = module.eu_west_1[0].module.disaster_recovery_backup[0].module.dr_backup_security_group.aws_security_group_rule.rules
}
moved {
  from = module.api_aurora[0].aws_rds_cluster.cluster_serverless[0]
  to   = module.eu_west_1[0].module.api_aurora[0].aws_rds_cluster.cluster_serverless[0]
}
moved {
  from = module.api_aurora[0].aws_rds_cluster_instance.serverless_instances[0]
  to   = module.eu_west_1[0].module.api_aurora[0].aws_rds_cluster_instance.serverless_instances[0]
}
