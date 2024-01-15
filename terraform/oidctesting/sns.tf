resource "aws_sns_topic" "oicd-test" {
  name = "oidc-test-${terraform.workspace}"
}

resource "aws_sns_topic" "oicd-test-sb" {
  provider = aws.sandbox
  name     = "oidc-test-sb-${terraform.workspace}"
}
