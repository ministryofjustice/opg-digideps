<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RestInputOuputFormatter
{

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
     * @param array $data
     * @param Request $request
     * @return Response
     */
    private function arrayToResponse($data, Request $request)
    {
        $format = $request->getContentType();

        if (!in_array($format, $this->supportedFormats)) {
            throw new \Exception("format $format not supported. Supported formats: " . implode(',', $this->supportedFormats));
        }

        $serializedData = $this->serializer->serialize($data, $format);
        $response = new Response($serializedData);
        $response->headers->set('content_type', 'application/' . $format);
        
        return $response;
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
        $data = array('success' => true, 'data' => $event->getControllerResult(), 'message' => '');
        
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
            'stacktrace' => 'enable debug mode to see it'
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
            $errorGetLast = error_get_last();
            $message = "{$errorGetLast['message']} {$errorGetLast['file']}:{$errorGetLast['line']} ";
            $data = array('success' => false, 'data' => '', 'message' => $message);
            
//            $response = $this->arrayToResponse($data, $event->getRequest());
//            echo get_class($response);die;
//            $response->sendHeaders();
//            $response->sendContent();
//            die;
            
            echo json_encode($data);
            die;
            //TODO find a way to use kernely temrinate instead, usgin 
        });

    }
    
}