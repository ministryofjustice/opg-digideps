removed {
  from = aws_cloudwatch_log_anomaly_detector.vpc_flow_logs
  lifecycle {
    destroy = false
  }
}

removed {
  from = aws_cloudwatch_log_group.vpc_flow_logs
  lifecycle {
    destroy = false
  }
}

removed {
  from = aws_cloudwatch_log_group.vpc_flow_logs_default
  lifecycle {
    destroy = false
  }
}

removed {
  from = aws_flow_log.vpc_flow_logs
  lifecycle {
    destroy = false
  }
}

removed {
  from = aws_flow_log.vpc_flow_logs_default
  lifecycle {
    destroy = false
  }
}

removed {
  from = aws_iam_role.vpc_flow_logs
  lifecycle {
    destroy = false
  }
}

removed {
  from = aws_iam_role_policy.vpc_flow_logs
  lifecycle {
    destroy = false
  }
}

removed {
  from = aws_vpc.main

  lifecycle {
    destroy = false
  }
}
