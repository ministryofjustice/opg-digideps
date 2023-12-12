terraform {
  required_version = "1.6.5"
  required_providers {
    archive = {
      source = "hashicorp/archive"
    }
    aws = {
      source  = "hashicorp/aws"
      version = "5.30.0"
    }
    local = {
      source = "hashicorp/local"
    }
  }
}
