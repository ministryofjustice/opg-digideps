locals {
  docker_tag_elems = split("-", var.OPG_DOCKER_TAG)
  client_tag       = local.environment == "development" ? "development-${local.docker_tag_elems[1]}" : var.OPG_DOCKER_TAG

  images = {
    api         = "${data.aws_ecr_repository.images["api"].repository_url}:${var.OPG_DOCKER_TAG}"
    client      = "${data.aws_ecr_repository.images["client"].repository_url}:${local.client_tag}"
    sync        = "${data.aws_ecr_repository.images["sync"].repository_url}:${var.OPG_DOCKER_TAG}"
    wkhtmltopdf = "${data.aws_ecr_repository.images["wkhtmltopdf"].repository_url}:${var.OPG_DOCKER_TAG}"
    drbackup    = "${data.aws_ecr_repository.images["dr-backup"].repository_url}:${var.OPG_DOCKER_TAG}"
  }

  repositories = [
    "api",
    "client",
    "dr-backup",
    "sync",
    "wkhtmltopdf"
  ]
}

data "aws_ecr_repository" "images" {
  for_each = toset(local.repositories)

  name     = "digideps/${each.key}"
  provider = aws.management
}
