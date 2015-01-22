<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use JMS\Serializer\Serializer;

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
    
    public function __construct(Serializer $serializer, array $supportedFormats)
    {
        $this->serializer = $serializer;
        $this->supportedFormats = $supportedFormats;
    }
        
    
    private function createFormattedResponse($success, $data, $message, $format)
    {
        $dataToReturn = array('success' => $success, 'data' => $data, 'message'=>$message);
        
        if (!in_array($format, $this->supportedFormats)) {
            throw new \Exception("format $format not supported. Supported formats: " . implode(',', $this->supportedFormats));
            
            //TOOD add header
        }
        
        $serializedData = $this->serializer->serialize($dataToReturn, $format);
        $response = new Response($serializedData);
        $response->headers->set('content_type', 'application/' . $format);
        
        return $response;
    }
    
    public function handleResponse(GetResponseForControllerResultEvent $event)
    {
        $response = $this->createFormattedResponse(true, $event->getControllerResult(), '', $event->getRequest()->getContentType());
        
        $event->setResponse($response);
    }
    
    public function handleException(GetResponseForExceptionEvent $event)
    {
        $response = $this->createFormattedResponse(false, '', $event->getException()->getMessage(), $event->getRequest()->getContentType());
        
        $event->setResponse($response);
    }
    
}