<?php

declare(strict_types=1);

namespace App\Service;

use Aws\Ssm\SsmClient;

class ParameterStoreService
{
    public const FLAG_DOCUMENT_SYNC = 'document-sync';

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

    public function putParameter(string $parameterName, string $parameterValue)
    {
        $parameterName = $this->parameterPrefix.$parameterName;
        $this->ssmClient->putParameter(['Name' => $parameterName, 'Value' => $parameterValue, 'Overwrite' => true]);
    }

    public function putFeatureFlag(string $flagName, string $flagValue)
    {
        $flagName = $this->flagPrefix.$flagName;
        $this->ssmClient->putParameter(['Name' => $flagName, 'Value' => $flagValue, 'Overwrite' => true]);
    }
}
