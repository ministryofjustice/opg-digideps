terraform {
  backend "s3" {
    bucket  = "opg.terraform.state"
    key     = "opg-digideps-environment/terraform.tfstate"
    encrypt = true
    region  = "eu-west-1"
    assume_role = {
      role_arn = "arn:aws:iam::311462405659:role/digideps-state-write"
    }
    dynamodb_table = "remote_lock"
  }
}

provider "aws" {
  region = "eu-west-1"
  default_tags {
    tags = local.default_tags
  }
  assume_role {
    role_arn     = "arn:aws:iam::${local.account["account_id"]}:role/${var.DEFAULT_ROLE}"
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
    role_arn     = "arn:aws:iam::311462405659:role/${var.DEFAULT_ROLE_MGMT}"
    session_name = "terraform-session"
  }
}

# New config
provider "aws" {
  region = "eu-west-1"
  alias  = "digideps_eu_west_1"
  default_tags {
    tags = local.default_tags
  }
  assume_role {
    role_arn     = "arn:aws:iam::${local.account["account_id"]}:role/${var.DEFAULT_ROLE}"
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
    role_arn     = "arn:aws:iam::${local.account["account_id"]}:role/${var.DEFAULT_ROLE}"
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

# DD has it's public DNS in production, not management
provider "aws" {
  region = "eu-west-1"
  alias  = "dns"
  default_tags {
    tags = local.default_tags
  }
  assume_role {
    role_arn     = "arn:aws:iam::515688267891:role/${var.DEFAULT_ROLE}"
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
