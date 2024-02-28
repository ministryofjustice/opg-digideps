resource "aws_rds_cluster" "cluster" {
  count                               = var.aurora_serverless ? 0 : 1
  apply_immediately                   = var.apply_immediately
  availability_zones                  = var.availability_zones
  backup_retention_period             = var.backup_retention_period
  cluster_identifier                  = "${var.cluster_identifier}-${terraform.workspace}"
  database_name                       = var.database_name
  db_subnet_group_name                = var.db_subnet_group_name
  deletion_protection                 = var.deletion_protection
  engine                              = var.engine
  engine_version                      = var.engine_version
  enabled_cloudwatch_logs_exports     = ["postgresql"]
  final_snapshot_identifier           = "${var.database_name}-${terraform.workspace}-final-snapshot"
  kms_key_id                          = var.kms_key_id
  master_username                     = var.master_username
  master_password                     = var.master_password
  preferred_backup_window             = "23:00-23:30"
  preferred_maintenance_window        = "mon:00:00-mon:01:00"
  replication_source_identifier       = var.replication_source_identifier
  skip_final_snapshot                 = var.skip_final_snapshot
  storage_encrypted                   = var.storage_encrypted
  vpc_security_group_ids              = var.vpc_security_group_ids
  tags                                = var.tags
  iam_database_authentication_enabled = var.iam_database_authentication_enabled
  lifecycle {
    ignore_changes  = [replication_source_identifier]
    prevent_destroy = true
  }
  depends_on = [var.log_group]
}

resource "aws_rds_cluster_instance" "cluster_instances" {
  count                           = var.aurora_serverless ? 0 : var.instance_count
  auto_minor_version_upgrade      = var.auto_minor_version_upgrade
  db_subnet_group_name            = var.db_subnet_group_name
  depends_on                      = [aws_rds_cluster.cluster]
  cluster_identifier              = "${var.cluster_identifier}-${terraform.workspace}"
  engine                          = var.engine
  engine_version                  = var.engine_version
  identifier                      = "${var.cluster_identifier}-${terraform.workspace}-${count.index}"
  ca_cert_identifier              = var.ca_cert_identifier
  instance_class                  = var.instance_class
  monitoring_interval             = 30
  monitoring_role_arn             = "arn:aws:iam::${var.account_id}:role/rds-enhanced-monitoring"
  performance_insights_enabled    = true
  performance_insights_kms_key_id = var.kms_key_id
  publicly_accessible             = var.publicly_accessible
  tags                            = var.tags

  timeouts {
    create = var.timeout_create
    update = var.timeout_update
    delete = var.timeout_delete
  }

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_rds_cluster" "cluster_serverless" {
  count                               = var.aurora_serverless ? 1 : 0
  cluster_identifier                  = "${var.cluster_identifier}-${terraform.workspace}"
  apply_immediately                   = var.apply_immediately
  availability_zones                  = var.availability_zones
  backup_retention_period             = var.backup_retention_period
  copy_tags_to_snapshot               = true
  database_name                       = var.database_name
  db_subnet_group_name                = var.db_subnet_group_name
  deletion_protection                 = var.deletion_protection
  engine                              = var.engine
  engine_version                      = var.engine_version
  engine_mode                         = "provisioned"
  final_snapshot_identifier           = "${var.database_name}-${terraform.workspace}-final-snapshot"
  kms_key_id                          = var.kms_key_id
  master_username                     = var.master_username
  master_password                     = var.master_password
  preferred_backup_window             = "23:00-23:30"
  preferred_maintenance_window        = "mon:00:00-mon:01:00"
  storage_encrypted                   = var.storage_encrypted
  skip_final_snapshot                 = var.skip_final_snapshot
  vpc_security_group_ids              = var.vpc_security_group_ids
  tags                                = var.tags
  iam_database_authentication_enabled = var.iam_database_authentication_enabled

  serverlessv2_scaling_configuration {
    min_capacity = 0.5
    max_capacity = 4
  }
  depends_on = [var.log_group]
}

resource "aws_rds_cluster_instance" "serverless_instances" {
  count                           = var.aurora_serverless ? var.instance_count : 0
  apply_immediately               = var.apply_immediately
  auto_minor_version_upgrade      = var.auto_minor_version_upgrade
  db_subnet_group_name            = var.db_subnet_group_name
  depends_on                      = [aws_rds_cluster.cluster_serverless]
  cluster_identifier              = "${var.cluster_identifier}-${terraform.workspace}"
  engine                          = var.engine
  engine_version                  = var.engine_version
  identifier                      = "${var.cluster_identifier}-${terraform.workspace}-${count.index}"
  ca_cert_identifier              = var.ca_cert_identifier
  instance_class                  = "db.serverless"
  monitoring_interval             = 30
  monitoring_role_arn             = "arn:aws:iam::${var.account_id}:role/rds-enhanced-monitoring"
  performance_insights_enabled    = true
  performance_insights_kms_key_id = var.kms_key_id
  publicly_accessible             = var.publicly_accessible
  tags                            = var.tags

  timeouts {
    create = var.timeout_create
    update = var.timeout_update
    delete = var.timeout_delete
  }
}
