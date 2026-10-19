output "master_username" {
  description = "The master username for the Aurora cluster."
  value       = aws_rds_cluster.cluster_serverless.master_username
}

output "port" {
  description = "The port number used to connect to the Aurora cluster."
  value       = aws_rds_cluster.cluster_serverless.port
}

output "endpoint" {
  description = "The endpoint for connecting to the Aurora cluster."
  value       = aws_rds_cluster.cluster_serverless.endpoint
}

output "reader_endpoint" {
  description = "The reader endpoint for the Aurora cluster, if applicable."
  value       = aws_rds_cluster.cluster_serverless.reader_endpoint
}

output "name" {
  description = "The name of the database within the Aurora cluster."
  value       = aws_rds_cluster.cluster_serverless.database_name
}

output "cluster" {
  description = "Information about the Aurora cluster."
  value       = aws_rds_cluster.cluster_serverless
}

output "cluster_arn" {
  description = "ARN for the Aurora cluster."
  value       = aws_rds_cluster.cluster_serverless.arn
}

output "cluster_resource_id" {
  description = "Resource ID for the Aurora cluster."
  value       = aws_rds_cluster.cluster_serverless.cluster_resource_id
}

output "instance_identifiers" {
  value = {
    for i in range(var.instance_count) :
    tostring(i) => aws_rds_cluster_instance.serverless_instances[i].id
  }
}
