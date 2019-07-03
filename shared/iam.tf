resource "aws_iam_account_password_policy" "strict" {
  minimum_password_length = 30
}
