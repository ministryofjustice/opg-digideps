<?php

namespace AppBundle\EventListener;

use AppBundle\Exception\HasDataInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class RestInputOuputFormatter
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $defaultFormat;

    /**
     * @var array
     */
    private $supportedFormats;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var Closure
     */
    private $responseModifiers = [];

    /**
     * @var Closure
     */
    private $contextModifiers = [];

    public function __construct(Serializer $serializer, LoggerInterface $logger, array $supportedFormats, $defaultFormat, $debug)
    {
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->supportedFormats = array_values($supportedFormats);
        $this->defaultFormat = $defaultFormat;
        $this->debug = $debug;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function requestContentToArray(Request $request)
    {
        $format = $request->getContentType();

        $content = $request->getContent();
        if (!$content) {
            return [];
        }

        return $this->serializer->deserialize($request->getContent(), 'array', $format);
    }


    /**
     * Converts objects into a serialised response
     * Request format has to match the supported formats.
     * Context modifiers are applied.
     * 
     *
     *
     * @param $data
     * @param Request $request
     * @param bool $groupsCheck
     * @return Response
     */
    private function arrayToResponse($data, Request $request, $groupsCheck = false)
    {
        $format = $request->getContentType();

        if (!in_array($format, $this->supportedFormats)) {
            if ($this->defaultFormat) {
                $format = $this->defaultFormat;
            } else {
                throw new \RuntimeException("format $format not supported and  defaultFormat not defined. Supported formats: " . implode(',', $this->supportedFormats));
            }
        }

        $context = SerializationContext::create()->setSerializeNull(true);
        // context modifier
        foreach ($this->contextModifiers as $modifier) {
            $modifier($context);
        }

        if ($groupsCheck &&$context->attributes->get('groups')->isEmpty()) {
            throw new \RuntimeException($request->getUri() . " missing JMS group");
        }

        $serializedData = $this->serializer->serialize($data, $format, $context);
        $response = new Response($serializedData);
        $response->headers->set('Content-Type', 'application/' . $format);
        // response modifier
        foreach ($this->responseModifiers as $modifier) {
            $modifier($response);
        }

        return $response;
    }

    /**
     * Attach the following with
     * services:.
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $data = [
            'success' => true,
            'data' => $event->getControllerResult(),
            'message' => '',
        ];

        $response = $this->arrayToResponse($data, $event->getRequest(), true);

        $event->setResponse($response);
    }

    /**
     * Attach the following with.
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();
        $message = $e->getMessage();
        $code = (int) $e->getCode();
        $level = 'warning'; //defeault exception level, unless override

        // transform message and code
        if ($code < 400 || $code > 599) {
            $code = 500;
        }
        if ($e instanceof \InvalidArgumentException) {
            $code = 400;
        }
        if ($e instanceof AccessDeniedHttpException) {
            $code = 403;
        }

        //simple timeout
        if (419 === $code) {
            $level = 'info';
        }

        // log exception
        $this->logger->log($level, $message);

        $data = [
            'success' => false,
            'data' => ($e instanceof HasDataInterface) ? $e->getData() : '',
            'message' => $message,
            'stacktrace' => ($this->debug) ?
                    sprintf('%s: %s', get_class($e), substr($e->getTraceAsString(), 0, 64000))
                    : 'enable debug mode to see it',
            'code' => $code,
        ];

        $response = $this->arrayToResponse($data, $event->getRequest(), false);
        $response->setStatusCode($code);

        $event->setResponse($response);
    }

    public static function onKernelRequest(GetResponseEvent $event)
    {
        function_exists('xdebug_disable') && xdebug_disable();

        register_shutdown_function(function () use ($event) {
            $lastError = error_get_last();
            if (!$lastError) {
                return;
            }
            echo json_encode([
                'success' => false,
                'data' => '',
                'message' => "{$lastError['message']} {$lastError['file']}:{$lastError['line']} ",
            ]);
            die;
            //TODO find a way to use kernely temrinate instead, usgin
        });
    }

    /**
     * @param \Closure $f
     */
    public function addResponseModifier(\Closure $f)
    {
        $this->responseModifiers[] = $f;
    }

    /**
     * @param \Closure $f
     */
    public function addContextModifier(\Closure $f)
    {
        $this->contextModifiers[] = $f;
    }
}
