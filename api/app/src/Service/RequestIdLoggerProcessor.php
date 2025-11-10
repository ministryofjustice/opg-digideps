<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;

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
     * Add request header 'x-aws-request-id' into ['extra']['aws_request_id']
     * Does not change the record if the scope is not active, or the request is not found or doesn't contain the header.
     *
     * @return array same record with extra info
     */
    public function processRecord(array $record)
    {
        if (!$this->container->has('request_stack')) {
            return $record;
        }

        /** @var RequestStack $rq */
        $rq = $this->container->get('request_stack');
        $request = $rq->getCurrentRequest();
        if (empty($request)) {
            return $record;
        }
        $reqId = self::getRequestIdFromContainer($request);
        $sessId = self::getSessionSafeIdFromContainer($request);

        if (!empty($reqId)) {
            $record['extra']['aws_request_id'] = $reqId;
        }

        if (!empty($sessId)) {
            $record['extra']['session_safe_id'] = $sessId;
        }

        return $record;
    }

    public static function getRequestIdFromContainer(Request $request): ?string
    {
        if ($request->headers->has('x-aws-request-id')) {
            return $request->headers->get('x-aws-request-id');
        }

        return null;
    }

    public static function getSessionSafeIdFromContainer(Request $request): ?string
    {
        if ($request->headers->has('x-session-safe-id')) {
            return $request->headers->get('x-session-safe-id');
        }

        return null;
    }
}
