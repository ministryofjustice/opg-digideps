terraform {
  backend "s3" {
    bucket         = "opg.terraform.state"
    key            = "opg-digi-deps-infrastructure/terraform.tfstate"
    encrypt        = true
    region         = "eu-west-1"
    role_arn       = "arn:aws:iam::311462405659:role/digideps-ci"
    dynamodb_table = "remote_lock"
  }
}

provider "aws" {
  region = "eu-west-1"

  assume_role {
    role_arn     = "arn:aws:iam::${local.account["account_id"]}:role/${var.default_role}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  region = "eu-west-1"
  alias  = "management"

  assume_role {
    role_arn     = "arn:aws:iam::311462405659:role/${var.default_role}"
    session_name = "terraform-session"
  }
}

# DD has it's public DNS in production, not management
provider "aws" {
  region = "eu-west-1"
  alias  = "dns"

  assume_role {
    role_arn     = "arn:aws:iam::515688267891:role/${var.default_role}"
    session_name = "terraform-session"
  }
}
