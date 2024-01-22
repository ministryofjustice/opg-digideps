locals {
  cloud9_users_from_secret = jsondecode(data.aws_secretsmanager_secret_version.cloud9_users.secret_string)["user_names"]
  cloud9_owner_from_secret = jsondecode(data.aws_secretsmanager_secret_version.cloud9_users.secret_string)["owner"]
  cloud9_users_for_each = {
    for user, user in local.cloud9_users_from_secret : user => user
  }
}

resource "aws_cloud9_environment_ec2" "shared" {
  instance_type               = "t2.micro"
  name                        = "team-cloud9-env"
  automatic_stop_time_minutes = 20
  image_id                    = "amazonlinux-2-x86_64"
  description                 = "Shared Cloud9 instance to be used by all devs"
  subnet_id                   = aws_subnet.public[0].id
  owner_arn                   = "arn:aws:iam::${var.account.account_id}:assumed-role/${nonsensitive(local.cloud9_owner_from_secret)}/${nonsensitive(local.cloud9_owner_from_secret)}"
  tags                        = var.default_tags
}

resource "aws_cloud9_environment_membership" "shared" {
  for_each = nonsensitive(local.cloud9_users_for_each)

  environment_id = aws_cloud9_environment_ec2.shared.id
  permissions    = "read-write"
  user_arn       = "arn:aws:iam::${var.account.account_id}:assumed-role/operator/${each.value}"
}
