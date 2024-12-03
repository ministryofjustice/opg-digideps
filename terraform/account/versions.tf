terraform {
  required_version = "1.9.8"
  required_providers {
    archive = {
      source = "hashicorp/archive"
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
