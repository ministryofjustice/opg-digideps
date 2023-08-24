output "master_username" {
  value = var.aurora_serverless ? aws_rds_cluster.cluster_serverless[0].master_username : aws_rds_cluster.cluster[0].master_username
}

output "port" {
  value = var.aurora_serverless ? aws_rds_cluster.cluster_serverless[0].port : aws_rds_cluster.cluster[0].port
}

output "endpoint" {
  value = var.aurora_serverless ? aws_rds_cluster.cluster_serverless[0].endpoint : aws_rds_cluster.cluster[0].endpoint
}

output "reader_endpoint" {
  value = var.aurora_serverless ? aws_rds_cluster.cluster_serverless[0].reader_endpoint : aws_rds_cluster.cluster[0].reader_endpoint
}

output "name" {
  value = var.aurora_serverless ? aws_rds_cluster.cluster_serverless[0].database_name : aws_rds_cluster.cluster[0].database_name
}

output "cluster" {
  value = var.aurora_serverless ? aws_rds_cluster.cluster_serverless[0] : aws_rds_cluster.cluster[0]
}
