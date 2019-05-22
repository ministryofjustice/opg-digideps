data "template_file" "maintenance_user_data" {
  template = "${file("${path.module}/user_data.tpl")}"

  vars {
    IS_SALTMASTER     = "no"
    HAS_DATA_STORAGE  = "no"
    OPG_ROLE          = "maintenance"
    OPG_STACKNAME     = "${local.vpc_name}"
    OPG_PROJECT       = "${local.project}"
    OPG_ACCOUNT_ID    = "${local.account_id}"
    OPG_ENVIRONMENT   = "production"
    OPG_SHARED_SUFFIX = "${local.vpc_name}"
    OPG_DOMAIN        = "${local.account_name}.${local.domain_name}"
    OPG_VPCNAME       = "${local.vpc_name}"
  }
}

resource "aws_instance" "maintenance" {
  count                = "${local.maintenance_enabled}"
  ami                  = "${data.aws_ami.opg_ubuntu_14_04.id}"
  instance_type        = "t2.micro"
  iam_instance_profile = "maintenance.${local.vpc_name}"
  subnet_id            = "${element(data.aws_subnet.private.*.id, 0)}"
  user_data            = "${data.template_file.maintenance_user_data.rendered}"
  monitoring           = true

  vpc_security_group_ids = [
    "${data.aws_security_group.shared_services.id}",
    "${data.aws_security_group.jumphost_client.id}",
    "${data.aws_security_group.maintenance.id}",
  ]

  tags = "${merge(
      local.default_tags,
      map("Name", "maintenance.${local.vpc_name}")
    )}"
}
