locals {
  front_sg_rules = {
    ecr  = local.common_sg_rules.ecr
    logs = local.common_sg_rules.logs
    s3   = local.common_sg_rules.s3
    cache = {
      port        = 6379
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.front_cache_security_group.id
    }
    api = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_service_security_group.id
    }
    pdf = {
      port        = 80
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.wkhtmltopdf_security_group.id
    }
    scan = {
      port        = 8080
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.scan_security_group.id
    }
    front_elb = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.front_elb_security_group.id
    }
    ses = {
      port        = 587
      type        = "egress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
    route53_healthcheck_us_west_1_a = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "54.183.255.128/26"
    }
    route53_healthcheck_us_west_1_b = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "54.241.32.64/26"
    }
    route53_healthcheck_us_west_2_a = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "54.245.168.0/26"
    }
    route53_healthcheck_us_west_2_b = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "54.244.52.192/26"
    }
    route53_healthcheck_us_east_1_a = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "54.243.31.192/26"
    }
    route53_healthcheck_us_east_1_b = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "107.23.255.0/26"
    }
    route53_healthcheck_sa_east_1_a = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "177.71.207.128/26"
    }
    route53_healthcheck_sa_east_1_b = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "54.232.40.64/26"
    }
    route53_healthcheck_ap_southeast_1_a = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "54.255.254.192/26"
    }
    route53_healthcheck_ap_southeast_1_b = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "54.251.31.128/26"
    }
    route53_healthcheck_eu_west_1_a = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "176.34.159.192/26"
    }
    route53_healthcheck_eu_west_1_b = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "54.228.16.0/26"
    }
    route53_healthcheck_ap_northeast_1_a = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "54.250.253.192/26"
    }
    route53_healthcheck_ap_southeast_2_a = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "54.252.254.192/26"
    }
    route53_healthcheck_ap_southeast_2_b = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "54.252.79.128/26"
    }
    //    {
    //    "ip_prefix": "54.245.168.0/26",
    //    "region": "us-west-2",
    //    },
    //    {
    //    "ip_prefix": "54.243.31.192/26",
    //    "region": "us-east-1",
    //    },
    //    {
    //    "ip_prefix": "177.71.207.128/26",
    //    "region": "sa-east-1",
    //    },
    //    {
    //    "ip_prefix": "54.255.254.192/26",
    //    "region": "ap-southeast-1",
    //    },
    //    {
    //    "ip_prefix": "54.244.52.192/26",
    //    "region": "us-west-2",
    //    },
    //    {
    //    "ip_prefix": "176.34.159.192/26",
    //    "region": "eu-west-1",
    //    },
    //    {
    //    "ip_prefix": "54.251.31.128/26",
    //    "region": "ap-southeast-1",
    //    },
    //    {
    //    "ip_prefix": "54.183.255.128/26",
    //    "region": "us-west-1",
    //    },
    //    {
    //    "ip_prefix": "54.241.32.64/26",
    //    "region": "us-west-1",
    //    },
    //    {
    //    "ip_prefix": "54.252.254.192/26",
    //    "region": "ap-southeast-2",
    //    },
    //    {
    //    "ip_prefix": "107.23.255.0/26",
    //    "region": "us-east-1",
    //    },
    //    {
    //    "ip_prefix": "54.228.16.0/26",
    //    "region": "eu-west-1",
    //    },
    //    {
    //    "ip_prefix": "54.250.253.192/26",
    //    "region": "ap-northeast-1",
    //    },
    //    {
    //    "ip_prefix": "54.232.40.64/26",
    //    "region": "sa-east-1",
    //    },
    //    {
    //    "ip_prefix": "54.252.79.128/26",
    //    "region": "ap-southeast-2",
    //    },
  }
}

module "front_service_security_group" {
  source = "./security_group"
  rules  = local.front_sg_rules
  name   = "front-service"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}

locals {
  front_cache_sg_rules = {
    front_service = {
      port        = 6379
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.front_service_security_group.id
    }
  }
}

module "front_cache_security_group" {
  source = "./security_group"
  rules  = local.front_cache_sg_rules
  name   = "front-cache"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}

locals {
  front_elb_sg_rules = {
    front_service = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.front_service_security_group.id
    }
  }
}

module "front_elb_security_group" {
  source = "./security_group"
  rules  = local.front_elb_sg_rules
  name   = "front-alb"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}

# Using resources rather than a module here due to a large list of IPs

resource "aws_security_group_rule" "front_elb_http_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 80
  to_port           = 80
  security_group_id = module.front_elb_security_group.id
  cidr_blocks       = local.front_whitelist
}

resource "aws_security_group_rule" "front_elb_https_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 443
  to_port           = 443
  security_group_id = module.front_elb_security_group.id
  cidr_blocks       = local.front_whitelist
}
