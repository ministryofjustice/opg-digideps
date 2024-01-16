locals {
  default_role = var.DEFAULT_ROLE
}

terraform {
  backend "s3" {
    bucket         = "s3-access-logs.jstestsp"
    key            = "opg-digi-deps-infra/terraform.tfstate"
    encrypt        = true
    region         = "eu-west-1"
    profile        = "digideps"
    dynamodb_table = "remote_lock"
  }
}

provider "aws" {
  region  = "eu-west-1"
  profile = "digideps"
}

provider "aws" {
  region  = "eu-west-1"
  alias   = "sandbox"
  profile = "sandbox"
}
