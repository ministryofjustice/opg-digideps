<?php

namespace App\EventListener;

use App\Exception\BusinessRulesException;
use App\Exception\HasDataInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RestInputOuputFormatter
{
    /**
     * @var array
     */
    private $supportedFormats;

    /**
     * @var \Closure[]
     */
    private $responseModifiers = [];

    /**
     * @var \Closure[]
     */
    private $contextModifiers = [];

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
        array $supportedFormats,
        private readonly string $defaultFormat,
        private readonly bool $debug
    ) {
        $this->supportedFormats = array_values($supportedFormats);
    }

    /**
     * @return array
     */
    public function requestContentToArray(Request $request)
    {
        $format = $request->getContentType();

        /** @var string $content */
        $content = $request->getContent(false);

        if (!$content || !$format) {
            return [];
        }

        /** @var array $array */
        $array = $this->serializer->deserialize($content, 'array', $format);

        return $array;
    }

    /**
     * Converts objects into a serialised response
     * Request format has to match the supported formats.
     * Context modifiers are applied.
     *
     * @param bool $groupsCheck
     *
     * @return Response
     */
    private function arrayToResponse(array $data, Request $request, $groupsCheck = true)
    {
        $format = $request->getContentType();

        if (!$format || !in_array($format, $this->supportedFormats)) {
            if ($this->defaultFormat) {
                $format = $this->defaultFormat;
            } else {
                throw new \RuntimeException("format $format not supported and  defaultFormat not defined. Supported formats: ".implode(',', $this->supportedFormats));
            }
        }

        $context = SerializationContext::create()->setSerializeNull(true);
        // context modifier
        foreach ($this->contextModifiers as $modifier) {
            $modifier($context);
        }

        // if data is defined,
        if ($groupsCheck && !empty($data['data']) && $this->containsEntity($data['data']) && false === $context->hasAttribute('groups')) {
            throw new \RuntimeException($request->getMethod().' '.$request->getUri().' missing JMS group');
        }

        $serializedData = $this->serializer->serialize($data, $format, $context);
        $response = new Response($serializedData);
        $response->headers->set('Content-Type', 'application/'.$format);
        // response modifier
        foreach ($this->responseModifiers as $modifier) {
            $modifier($response);
        }

        return $response;
    }

    private function containsEntity($object)
    {
        if (is_array($object)) {
            foreach ($object as $subObject) {
                if ($this->containsEntity($subObject)) {
                    return true;
                }
            }

            return false;
        }

        return is_object($object) && false !== strpos(get_class($object), 'Entity');
    }

    /**
     * Attach the following with
     * services:.
     */
    public function onKernelView(ViewEvent $event)
    {
        $data = [
            'success' => true,
            'data' => $event->getControllerResult(),
            'message' => '',
        ];

        $response = $this->arrayToResponse($data, $event->getRequest());

        $event->setResponse($response);
    }

    /**
     * Attach the following with.
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $e = $event->getThrowable();
        $message = $e->getMessage();
        $code = (int) $e->getCode();
        $level = 'warning'; // defeault exception level, unless override

        // transform message and code
        if ($code < 400 || $code > 599) {
            $code = 500;
        }

        if ($e instanceof BusinessRulesException) {
            $code = 409;
        }
        if ($e instanceof \InvalidArgumentException) {
            $code = 400;
        }
        if ($e instanceof AccessDeniedHttpException) {
            $code = 403;
        }
        if ($e instanceof NotFoundHttpException) {
            $code = 404;
        }

        // simple timeout
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

    public static function onKernelRequest(RequestEvent $event)
    {
        if (function_exists('xdebug_disable')) {
            xdebug_disable();
        }

        register_shutdown_function(function (): void {
            $lastError = error_get_last();
            if (!$lastError) {
                return;
            }
            echo json_encode([
                'success' => false,
                'data' => '',
                'message' => "{$lastError['message']} {$lastError['file']}:{$lastError['line']} ",
            ]);
            exit;
            // TODO find a way to use kernely temrinate instead, usgin
        });
    }

    public function addResponseModifier(\Closure $f)
    {
        $this->responseModifiers[] = $f;
    }

    public function addContextModifier(\Closure $f)
    {
        $this->contextModifiers[] = $f;
    }
}
