resource "aws_kms_key" "s3" {
  description             = "Digideps S3 bucket encryption key"
  deletion_window_in_days = 10
  tags                    = local.default_tags
  enable_key_rotation     = true
}

resource "aws_kms_alias" "s3" {
  name          = "alias/s3-digideps-${terraform.workspace}"
  target_key_id = aws_kms_key.s3.key_id
}
