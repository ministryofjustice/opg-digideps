terraform {
  required_version = "1.10.3"
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
