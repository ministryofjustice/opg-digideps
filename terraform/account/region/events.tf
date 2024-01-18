# To be removed as moved to environment
resource "aws_iam_role" "events_task_runner" {
  name = "events_task_runner"

  assume_role_policy = <<DOC
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "",
      "Effect": "Allow",
      "Principal": {
        "Service": "events.amazonaws.com"
      },
      "Action": "sts:AssumeRole"
    }
  ]
}
DOC
}

resource "aws_iam_role_policy" "events_task_runner" {
  name = "ecs_events_run_task_with_any_role"
  role = aws_iam_role.events_task_runner.id

  policy = <<DOC
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": "iam:PassRole",
            "Resource": "*"
        },
        {
            "Effect": "Allow",
            "Action": "ecs:RunTask",
            "Resource": "*"
        }
    ]
}
DOC
}

resource "aws_cloudwatch_event_rule" "nightly" {
  name                = "nightly"
  description         = "Nightly scheduled tasks"
  schedule_expression = "cron(0 3 * * ? *)"
  tags                = var.default_tags
}
