output "master_username" {
  description = "The master username for the Aurora cluster."
  value       = var.aurora_serverless ? aws_rds_cluster.cluster_serverless[0].master_username : aws_rds_cluster.cluster[0].master_username
}

output "port" {
  description = "The port number used to connect to the Aurora cluster."
  value       = var.aurora_serverless ? aws_rds_cluster.cluster_serverless[0].port : aws_rds_cluster.cluster[0].port
}

output "endpoint" {
  description = "The endpoint for connecting to the Aurora cluster."
  value       = var.aurora_serverless ? aws_rds_cluster.cluster_serverless[0].endpoint : aws_rds_cluster.cluster[0].endpoint
}

output "reader_endpoint" {
  description = "The reader endpoint for the Aurora cluster, if applicable."
  value       = var.aurora_serverless ? aws_rds_cluster.cluster_serverless[0].reader_endpoint : aws_rds_cluster.cluster[0].reader_endpoint
}

output "name" {
  description = "The name of the database within the Aurora cluster."
  value       = var.aurora_serverless ? aws_rds_cluster.cluster_serverless[0].database_name : aws_rds_cluster.cluster[0].database_name
}

output "cluster" {
  description = "Information about the Aurora cluster."
  value       = var.aurora_serverless ? aws_rds_cluster.cluster_serverless[0] : aws_rds_cluster.cluster[0]
}

output "cluster_arn" {
  description = "ARN for the Aurora cluster."
  value       = var.aurora_serverless ? aws_rds_cluster.cluster_serverless[0].arn : aws_rds_cluster.cluster[0].arn
}

output "cluster_resource_id" {
  description = "Resource ID for the Aurora cluster."
  value       = var.aurora_serverless ? aws_rds_cluster.cluster_serverless[0].cluster_resource_id : aws_rds_cluster.cluster[0].cluster_resource_id
}
