terraform {
  required_providers {
    archive = {
      source = "hashicorp/archive"
    }
    aws = {
      source  = "hashicorp/aws"
      version = "2.70.0"
    }
  }
  required_version = ">= 0.13"
}
