<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service;

use Aws\Ssm\SsmClient;

class ParameterStoreService
{
    public const string PARAMETER_CHECKLIST_SYNC_ROW_LIMIT = 'checklist-sync-row-limit';
    public const string PARAMETER_DOCUMENT_SYNC_ROW_LIMIT = 'document-sync-row-limit';
    public const string FLAG_CHECKLIST_SYNC = 'checklist-sync';
    public const string FLAG_DOCUMENT_SYNC = 'document-sync';

    private SsmClient $ssmClient;
    private string $parameterPrefix;
    private string $flagPrefix;

    public function __construct(SsmClient $ssmClient, string $parameterPrefix, string $flagPrefix)
    {
        $this->ssmClient = $ssmClient;
        $this->parameterPrefix = $parameterPrefix;
        $this->flagPrefix = $flagPrefix;
    }

    public function getParameter(string $parameterKey)
    {
        $parameterKey = $this->parameterPrefix . $parameterKey;
        $parameter = $this->ssmClient->getParameter(['Name' => $parameterKey]);

        return $parameter['Parameter']['Value'];
    }

    public function getFeatureFlag(string $flagKey)
    {
        $flagName = $this->flagPrefix . $flagKey;
        $flag = $this->ssmClient->getParameter(['Name' => $flagName]);

        return $flag['Parameter']['Value'];
    }

    public function putFeatureFlag(string $flagName, string $flagValue): void
    {
        $flagName = $this->flagPrefix . $flagName;
        $this->ssmClient->putParameter(['Name' => $flagName, 'Value' => $flagValue, 'Overwrite' => true]);
    }
}
