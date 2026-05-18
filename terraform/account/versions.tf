terraform {
  required_version = "1.15.0"
  required_providers {
    archive = {
      source = "hashicorp/archive"
    }
    aws = {
      source  = "hashicorp/aws"
      version = ">= 6.0.0"
    }
    local = {
      source = "hashicorp/local"
    }
  }
}
