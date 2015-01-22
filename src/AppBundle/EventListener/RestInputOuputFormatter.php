<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
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

    public function requestBodyToArray(Request $request)
    {
        $format = $request->getContentType();

        return $this->serializer
                        ->deserialize($request->getContent(), 'array', $format);
    }

    public function handleResponse(GetResponseForControllerResultEvent $event)
    {
        $dataToReturn = array('success' => true, 'data' => $event->getControllerResult(), 'message' => '');
        $format = $event->getRequest()->getContentType();

        if (!in_array($format, $this->supportedFormats)) {
            throw new \Exception("format $format not supported. Supported formats: " . implode(',', $this->supportedFormats));
        }

        $serializedData = $this->serializer->serialize($dataToReturn, $format);
        $response = new Response($serializedData);
        $response->headers->set('content_type', 'application/' . $format);

        $event->setResponse($response);
    }

    public function handleException(GetResponseForExceptionEvent $event)
    {
        $dataToReturn = array('success' => false, 'data' => '', 'message' => $event->getException()->getMessage());
        $format = $event->getRequest()->getContentType();

        if (!in_array($event->getRequest()->getContentType(), $this->supportedFormats)) {
            throw new \Exception("format $format not supported. Supported formats: " . implode(',', $this->supportedFormats));
        }

        $serializedData = $this->serializer->serialize($dataToReturn, $format);
        $response = new Response($serializedData);
        $response->headers->set('content_type', 'application/' . $format);

        $event->setResponse($response);
    }
    
}