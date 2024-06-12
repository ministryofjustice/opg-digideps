locals {
  client_web_tag = local.environment == "development" ? "development-${var.docker_tag}" : var.docker_tag

  images = {
    api              = "${data.aws_ecr_repository.images["api"].repository_url}:${var.docker_tag}"
    api-webserver    = "${data.aws_ecr_repository.images["api-webserver"].repository_url}:${var.docker_tag}"
    client           = "${data.aws_ecr_repository.images["client"].repository_url}:${var.docker_tag}"
    client-webserver = "${data.aws_ecr_repository.images["client-webserver"].repository_url}:${local.client_web_tag}"
    orchestration    = "${data.aws_ecr_repository.images["sync"].repository_url}:${var.docker_tag}"
    htmltopdf        = "${data.aws_ecr_repository.images["htmltopdf"].repository_url}:${var.docker_tag}"
    dr-backup        = "${data.aws_ecr_repository.images["dr-backup"].repository_url}:${var.docker_tag}"
    synchronise      = "${data.aws_ecr_repository.images["synchronise-lambda"].repository_url}:${var.docker_tag}"
    file-scanner     = "${data.aws_ecr_repository.images["file-scanner"].repository_url}:${var.docker_tag}"
    squid-proxy      = "${data.aws_ecr_repository.images["test"].repository_url}:${var.docker_tag}"
  }

  repositories = [
    "api",
    "api-webserver",
    "client",
    "client-webserver",
    "dr-backup",
    "sync",
    "htmltopdf",
    "synchronise-lambda",
    "file-scanner",
    "test"
  ]
}

data "aws_ecr_repository" "images" {
  for_each = toset(local.repositories)

  name     = "digideps/${each.key}"
  provider = aws.management
}
