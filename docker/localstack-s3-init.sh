#! /usr/bin/env sh

set -e

awslocal logs create-log-group --log-group-name audit-local

awslocal s3api create-bucket --bucket pa-uploads-local
awslocal s3api put-bucket-versioning --bucket pa-uploads-local --versioning-configuration Status=Enabled

awslocal ssm put-parameter --name "/default/flag/document-sync" --value "1" --type String --overwrite
awslocal ssm put-parameter --name "/default/flag/checklist-sync" --value "1" --type String --overwrite
awslocal ssm put-parameter --name "/default/flag/client-benefits-questions" --value "31-12-2030 00:00:00" --type String --overwrite

awslocal ssm put-parameter --name "/default/parameter/document-sync-row-limit" --value "100" --type String --overwrite
awslocal ssm put-parameter --name "/default/parameter/checklist-sync-row-limit" --value "100" --type String --overwrite
awslocal ssm put-parameter --name "/default/parameter/document-sync-interval-minutes" --value "4" --type String --overwrite



#$ssmClient = new SsmClient([
#    'version' => 'latest',
#    'region' => 'eu-west-1',
#    'endpoint' => 'http://localstack:4583',
#    'validate' => false,
#    'credentials' => [
#        'key' => 'FAKE_ID',
#        'secret' => 'FAKE_KEY',
#    ],
#]);

#$ssmClient->putParameter([
#    'Name' => '/default/flag/document-sync',
#    'Type' => 'String',
#    'Value' => '1',
#    'Overwrite' => true,
#]);

#$ssmClient->putParameter([
#    'Name' => '/default/flag/benefits-questions',
#    'Type' => 'String',
#    'Value' => '31-12-2030 00:00:00',
#    'Overwrite' => true,
#]);

#$ssmClient->putParameter([
#    'Name' => '/default/parameter/document-sync-row-limit',
#    'Type' => 'String',
#    'Value' => '100',
#    'Overwrite' => true,
#]);

#$ssmClient->putParameter([
#    'Name' => '/default/parameter/document-sync-interval-minutes',
#    'Type' => 'String',
#    'Value' => '4.5',
#    'Overwrite' => true,
#]);

#$ssmClient->putParameter([
#    'Name' => '/default/flag/checklist-sync',
#    'Type' => 'String',
#    'Value' => '1',
#    'Overwrite' => true,
#]);

#$ssmClient->putParameter([
#    'Name' => '/default/parameter/checklist-sync-row-limit',
#    'Type' => 'String',
#    'Value' => '100',
#    'Overwrite' => true,
#]);

#$ssmClient->putParameter([
#     'Name' => '/default/flag/client-benefits-questions',
#     'Type' => 'String',
#     'Value' => '31-12-2030 00:00:00',
#     'Overwrite' => true,
#]);

#$cloudwatchLogsClient = new CloudWatchLogsClient([
#    'version' => 'latest',
#    'region' => 'eu-west-1',
#    'endpoint' => 'http://localstack:4586',
#    'validate' => false,
#    'credentials' => [
#        'key' => 'FAKE_ID',
#        'secret' => 'FAKE_KEY',
#    ],
#]);
#
#$logsResult = $cloudwatchLogsClient->describeLogGroups(['logGroupNamePrefix' => 'audit-local']);
#
#if (empty($logsResult->get('logGroups'))) {
#    $cloudwatchLogsClient->createLogGroup([
#        'logGroupName' => 'audit-local',
#    ]);
#}

#$s3Client = new S3Client([
#    'version' => 'latest',
#    'region' => 'eu-west-1',
#    'endpoint' => 'http://localstack:4572',
#    'validate' => false,
#    'use_path_style_endpoint' => true,
#    'credentials' => [
#        'key' => 'FAKE_ID',
#        'secret' => 'FAKE_KEY',
#    ],
#]);
#
#$s3Result = $s3Client->listBuckets(['logGroupNamePrefix' => 'audit-local']);
#
#if (empty($s3Result->get('Buckets'))) {
#    $s3Client->createBucket([
#        'Bucket' => 'pa-uploads-local',
#    ]);
#
#    $s3Client->putBucketVersioning([
#        'Bucket' => 'pa-uploads-local',
#        'VersioningConfiguration' => [
#            'MFADelete' => 'Disabled',
#            'Status' => 'Enabled',
#        ],
#    ]);
#}
