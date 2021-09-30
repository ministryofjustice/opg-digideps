<?php

declare(strict_types=1);

namespace App\Service;

use Aws\Ssm\SsmClient;

class ParameterStoreService
{
    /** @var SsmClient */
    private $ssmClient;

    /** @var string */
    private $parameterPrefix;

    /** @var string */
    private $flagPrefix;

    public function __construct(SsmClient $ssmClient, string $parameterPrefix, string $flagPrefix)
    {
        $this->ssmClient = $ssmClient;
        $this->parameterPrefix = $parameterPrefix;
        $this->flagPrefix = $flagPrefix;
    }

    public function getParameter(string $parameterKey)
    {
        $parameterKey = $this->parameterPrefix.$parameterKey;
        $parameter = $this->ssmClient->getParameter(['Name' => $parameterKey]);

        return $parameter['Parameter']['Value'];
    }

    public function getFeatureFlag(string $flagKey)
    {
        $flagName = $this->flagPrefix.$flagKey;
        $flag = $this->ssmClient->getParameter(['Name' => $flagName]);

        return $flag['Parameter']['Value'];
    }
}
