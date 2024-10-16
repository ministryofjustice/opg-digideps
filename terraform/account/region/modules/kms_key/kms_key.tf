resource "aws_kms_key" "main" {
  description             = "${data.aws_default_tags.current.tags.application} ${var.encrypted_resource} encryption key"
  deletion_window_in_days = var.deletion_window_in_days
  enable_key_rotation     = var.enable_key_rotation
  policy                  = var.kms_key_policy
  multi_region            = var.enable_multi_region
  provider                = aws.eu_west_1
}

resource "aws_kms_replica_key" "main" {
  description             = "${data.aws_default_tags.current.tags.application} ${var.encrypted_resource} multi-region replica key"
  deletion_window_in_days = var.deletion_window_in_days
  primary_key_arn         = aws_kms_key.main.arn
  policy                  = var.kms_key_policy
  provider                = aws.eu_west_2
}

resource "aws_kms_alias" "main_eu_west_1" {
  name          = "alias/${var.kms_key_alias_name}"
  target_key_id = aws_kms_key.main.key_id
  provider      = aws.eu_west_1
}

resource "aws_kms_alias" "main_eu_west_2" {
  name          = "alias/${var.kms_key_alias_name}"
  target_key_id = aws_kms_replica_key.main.key_id
  provider      = aws.eu_west_2
}
