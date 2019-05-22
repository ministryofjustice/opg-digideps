data "template_file" "master_user_data" {
  template = "${file("${path.module}/user_data.tpl")}"

  vars {
    IS_SALTMASTER     = "yes"
    HAS_DATA_STORAGE  = "no"
    OPG_ROLE          = "master"
    OPG_STACKNAME     = "${local.vpc_name}"
    OPG_PROJECT       = "${local.project}"
    OPG_ACCOUNT_ID    = "${local.account_id}"
    OPG_ENVIRONMENT   = "${local.account_long_name}"
    OPG_SHARED_SUFFIX = "${local.vpc_name}"
    OPG_DOMAIN        = "${local.account_name}.${local.domain_name}"
    OPG_VPCNAME       = "${local.vpc_name}"
  }
}

resource "aws_instance" "master" {
  count                = "${local.vpc_enabled}"
  ami                  = "${data.aws_ami.opg_ubuntu_14_04.id}"
  instance_type        = "m4.large"
  iam_instance_profile = "master-role-${local.vpc_name}"
  subnet_id            = "${element(data.aws_subnet.private.*.id, 2)}"
  user_data            = "${data.template_file.master_user_data.rendered}"
  monitoring           = true

  vpc_security_group_ids = [
    "${data.aws_security_group.salt_master.id}",
    "${data.aws_security_group.shared_services.id}",
    "${data.aws_security_group.jumphost_client.id}",
  ]

  tags = "${merge(
      local.default_tags,
      map("Name", "master.${local.vpc_name}")
    )}"
}
