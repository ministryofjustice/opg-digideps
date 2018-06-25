<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * RequestIdLoggerProcessor.
 *
 * Log processor to add and extra key 'request_id' to the log entry with the 'x-request-id' value found in the request header
 *
 * Usage in services.yml
 * monolog.processor.add_request_id:
 *        class: AppBundle\Service\RequestIdLoggerProcessor
 *        arguments:  [ @service_container ]
 *        tags:
 *            - { name: monolog.processor, method: processRecord }
 */
class RequestIdLoggerProcessor
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * RequestIdLoggerProcessor constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Add request header 'x-request-id' into ['extra']['request_id']
     * Does not change the record if the scope is not active, or the request is not found or doesn't contain the header.
     *
     * @param array $record
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
        if (($rq = $container->get('request_stack'))
            && ($request = $rq->getCurrentRequest())
            && ($request->headers->has('x-request-id'))) {
            return $request->headers->get('x-request-id');
        }

        return null;
    }
}
