resource "aws_sns_topic" "oicd-test" {
  name = "oidc-test-${terraform.workspace}"
}
