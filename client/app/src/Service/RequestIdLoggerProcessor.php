<?php

namespace App\Service;

use Psr\Container\ContainerInterface;

/**
 * RequestIdLoggerProcessor.
 *
 * Log processor to add and extra key 'request_id' to the log entry with the 'x-request-id' value found in the request header
 *
 * Usage in services.yml
 * monolog.processor.add_request_id:
 *        class: App\Service\RequestIdLoggerProcessor
 *        arguments:  [ @service_container ]
 *        tags:
 *            - { name: monolog.processor, method: processRecord }
 */
class RequestIdLoggerProcessor
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * RequestIdLoggerProcessor constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Add request header 'x-request-id' into ['extra']['request_id']
     * Does not change the record if the scope is not active, or the request is not found or doesn't contain the header.
     *
     * @return array same record with extra info
     */
    public function processRecord(array $record)
    {
        $reqId = self::getRequestIdFromContainer($this->container);

        if ($reqId) {
            $record['extra']['request_id'] = $reqId;
        }

        return $record;
    }

    public static function getRequestIdFromContainer(ContainerInterface $container)
    {
        if (
            ($rq = $container->get('request_stack'))
            && ($request = $rq->getCurrentRequest())
            && ($request->headers->has('x-request-id'))
        ) {
            return $request->headers->get('x-request-id');
        }

        return null;
    }
}
