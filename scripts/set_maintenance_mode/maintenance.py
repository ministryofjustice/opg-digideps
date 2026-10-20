#!/usr/bin/env python3

import argparse
import boto3


def assume_breakglass(environment):
    environments = {
        "development": "248804316466",
        "staging": "248804316466",
        "training": "454262938596",
        "preproduction": "454262938596",
        "production": "515688267891",
    }

    account = environments.get(environment, environments["development"])

    sts_client = boto3.client("sts")
    assumed_role = sts_client.assume_role(
        RoleArn=f"arn:aws:iam::{account}:role/breakglass",
        RoleSessionName="MaintenanceMode",
    )

    credentials = assumed_role["Credentials"]
    session = boto3.Session(
        aws_access_key_id=credentials["AccessKeyId"],
        aws_secret_access_key=credentials["SecretAccessKey"],
        aws_session_token=credentials["SessionToken"],
    )
    return session


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("service", choices=["front", "admin"])
    parser.add_argument("environment")
    parser.add_argument("mode", choices=["on", "off"])
    args = parser.parse_args()

    session = assume_breakglass(args.environment)
    client = session.client("elbv2")

    # Find the load balancer by its predictable name.
    lb_name = f"{args.service}-{args.environment}"

    load_balancer = client.describe_load_balancers(Names=[lb_name])["LoadBalancers"][0]

    # Find the HTTPS listener.
    listeners = client.describe_listeners(
        LoadBalancerArn=load_balancer["LoadBalancerArn"]
    )["Listeners"]

    listener = next(listener for listener in listeners if listener["Port"] == 443)

    # Find the maintenance rule by its known priority.
    rules = client.describe_rules(ListenerArn=listener["ListenerArn"])["Rules"]

    rule = next(rule for rule in rules if rule["Priority"] == "101")

    # Match everything when enabled; effectively nothing when disabled.
    path = "/*" if args.mode == "on" else "/maintenance"

    client.modify_rule(
        RuleArn=rule["RuleArn"],
        Conditions=[
            {
                "Field": "path-pattern",
                "PathPatternConfig": {
                    "Values": [path],
                },
            }
        ],
    )

    print(f"Maintenance mode {args.mode}: " f"{lb_name} ({path})")


if __name__ == "__main__":
    main()
