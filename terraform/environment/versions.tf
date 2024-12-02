terraform {
  required_version = "1.9.8"
  required_providers {
    archive = {
      source  = "hashicorp/archive"
      version = ">= 2.4"
    }
    aws = {
      source  = "hashicorp/aws"
      version = "5.77.0"
    }
    local = {
      source = "hashicorp/local"
    }
  }
}
