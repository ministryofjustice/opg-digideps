<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\Container;

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
        if (!$this->container->isScopeActive('request')
            || !$this->container->has('request')
            || !($request = $this->container->get('request'))
            || !$request->headers->has('x-request-id')
        ) {
            return $record;
        }

        $record['extra']['request_id'] = $request->headers->get('x-request-id');

        return $record;
    }
}
