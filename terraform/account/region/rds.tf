resource "aws_iam_role" "enhanced_monitoring" {
  name               = "rds-enhanced-monitoring"
  assume_role_policy = data.aws_iam_policy_document.enhanced_monitoring.json
  tags = merge(
    var.default_tags,
    { Name = "rds-enhanced-monitoring-role-${var.account.name}" },
  )
}

resource "aws_iam_role_policy_attachment" "enhanced_monitoring" {
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonRDSEnhancedMonitoringRole"
  role       = aws_iam_role.enhanced_monitoring.name
}

data "aws_iam_policy_document" "enhanced_monitoring" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["monitoring.rds.amazonaws.com"]
      type        = "Service"
    }
  }
}
