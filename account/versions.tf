terraform {
  required_version = "1.5.7"
  required_providers {
    archive = {
      source = "hashicorp/archive"
    }
    aws = {
      source  = "hashicorp/aws"
      version = "5.14.0"
    }
    local = {
      source = "hashicorp/local"
    }
  }
}
