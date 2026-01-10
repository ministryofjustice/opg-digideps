terraform {
  required_version = "1.14.3"
  required_providers {
    archive = {
      source  = "hashicorp/archive"
      version = ">= 2.4"
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
