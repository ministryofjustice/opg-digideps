import boto3
import argparse
import requests
import json
import os


class ECRScanChecker:
    aws_account_id = ''
    aws_iam_session = ''
    aws_ecr_client = ''
    images_to_check = []
    tag = ''
    report = ''
    report_limit = ''
    major_sev_count = 0

    def __init__(self, report_limit, search_term):
        self.report_limit = int(report_limit)
        self.aws_account_id = 311462405659  # management account id
        self.set_iam_role_session()
        self.aws_ecr_client = boto3.client(
            'ecr',
            region_name='eu-west-1',
            aws_access_key_id=self.aws_iam_session['Credentials']['AccessKeyId'],
            aws_secret_access_key=self.aws_iam_session['Credentials']['SecretAccessKey'],
            aws_session_token=self.aws_iam_session['Credentials']['SessionToken'])
        self.images_to_check = self.get_repositories(search_term)

    def set_iam_role_session(self):
        if os.getenv('CI'):
            role_arn = 'arn:aws:iam::{}:role/digideps-ci'.format(
                self.aws_account_id)
        else:
            role_arn = 'arn:aws:iam::{}:role/operator'.format(
                self.aws_account_id)

        sts = boto3.client(
            'sts',
            region_name='eu-west-1',
        )
        session = sts.assume_role(
            RoleArn=role_arn,
            RoleSessionName='checking_ecr_image_scan',
            DurationSeconds=900
        )
        self.aws_iam_session = session

    def get_repositories(self, search_term):
        images_to_check = []
        response = self.aws_ecr_client.describe_repositories()
        for repository in response["repositories"]:
            if search_term in repository["repositoryName"]:
                images_to_check.append(repository["repositoryName"])
        return images_to_check

    def recursive_wait(self, tag):
        print("Waiting for ECR scans to complete...")
        for image in self.images_to_check:
            self.wait_for_scan_completion(image, tag)
        print("ECR image scans complete")

    def wait_for_scan_completion(self, image, giventag):
        for tag in (giventag, "latest"):
            tag_exists = False
            try:
                waiter = self.aws_ecr_client.get_waiter('image_scan_complete')
                tag_exists = True
                waiter.wait(
                    repositoryName=image,
                    imageId={
                        'imageTag': tag
                    },
                    WaiterConfig={
                        'Delay': 5,
                        'MaxAttempts': 60
                    }
                )
            except:
                continue
        if not tag_exists:
            print("No ECR image scan results for image {0}, tag {1}".format(
                image, giventag))

    def recursive_check_make_report(self, giventag):
        print("Checking ECR scan results...")
        for image in self.images_to_check:
            tag_exists = False
            for tag in (giventag, "latest"):
                try:
                    findings = self.get_ecr_scan_findings(image, tag)[
                        "imageScanFindings"]
                    tag_exists = True
                    if findings["findings"] != []:
                        counts = findings["findingSeverityCounts"]
                        if ("CRITICAL" in counts) or ("HIGH" in counts) or ("MEDIUM" in counts):
                          self.major_sev_count += 1
                        title = "\n\n:warning: *AWS ECR Scan found results for {}:* \n".format(
                            image)
                        severity_counts = "Severity finding counts:\n{}\nDisplaying the first {} in order of severity\n\n".format(
                            counts, self.report_limit)
                        self.report = title + severity_counts
                        for finding in findings["findings"]:
                            cve = finding["name"]
                            description = "No description available"
                            if "description" in finding:
                              description = finding["description"]
                            severity = finding["severity"]
                            link = finding["uri"]
                            result = "*Image:* {0} \n**Tag:* {1} \n*Severity:* {2} \n*CVE:* {3} \n*Description:* {4} \n*Link:* {5}\n\n".format(
                                image, tag, severity, cve, description, link)
                            self.report += result
                        print(self.report)

                except:
                    continue
            if not tag_exists:
                print("Unable to get ECR image scan results for image {0}, tag {1}".format(
                    image, giventag))

    def get_ecr_scan_findings(self, image, tag):
        response = self.aws_ecr_client.describe_image_scan_findings(
            repositoryName=image,
            imageId={
                'imageTag': tag
            },
            maxResults=self.report_limit
        )
        return response

    def post_to_slack(self, slack_webhook):
        if self.major_sev_count > 0:
            print(f"Sending slack message as there are {self.major_sev_count} images with flagged security vulnerabilities")
            branch_info = "*Images With Serious Issues:* {0}\n *Github Branch:* {1}\n*CircleCI Job Link:* {2}\n\n".format(
                self.major_sev_count,
                os.getenv('CIRCLE_BRANCH', ""),
                os.getenv('CIRCLE_BUILD_URL', ""))

            post_data = json.dumps({"channel": "opg-digideps-devs", "text": branch_info})
            print(post_data)
            response = requests.post(
                slack_webhook, data=post_data,
                headers={'Content-Type': 'application/json'}
            )
            if response.status_code != 200:
                raise ValueError(
                    'Request to slack returned an error %s, the response is:\n%s'
                    % (response.status_code, response.text)
                )
        else:
            print(f"Not sending slack message as there are {self.major_sev_count} images with flagged security vulnerabilities")


def main():
    parser = argparse.ArgumentParser(
        description="Check ECR Scan results for all service container images.")
    parser.add_argument("--search",
                        default="",
                        help="The root part of the ECR repository path, for example digideps")
    parser.add_argument("--tag",
                        default="latest",
                        help="Image tag to check scan results for.")
    parser.add_argument("--result_limit",
                        default=5,
                        help="How many results for each image to return. Defaults to 5")
    parser.add_argument("--slack_webhook",
                        default=os.getenv('SLACK_WEBHOOK'),
                        help="Webhook to use, determines what channel to post to")
    parser.add_argument("--post_to_slack",
                        default=True,
                        help="Optionally turn off posting messages to slack")

    args = parser.parse_args()
    work = ECRScanChecker(args.result_limit, args.search)
    work.recursive_wait(args.tag)
    work.recursive_check_make_report(args.tag)
    if args.slack_webhook is None:
        print("No slack webhook provided, skipping post of results to slack")
    elif args.post_to_slack != "True":
        print("Post to slack flag set to false. Not sending to slack")
    else:
        work.post_to_slack(args.slack_webhook)


if __name__ == "__main__":
    main()
