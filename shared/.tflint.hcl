config {
  module = false
  deep_check = false
  force = false

  ignore_module = {
    "github.com/wata727/example-module" = true
  }
}

rule "aws_elasticache_cluster_default_parameter_group" {
  enabled = false
}

rule "aws_db_instance_default_parameter_group" {
  enabled = false
}
