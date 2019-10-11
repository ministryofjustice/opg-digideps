#TODO use foreach
locals {
  images = {
    api          = "${data.aws_ecr_repository.api.repository_url}:${var.OPG_DOCKER_TAG}"
    client       = "${data.aws_ecr_repository.client.repository_url}:${var.OPG_DOCKER_TAG}"
    file_scanner = "${data.aws_ecr_repository.file_scanner.repository_url}:latest"
    sync         = "${data.aws_ecr_repository.sync.repository_url}:${var.OPG_DOCKER_TAG}"
    wkhtmltopdf  = "${data.aws_ecr_repository.wkhtmltopdf.repository_url}:latest"
  }
}

data "aws_ecr_repository" "api" {
  name     = "digideps/api"
  provider = "aws.management"
}

data "aws_ecr_repository" "client" {
  name     = "digideps/client"
  provider = "aws.management"
}

data "aws_ecr_repository" "sync" {
  name     = "digideps/sync"
  provider = "aws.management"
}

data "aws_ecr_repository" "file_scanner" {
  name     = "digideps/file-scanner"
  provider = "aws.management"
}

data "aws_ecr_repository" "wkhtmltopdf" {
  name     = "digideps/wkhtmltopdf"
  provider = "aws.management"
}
