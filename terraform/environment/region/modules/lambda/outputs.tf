output "lambda" {
  description = "Lambda function"
  value       = aws_lambda_function.lambda_function
}

output "lambda_sg" {
  description = "Security group of the lambda"
  value       = aws_security_group.lambda
}

output "lambda_role" {
  description = "Role for the lambda"
  value       = aws_iam_role.lambda_role
}
