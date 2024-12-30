terraform {
  backend "s3" {
    bucket  = "opg.terraform.state"
    key     = "opg-digideps-environment/terraform.tfstate"
    encrypt = true
    region  = "eu-west-1"
    assume_role = {
      role_arn = "arn:aws:iam::311462405659:role/digideps-ci"
    }
    dynamodb_table = "remote_lock"
  }
}

provider "aws" {
  region = "eu-west-1"
  alias  = "digideps_eu_west_1"
  default_tags {
    tags = local.default_tags
  }
  assume_role {
    role_arn     = "arn:aws:iam::${var.accounts[terraform.workspace].account_id}:role/${var.DEFAULT_ROLE}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  region = "eu-west-1"
  alias  = "management_eu_west_1"
  default_tags {
    tags = local.default_tags
  }
  assume_role {
    role_arn     = "arn:aws:iam::311462405659:role/${var.DEFAULT_ROLE_MGMT}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  region = "eu-west-2"
  alias  = "digideps_eu_west_2"
  default_tags {
    tags = local.default_tags
  }
  assume_role {
    role_arn     = "arn:aws:iam::${var.accounts[terraform.workspace].account_id}:role/${var.DEFAULT_ROLE}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  region = "eu-west-2"
  alias  = "management_eu_west_2"
  default_tags {
    tags = local.default_tags
  }
  assume_role {
    role_arn     = "arn:aws:iam::311462405659:role/${var.DEFAULT_ROLE_MGMT}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  region = "us-east-1"
  alias  = "global"
  default_tags {
    tags = local.default_tags
  }
  assume_role {
    role_arn     = "arn:aws:iam::${local.account["account_id"]}:role/${var.DEFAULT_ROLE}"
    session_name = "terraform-session"
  }
}
