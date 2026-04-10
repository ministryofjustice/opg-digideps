<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Client;

interface RestClientInterface
{
    public function post($endpoint, $mixed, array $jmsGroups = [], $expectedResponseType = 'array');
}
