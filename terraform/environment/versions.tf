terraform {
  required_version = "1.6.6"
  required_providers {
    archive = {
      source = "hashicorp/archive"
    }
    aws = {
      source  = "hashicorp/aws"
      version = "5.31.0"
    }
    local = {
      source = "hashicorp/local"
    }
  }
}
