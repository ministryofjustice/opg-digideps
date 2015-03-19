<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RestInputOuputFormatter
{
    const HEADER_JMS_GROUP = 'JmsSerialiseGroup';
    
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var array
     */
    private $supportedFormats;
    
    /**
     * @var boolean
     */
    private $debug;


    public function __construct(Serializer $serializer, array $supportedFormats, $debug)
    {
        $this->serializer = $serializer;
        $this->supportedFormats = array_values($supportedFormats);
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
     * @param array $data for custom serialise groups, use serialise_groups
     * @param Request $request
     * @return Response
     */
    private function arrayToResponse($data, Request $request)
    {
        $format = $request->getContentType();

        if (!in_array($format, $this->supportedFormats)) {
            throw new \Exception("format $format not supported. Supported formats: " . implode(',', $this->supportedFormats));
        }

        $context = SerializationContext::create(); //->setSerializeNull(true);
        if ($serialiseGroup = $request->headers->get(self::HEADER_JMS_GROUP)) {
            $context->setGroups([$serialiseGroup]);
        }
        
        $serializedData = $this->serializer->serialize($data, $format, $context);
        $response = new Response($serializedData);
        $response->headers->set('content_type', 'application/' . $format);
        
        return $response;
    }
    
    public static function addJmsSerialiserGroupToRequest($request, $group)
    {
        $request->headers->set(self::HEADER_JMS_GROUP, $group);
    }

    /**
     * Attach the following with
     * services:
            kernel.listener.responseConverter:
                class: AppBundle\EventListener\RestInputOuputFormatter
                arguments: [ @serializer, ["json", "xml"] ]
                tags:
                    - { name: kernel.event_listener, event: kernel.view, method: onKernelView }            
     */
    
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $data = array(
            'success' => true, 
            'data' => $event->getControllerResult(), 
            'message' => ''
        );
        
        $response = $this->arrayToResponse($data, $event->getRequest());
        
        $event->setResponse($response);
    }

    /**
     * Attach the following with
       services:
            kernel.listener.responseConverter:
                class: AppBundle\EventListener\RestInputOuputFormatter
                arguments: [ @serializer, ["json", "xml"] ]
                tags:
                    - { name: kernel.event_listener, event: kernel.exception, method: onKernelException }
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $data = array(
            'success' => false, 
            'data' => '', 
            'message' => $event->getException()->getMessage(),
            'stacktrace' => 'enable debug mode to see it',
            'code' => $event->getException()->getCode()
        );
        
        if ($this->debug) {
            $data['stacktrace'] = $event->getException()->getTraceAsString();
        }
        
        $response = $this->arrayToResponse($data, $event->getRequest());

        $event->setResponse($response);
    }
    
    
    public static function onKernelRequest(GetResponseEvent $event)
    {
        function_exists('xdebug_disable') && xdebug_disable();

        register_shutdown_function(function () use ($event) {
            $lastError = error_get_last();
            echo json_encode(array(
                'success' => false, 
                'data' => '', 
                'message' => "{$lastError['message']} {$lastError['file']}:{$lastError['line']} "
            ));
            die;
            //TODO find a way to use kernely temrinate instead, usgin 
        });

    }
    
}