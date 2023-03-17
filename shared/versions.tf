terraform {
  required_version = ">= 1.2.1"
  required_providers {
    archive = {
      source = "hashicorp/archive"
    }
    aws = {
      source  = "hashicorp/aws"
      version = "4.59.0"
    }
    local = {
      source = "hashicorp/local"
    }
  }
}
