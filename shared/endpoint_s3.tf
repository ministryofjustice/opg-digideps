resource "aws_vpc_endpoint" "s3" {
  service_name      = "com.amazonaws.eu-west-1.s3"
  vpc_id            = aws_vpc.main.id
  vpc_endpoint_type = "Gateway"
  route_table_ids   = aws_route_table.private[*].id
  tags              = merge(local.default_tags, { Name = "s3" })
}
