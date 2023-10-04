locals {
  docker_tag_elems = split("-", var.OPG_DOCKER_TAG)
  client_web_tag   = local.environment == "development" ? "development-${local.docker_tag_elems[1]}" : var.OPG_DOCKER_TAG

  images = {
    api              = "${data.aws_ecr_repository.images["api"].repository_url}:${var.OPG_DOCKER_TAG}"
    client           = "${data.aws_ecr_repository.images["client"].repository_url}:${var.OPG_DOCKER_TAG}"
    client-webserver = "${data.aws_ecr_repository.images["client-webserver"].repository_url}:${local.client_web_tag}"
    sync             = "${data.aws_ecr_repository.images["sync"].repository_url}:${var.OPG_DOCKER_TAG}"
    htmltopdf        = "${data.aws_ecr_repository.images["htmltopdf"].repository_url}:${var.OPG_DOCKER_TAG}"
    drbackup         = "${data.aws_ecr_repository.images["dr-backup"].repository_url}:${var.OPG_DOCKER_TAG}"
    synchronise      = "${data.aws_ecr_repository.images["synchronise-lambda"].repository_url}:${var.OPG_DOCKER_TAG}"
    file-scanner     = "${data.aws_ecr_repository.images["file-scanner"].repository_url}:${var.OPG_DOCKER_TAG}"
  }

  repositories = [
    "api",
    "client",
    "client-webserver",
    "dr-backup",
    "sync",
    "htmltopdf",
    "synchronise-lambda",
    "file-scanner"
  ]
}

data "aws_ecr_repository" "images" {
  for_each = toset(local.repositories)

  name     = "digideps/${each.key}"
  provider = aws.management
}
