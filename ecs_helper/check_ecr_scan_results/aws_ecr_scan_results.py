import argparse
import json
import os
from datetime import date
from datetime import datetime
import sys
import botocore
import boto3
import requests


class ECRScanChecker:
    def __init__(self):
        self.aws_account_id = 311462405659  # management account id
        aws_iam_session = self.set_iam_role_session()

        self.aws_ecr_client = self.get_aws_client(
            'ecr',
            aws_iam_session,
        )

        self.aws_inspector2_client = self.get_aws_client(
            'inspector2',
            aws_iam_session,
        )

    def set_iam_role_session(self):
        if os.getenv('CI'):
            role_arn = f'arn:aws:iam::{self.aws_account_id}:role/opg-use-an-lpa-ci'
        else:
            role_arn = f'arn:aws:iam::{self.aws_account_id}:role/operator'

        sts = boto3.client(
            'sts',
            region_name='eu-west-1',
        )
        session = sts.assume_role(
            RoleArn=role_arn,
            RoleSessionName='checking_ecr_image_scan',
            DurationSeconds=900
        )
        return session

    @staticmethod
    def get_aws_client(client_type, aws_iam_session, region='eu-west-1'):
        client = boto3.client(
            client_type,
            region_name=region,
            aws_access_key_id=aws_iam_session['Credentials']['AccessKeyId'],
            aws_secret_access_key=aws_iam_session['Credentials']['SecretAccessKey'],
            aws_session_token=aws_iam_session['Credentials']['SessionToken'])
        return client

    def get_ecr_image_repositories(self, search_term):
        ecr_image_repositories = []
        response = self.aws_ecr_client.describe_repositories()
        for repository in response['repositories']:
            if search_term in repository['repositoryName']:
                ecr_image_repositories.append(repository['repositoryName'])

        return ecr_image_repositories

    def list_findings_for_each_repository(self, ecr_repositories, tag, push_date, report_limit):
        print('Checking ECR scan results...')
        report = ''
        for ecr_image_repository_name in ecr_repositories:
            print(ecr_image_repository_name)
            try:
                findings = self.list_findings(
                    ecr_image_repository_name, tag, push_date, report_limit)
                if findings['findings'] != []:

                    report = (
                        f'\n\n'
                        f':warning: *AWS ECR Scan found results for {ecr_image_repository_name}:*\n'
                        f'Vulnerability Reports Found.\n'
                        f'Displaying the first {report_limit} in order of severity\n\n'
                    )

                    for finding in findings['findings']:
                        report += self.summarise_finding(
                            ecr_image_repository_name, tag, finding)

            except botocore.exceptions.ClientError as error:
                print(error.response['Error']['Code'],
                      error.response['Error']['Message'])
                sys.exit(1)

        return report

    def list_findings(self, ecr_image_repository_name, tag, push_date, report_limit):
        date_start_inclusive = datetime.combine(
            push_date, datetime.min.time())

        date_end_inclusive = datetime.combine(
            push_date, datetime.max.time())

        response = self.aws_inspector2_client.list_findings(
            filterCriteria={
                'awsAccountId': [
                    {
                        'comparison': 'EQUALS',
                        'value': str(self.aws_account_id)
                    },
                ],
                'ecrImagePushedAt': [
                    {
                        'endInclusive': date_end_inclusive,
                        'startInclusive': date_start_inclusive
                    },
                ],
                'ecrImageRepositoryName': [
                    {
                        'comparison': 'EQUALS',
                        'value': ecr_image_repository_name
                    },
                ],
                'ecrImageTags': [
                    {
                        'comparison': 'EQUALS',
                        'value': tag
                    },
                ],
            },
            maxResults=report_limit,
            sortCriteria={
                'field': 'SEVERITY',
                'sortOrder': 'DESC'
            }
        )
        return response

    @classmethod
    def summarise_finding(cls, ecr_image_repository_name, tag, finding):
        severity = finding['severity']
        vuln_type = finding['type']
        cve = finding['title']
        description = 'None'
        if 'description' in finding:
            description = finding['description']
        updated = finding['updatedAt']
        link = finding['packageVulnerabilityDetails']['sourceUrl']
        result = (
            f'*Repository:* {ecr_image_repository_name} \n'
            f'*Tag:* {tag} \n'
            f'*Severity:* {severity} \n'
            f'*Type:* `{vuln_type}`\n'
            f'*CVE:* {cve} \n'
            f'*Description:* {description} \n'
            f'*Updated:* `{updated}`\n'
            f'*Link:* `{link}`\n\n'
        )
        return result

    @classmethod
    def post_to_slack(cls, slack_webhook, report):
        if report != '':
            build_url = os.getenv('CIRCLE_BUILD_URL', '')
            circleci_branch = os.getenv('CIRCLE_BRANCH', '')
            branch_info = (
                f'*Github Branch:* {circleci_branch}\n'
                f'*CircleCI Job Link:* {build_url}\n\n'
            )
            report += branch_info

            post_data = json.dumps({'text': report})
            response = requests.post(
                slack_webhook, data=post_data,
                headers={'Content-Type': 'application/json'}
            )
            if response.status_code != 200:
                raise ValueError(
                    f'Request to slack returned an error {response.status_code},'
                    f'the response is:\n'
                    f'{response.text}'
                )


def main():
    parser = argparse.ArgumentParser(
        description='Check ECR Scan results for all service container images.')
    parser.add_argument('--search',
                        default='',
                        help='The root part oof the ECR repositry path, for example online-lpa')
    parser.add_argument('--tag',
                        default='latest',
                        help='Image tag to check scan results for.')
    parser.add_argument('--ecr_push_date',
                        default=date.today(),
                        help='ECR Image push datetime in format YYYY-MM-dd')
    parser.add_argument('--result_limit',
                        default=5,
                        help='How many results for each image to return. Defaults to 5')
    parser.add_argument('--slack_webhook',
                        default=os.getenv('SLACK_WEBHOOK'),
                        help='Webhook to use, determines what channel to post to')
    parser.add_argument('--print_to_terminal', dest='print_to_terminal', action='store_const',
                        const=True, default=False,
                        help='print findings to terminal')
    parser.add_argument('--skip_post_to_slack', dest='skip_post_to_slack', action='store_const',
                        const=False, default=True,
                        help='Optionally turn off posting messages to slack')

    args = parser.parse_args()
    work = ECRScanChecker()
    ecr_repositories = work.get_ecr_image_repositories(args.search)
    report = work.list_findings_for_each_repository(
        ecr_repositories,
        args.tag,
        args.ecr_push_date,
        args.result_limit,
    )

    if args.print_to_terminal:
        print(report)

    if args.skip_post_to_slack and args.slack_webhook is not None:
        work.post_to_slack(
            args.slack_webhook,
            report,
        )
    else:
        print('Skipping post of results to slack')


if __name__ == '__main__':
    main()
