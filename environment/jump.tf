data "template_file" "jump_user_data" {
  template = "${file("${path.module}/user_data.tpl")}"

  vars {
    IS_SALTMASTER     = "no"
    HAS_DATA_STORAGE  = "no"
    OPG_ROLE          = "jump"
    OPG_STACKNAME     = "${local.vpc_name}"
    OPG_PROJECT       = "${local.project}"
    OPG_ACCOUNT_ID    = "${local.account_id}"
    OPG_ENVIRONMENT   = "${local.account_long_name}"
    OPG_SHARED_SUFFIX = "${local.vpc_name}"
    OPG_DOMAIN        = "${local.account_name}.${local.domain_name}"
    OPG_VPCNAME       = "${local.vpc_name}"
  }
}

resource "aws_instance" "jump" {
  count                       = "${local.vpc_enabled}"
  ami                         = "${data.aws_ami.opg_ubuntu_14_04.id}"
  instance_type               = "t2.nano"
  iam_instance_profile        = "jumphost-role-${local.vpc_name}"
  subnet_id                   = "${element(data.aws_subnet.public.*.id, 2)}"
  user_data                   = "${data.template_file.jump_user_data.rendered}"
  monitoring                  = true
  associate_public_ip_address = true

  vpc_security_group_ids = [
    "${data.aws_security_group.shared_services.id}",
    "${data.aws_security_group.jumphost.id}",
  ]

  tags = "${merge(
      local.default_tags,
      map("Name", "jump.${local.vpc_name}")
    )}"
}
