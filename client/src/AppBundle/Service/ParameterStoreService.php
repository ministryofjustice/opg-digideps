<?php declare(strict_types=1);

namespace AppBundle\Service;

use Aws\Ssm\SsmClient;

class ParameterStoreService
{
    public const PARAMETER_DOCUMENT_SYNC_ROW_LIMIT = 'document-sync-row-limit';
    public const PARAMETER_DOCUMENT_SYNC_INTERVAL_MINUTES = 'document-sync-interval-minutes';

    /** @var SsmClient */
    private $ssmClient;

    /** @var string */
    private $parameterPrefix;

    public function __construct(SsmClient $ssmClient, string $parameterPrefix)
    {
        $this->ssmClient = $ssmClient;
        $this->parameterPrefix = $parameterPrefix;
    }

    public function getParameter(string $parameterKey)
    {
        $parameterKey = $this->parameterPrefix . $parameterKey;
        $parameter = $this->ssmClient->getParameter([ 'Name' => $parameterKey ]);

        return $parameter['Parameter']['Value'];
    }
}
