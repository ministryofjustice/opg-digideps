output "lambda" {
  description = "The lambda function"
  value       = aws_lambda_function.lambda_function
}

output "lambda_sg" {
  description = "The SG of the lambda"
  value       = aws_security_group.lambda
}
