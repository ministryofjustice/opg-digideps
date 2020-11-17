<?php declare(strict_types=1);


namespace AppBundle\Service\Audit;

use AppBundle\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;

abstract class BaseLogger
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var DateTimeProvider */
    protected $dateTimeProvider;

    /**
     * @param LoggerInterface $logger
     * @return BaseLogger
     */
    public function setLogger(LoggerInterface $logger): BaseLogger
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param DateTimeProvider $dateTimeProvider
     * @return BaseLogger
     */
    public function setDateTimeProvider(DateTimeProvider $dateTimeProvider): BaseLogger
    {
        $this->dateTimeProvider = $dateTimeProvider;
        return $this;
    }
}
