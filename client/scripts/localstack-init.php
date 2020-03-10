<?php

use Aws\Ssm\SsmClient;
use GuzzleHttp\Client;

require __DIR__ . '/../vendor/autoload.php';

function isLocalstackAvailable() {
    try {
        $client = new Client();
        $response = $client->request('GET', 'http://localstack:8080');
        return $response->getStatusCode() === 200;
    } catch (Throwable $e) {
        return false;
    }
}

do {
    sleep(1);
} while (isLocalstackAvailable() === false);

$ssmClient = new SsmClient([
    'version'  => 'latest',
    'region'  => 'eu-west-1',
    'endpoint'  => 'http://localstack:4583',
    'validate'  => false,
    'credentials'  => [
        'key' => 'FAKE_ID',
        'secret' => 'FAKE_KEY',
    ],
]);

$ssmClient->putParameter([
    'Name' => '/default/flag/document-sync',
    'Type' => 'String',
    'Value' => '1',
    'Overwrite' => true
]);
