terraform {
  required_version = "1.7.3"
  required_providers {
    archive = {
      source  = "hashicorp/archive"
      version = ">= 2.4"
    }
    aws = {
      source  = "hashicorp/aws"
      version = "5.37.0"
    }
    local = {
      source = "hashicorp/local"
    }
  }
}
