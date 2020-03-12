<?php declare(strict_types=1);

namespace AppBundle\Service;

use Aws\Ssm\SsmClient;

class FeatureFlagService
{
    public const FLAG_DOCUMENT_SYNC = 'document-sync';

    /** @var SsmClient */
    private $ssmClient;

    /** @var string */
    private $flagPrefix;

    public function __construct(SsmClient $ssmClient, string $flagPrefix)
    {
        $this->ssmClient = $ssmClient;
        $this->flagPrefix = $flagPrefix;
    }

    public function get(string $flagKey)
    {
        $flagName = $this->flagPrefix . $flagKey;
        $flag = $this->ssmClient->getParameter([ 'Name' => $flagName ]);

        return $flag['Parameter']['Value'];
    }
}
