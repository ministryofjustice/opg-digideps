<?php declare(strict_types=1);

namespace AppBundle\Service;

use Aws\Ssm\SsmClient;

class ParameterStoreService
{
    public const FLAG_DOCUMENT_SYNC = 'document-sync';
    public const PARAMETER_DOCUMENT_SYNC_ROW_LIMIT = 'document-sync-row-limit';
    public const PARAMETER_DOCUMENT_SYNC_INTERVAL_MINUTES = 'document-sync-interval-minutes';

    public const FLAG_CHECKLIST_SYNC = 'checklist-sync';
    public const PARAMETER_CHECKLIST_SYNC_ROW_LIMIT = 'checklist-sync-row-limit';

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
        $parameterKey = $this->parameterPrefix . $parameterKey;
        $parameter = $this->ssmClient->getParameter([ 'Name' => $parameterKey ]);

        return $parameter['Parameter']['Value'];
    }

    public function getFeatureFlag(string $flagKey)
    {
        $flagName = $this->flagPrefix . $flagKey;
        $flag = $this->ssmClient->getParameter([ 'Name' => $flagName ]);

        return $flag['Parameter']['Value'];
    }
}
