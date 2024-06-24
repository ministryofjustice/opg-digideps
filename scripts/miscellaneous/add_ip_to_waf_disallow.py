import time

import boto3
import json

# Define the region and IP address to block
region = "eu-west-1"
ip_address = "81.178.180.162/32"  # Replace with the IP address you want to block

# Initialize a session using Amazon WAF
client = boto3.client("wafv2", region_name=region)


# Create IP set
def create_ip_set():
    response = client.create_ip_set(
        Name="BlockedIPs",
        Scope="REGIONAL",  # Use 'CLOUDFRONT' for global scope
        IPAddressVersion="IPV4",
        Addresses=[ip_address],
    )
    return response["Summary"]["ARN"], response["Summary"]["Id"]


# Get Web ACL details
def get_web_acl():
    web_acl_name = "development-web-acl"
    web_acl_id = "eb94750a-b128-46a7-ac1e-10f8e8d3526a"

    response = client.get_web_acl(
        Name=web_acl_name,
        Scope="REGIONAL",  # Use 'CLOUDFRONT' for global scope
        Id=web_acl_id,
    )
    return response["WebACL"], response["LockToken"]


# Update Web ACL
def update_web_acl(web_acl, ip_set_arn, lock_token):
    print(web_acl["Name"])
    print(web_acl["Id"])
    print(web_acl)

    web_acl["Rules"].append(
        {
            "Name": "BlockSpecificIP",
            "Priority": len(web_acl["Rules"]),
            "Action": {"Block": {}},
            "Statement": {"IPSetReferenceStatement": {"ARN": ip_set_arn}},
            "VisibilityConfig": {
                "SampledRequestsEnabled": True,
                "CloudWatchMetricsEnabled": True,
                "MetricName": "BlockSpecificIP",
            },
        }
    )
    time.sleep(60)
    response = client.update_web_acl(
        Name=web_acl["Name"],
        Scope="REGIONAL",  # Use 'CLOUDFRONT' for global scope
        Id=web_acl["Id"],
        DefaultAction=web_acl["DefaultAction"],
        Rules=web_acl["Rules"],
        VisibilityConfig=web_acl["VisibilityConfig"],
        LockToken=lock_token,
    )
    return response


# Main function
def main():
    ip_set_arn, ip_set_id = create_ip_set()
    web_acl, lock_token = get_web_acl()
    response = update_web_acl(web_acl, ip_set_arn, lock_token)
    print(json.dumps(response, indent=4))


if __name__ == "__main__":
    main()
