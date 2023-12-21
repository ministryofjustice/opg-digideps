terraform {
  backend "s3" {
    bucket         = "s3-access-logs.jstestsp"
    key            = "opg-digi-deps-infra/terraform.tfstate"
    encrypt        = true
    region         = "eu-west-1"
    role_arn       = "arn:aws:iam::248804316466:role/tf-basic-user-ddls1021494"
    dynamodb_table = "remote_lock"
  }
}

provider "aws" {
  region = "eu-west-1"
  assume_role {
    role_arn     = "arn:aws:iam::248804316466:role/tf-basic-user-ddls1021494"
    session_name = "terraform-session"
  }
}
