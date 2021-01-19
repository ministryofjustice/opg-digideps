//resource "aws_cloudwatch_log_group" "route53_public" {
//  provider = aws.dns-us
//
//  name              = "/aws/route53/${data.aws_route53_zone.public.name}"
//  retention_in_days = 30
//}
//
//# CloudWatch log resource policy to allow Route53 to write logs
//# to any log group under /aws/route53/*
//
//data "aws_iam_policy_document" "route53_query_logging" {
//  provider = aws.dns-us
//  statement {
//    actions = [
//      "logs:CreateLogStream",
//      "logs:PutLogEvents",
//    ]
//
//    resources = ["arn:aws:logs:*:*:log-group:/aws/route53/*"]
//
//    principals {
//      identifiers = ["route53.amazonaws.com"]
//      type        = "Service"
//    }
//  }
//}
//
//resource "aws_cloudwatch_log_resource_policy" "route53_query_logging" {
//  provider = aws.dns-us
//
//  policy_document = data.aws_iam_policy_document.route53_query_logging.json
//  policy_name     = "route53-query-logging"
//}
//
//resource "aws_route53_query_log" "route53_public" {
//  provider = aws.dns-us
//  depends_on = [aws_cloudwatch_log_resource_policy.route53_query_logging]
//  cloudwatch_log_group_arn = aws_cloudwatch_log_group.route53_public.arn
//  zone_id                  = data.aws_route53_zone.public.zone_id
//}
