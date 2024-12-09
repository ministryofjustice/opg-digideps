import boto3
from botocore.exceptions import ClientError


# Use this script when you have lingering security groups
# in development environment that are no longer managed by terraform


def delete_security_groups(sg_list):
    """
    Deletes security groups and their rules, including references from other security groups.
    """
    ec2 = boto3.client("ec2")

    for sg_id_or_name in sg_list:
        try:
            # Fetch the security group by ID or Name
            response = ec2.describe_security_groups(GroupIds=[sg_id_or_name])
            security_group = response["SecurityGroups"][0]
        except ClientError:
            try:
                # If GroupId lookup fails, try fetching by GroupName
                response = ec2.describe_security_groups(GroupNames=[sg_id_or_name])
                security_group = response["SecurityGroups"][0]
            except ClientError as err:
                print(f"Failed to find security group '{sg_id_or_name}': {err}")
                continue

        sg_id = security_group["GroupId"]
        sg_name = security_group["GroupName"]
        print(f"Processing security group: {sg_name} (ID: {sg_id})")

        # Remove rules from other security groups that reference this group
        remove_referencing_rules(sg_id)

        # Remove inbound rules
        try:
            if security_group["IpPermissions"]:
                ec2.revoke_security_group_ingress(
                    GroupId=sg_id, IpPermissions=security_group["IpPermissions"]
                )
                print(f"  Ingress rules removed for {sg_name} (ID: {sg_id})")
        except ClientError as err:
            print(f"  Error removing ingress rules for {sg_name}: {err}")

        # Remove outbound rules
        try:
            if security_group["IpPermissionsEgress"]:
                ec2.revoke_security_group_egress(
                    GroupId=sg_id, IpPermissions=security_group["IpPermissionsEgress"]
                )
                print(f"  Egress rules removed for {sg_name} (ID: {sg_id})")
        except ClientError as err:
            print(f"  Error removing egress rules for {sg_name}: {err}")

        # Delete the security group
        try:
            ec2.delete_security_group(GroupId=sg_id)
            print(f"  Security group {sg_name} (ID: {sg_id}) deleted successfully.")
        except ClientError as err:
            print(f"  Error deleting security group {sg_name} (ID: {sg_id}): {err}")


def remove_referencing_rules(target_sg_id):
    """
    Removes rules in other security groups that reference the target security group.
    """
    ec2 = boto3.client("ec2")

    try:
        # Fetch all security groups
        all_sgs = ec2.describe_security_groups()
        for sg in all_sgs["SecurityGroups"]:
            sg_id = sg["GroupId"]
            sg_name = sg["GroupName"]

            # Check and remove inbound rules referencing the target SG
            for rule in sg.get("IpPermissions", []):
                for user_id_group_pair in rule.get("UserIdGroupPairs", []):
                    if user_id_group_pair.get("GroupId") == target_sg_id:
                        print(
                            f"  Found inbound reference to {target_sg_id} in {sg_name} (ID: {sg_id})"
                        )
                        try:
                            ec2.revoke_security_group_ingress(
                                GroupId=sg_id, IpPermissions=[rule]
                            )
                            print(
                                f"    Removed inbound reference from {sg_name} (ID: {sg_id})"
                            )
                        except ClientError as err:
                            print(
                                f"    Error removing inbound reference from {sg_name}: {err}"
                            )

            # Check and remove outbound rules referencing the target SG
            for rule in sg.get("IpPermissionsEgress", []):
                for user_id_group_pair in rule.get("UserIdGroupPairs", []):
                    if user_id_group_pair.get("GroupId") == target_sg_id:
                        print(
                            f"  Found outbound reference to {target_sg_id} in {sg_name} (ID: {sg_id})"
                        )
                        try:
                            ec2.revoke_security_group_egress(
                                GroupId=sg_id, IpPermissions=[rule]
                            )
                            print(
                                f"    Removed outbound reference from {sg_name} (ID: {sg_id})"
                            )
                        except ClientError as err:
                            print(
                                f"    Error removing outbound reference from {sg_name}: {err}"
                            )
    except ClientError as err:
        print(f"Error fetching security groups: {err}")


# Run this script with aws-vault for targeting the dev environment.
# This script if meant as helper. Avoid using this on non dev envs! Do it manually for safety.
if __name__ == "__main__":
    sg_list = [""]
    delete_security_groups(sg_list)
