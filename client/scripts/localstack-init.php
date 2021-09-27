<?php

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\S3\S3Client;
use Aws\Ssm\SsmClient;
use GuzzleHttp\Client;

require __DIR__.'/../vendor/autoload.php';

function isLocalstackAvailable()
{
    try {
        $client = new Client();
        $response = $client->request('GET', 'http://localstack:8080');

        return 200 === $response->getStatusCode();
    } catch (Throwable $e) {
        return false;
    }
}

do {
    sleep(1);
} while (false === isLocalstackAvailable());

$ssmClient = new SsmClient([
    'version' => 'latest',
    'region' => 'eu-west-1',
    'endpoint' => 'http://localstack:4583',
    'validate' => false,
    'credentials' => [
        'key' => 'FAKE_ID',
        'secret' => 'FAKE_KEY',
    ],
]);

$ssmClient->putParameter([
    'Name' => '/default/flag/document-sync',
    'Type' => 'String',
    'Value' => '1',
    'Overwrite' => true,
]);

$ssmClient->putParameter([
    'Name' => '/default/parameter/document-sync-row-limit',
    'Type' => 'String',
    'Value' => '100',
    'Overwrite' => true,
]);

$ssmClient->putParameter([
    'Name' => '/default/parameter/document-sync-interval-minutes',
    'Type' => 'String',
    'Value' => '4.5',
    'Overwrite' => true,
]);

$ssmClient->putParameter([
    'Name' => '/default/flag/checklist-sync',
    'Type' => 'String',
    'Value' => '1',
    'Overwrite' => true,
]);

$ssmClient->putParameter([
    'Name' => '/default/parameter/checklist-sync-row-limit',
    'Type' => 'String',
    'Value' => '100',
    'Overwrite' => true,
]);

$ssmClient->putParameter([
    'Name' => '/default/flag/benefits-questions',
    'Type' => 'String',
    'Value' => '31-12-2030 00:00:00',
    'Overwrite' => true,
]);

$ssmClient->putParameter([
    'Name' => '/default/flag/paper-reports',
    'Type' => 'String',
    'Value' => '1',
    'Overwrite' => true,
]);

$cloudwatchLogsClient = new CloudWatchLogsClient([
    'version' => 'latest',
    'region' => 'eu-west-1',
    'endpoint' => 'http://localstack:4586',
    'validate' => false,
    'credentials' => [
        'key' => 'FAKE_ID',
        'secret' => 'FAKE_KEY',
    ],
]);

$logsResult = $cloudwatchLogsClient->describeLogGroups(['logGroupNamePrefix' => 'audit-local']);

if (empty($logsResult->get('logGroups'))) {
    $cloudwatchLogsClient->createLogGroup([
        'logGroupName' => 'audit-local',
    ]);
}

$s3Client = new S3Client([
    'version' => 'latest',
    'region' => 'eu-west-1',
    'endpoint' => 'http://localstack:4572',
    'validate' => false,
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key' => 'FAKE_ID',
        'secret' => 'FAKE_KEY',
    ],
]);

$s3Result = $s3Client->listBuckets(['logGroupNamePrefix' => 'audit-local']);

if (empty($s3Result->get('Buckets'))) {
    $s3Client->createBucket([
        'Bucket' => 'pa-uploads-local',
    ]);

    $s3Client->putBucketVersioning([
        'Bucket' => 'pa-uploads-local',
        'VersioningConfiguration' => [
            'MFADelete' => 'Disabled',
            'Status' => 'Enabled',
        ],
    ]);
}
