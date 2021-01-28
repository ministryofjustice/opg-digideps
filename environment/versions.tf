terraform {
  required_version = ">= 0.13"
  required_providers {
    archive = {
      source = "hashicorp/archive"
    }
    aws = {
      source  = "hashicorp/aws"
      version = "3.25.0"

    }
    local = {
      source = "hashicorp/local"
    }
  }
}
