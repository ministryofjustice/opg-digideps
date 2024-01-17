terraform {
  backend "s3" {
    bucket         = "opg.terraform.state"
    key            = "digideps-infrastructure-shared/terraform.tfstate"
    encrypt        = true
    region         = "eu-west-1"
    role_arn       = "arn:aws:iam::311462405659:role/digideps-ci"
    dynamodb_table = "remote_lock"
  }
}

# Old providers. To remove once this goes through all the envs.
provider "aws" {
  region = "eu-west-1"
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
  alias  = "management"
  default_tags {
    tags = local.default_tags
  }
  assume_role {
    role_arn     = "arn:aws:iam::311462405659:role/${var.DEFAULT_ROLE}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  region = "us-east-1"
  alias  = "us-east-1"
  default_tags {
    tags = local.default_tags
  }
  assume_role {
    role_arn     = "arn:aws:iam::${local.account["account_id"]}:role/${var.DEFAULT_ROLE}"
    session_name = "terraform-session"
  }
}

# New providers

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
    role_arn     = "arn:aws:iam::311462405659:role/${var.DEFAULT_ROLE}"
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
    role_arn     = "arn:aws:iam::311462405659:role/${var.DEFAULT_ROLE}"
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
