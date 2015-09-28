<?php
namespace AppBundle\Service;

use JMS\Serializer\SerializerInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Message\RequestInterface as GuzzleRequestInterface;
use AppBundle\Exception\DisplayableException;
use RuntimeException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ApiClient extends GuzzleClient
{
    /**
     * @var SerializerInterface
     */
    private $serialiser;

     /**
     * @var string
     */
    private $format;

     /**
      * If true, prints more info on exception
     * @var boolean
     */
    private $debug;

    private $session;
    
    private $options;

     /**
     * @var string
     */
    private $acceptedFormats = ['json']; //xml should work but need to be tested first

    /**
     * @var array 
     */
    private $history = [];

    /**
     * Contains debug information about the last failed call. 
     * To use for loggin, not meant to be displayed to the user
     * 
     * @var string 
     */
    private $lastErrorMessage;
    
    public function __construct(ContainerInterface $container, array $options)
    {
        $this->serialiser = $container->get('jms_serializer');
        
        // check arguments
        array_map(function($k) use ($options) {
            if (!array_key_exists($k, $options)) {
                throw new \InvalidArgumentException(__METHOD__ . " missing value for $k");
            }
        }, ['base_url', 'format', 'debug']);

        // set internal properties
        $this->format = $options['format'];
        if (!in_array($this->format, $this->acceptedFormats)) {
            throw new \InvalidArgumentException(
                __CLASS__ . ': '. $this->format . ' not valid. Accepted formats:' . implode(',', $this->acceptedFormats
            ));
        }
        $this->debug = $options['debug'];
        $this->options = $options;
        $this->collectData = $options['collectData'];
        
        $config = $this->getGuzzleClientConfig();
        
        parent::__construct($config);
    }

    /**
     * @param string $class
     * @param string $endpoint
     * @param array $options
     *
     * @return stdClass entity object
     */
    public function getEntity($class, $endpoint, array $options = [])
    {
        $response = $this->get($endpoint, $options); 
        
        $responseArray = $this->deserialiseResponse($response);
        $ret = $this->serialiser->deserialize(json_encode($responseArray['data']), 'AppBundle\\Entity\\' . $class, $this->format);
        
        return $ret;
    }

    /**
     * @param RequestException $e
     * @return string
     */
    private function getDebugRequestExceptionData(RequestException $e)
    {
        if (!$this->debug) {
            return '';
        }

        $ret = [];

        $url = $e->getRequest()->getUrl();
        $body = $e->getResponse() ? (string)$e->getResponse()->getBody() : '[No body found in response]';

        $ret[] = "Url: $url";
        $ret[] = "Response body: $body";
        $ret[] = "Exception trace: " . $e->getTraceAsString();
        if ($e->getRequest()->getMethod() == 'POST') {
            $ret[] = 'Request: ' . $e->getRequest()->getBody();
        }

        return 'Debug informations (only displayed when kernel.debug=true):' . implode(', ', $ret);
    }

    private function logRequest(GuzzleRequestInterface $request, $response, $time)
    {
        if (!$this->collectData) {
            return;
        }
        
        $this->history[] = [
            'url' => $request->getUrl(),
            'method' => $request->getMethod(),
            'requestBody' => method_exists($request, 'getBody') ? (string)$request->getBody() : null,
            'response' => method_exists($response, 'getBody') ? (string)$response->getBody() : null,
            'time' => $time
        ];
    }

    
    /**
     * Override send() to recognise and re-throw error messages in a more understandable format
     *
     * @param GuzzleRequestInterface $request
     *
     * @throws \RuntimeException
     */
    public function send(GuzzleRequestInterface $request)
    {
        try {
            $start = microtime(true);
            $response =  parent::send($request);
            $this->logRequest($request, $response, microtime(true) - $start);
            
            return $response;
        } catch (\Exception $e) {
            
            if ($e instanceof RequestException) {

                // try to unserialize response
                $response = $e->getResponse();
                if (!$response instanceof ResponseInterface) {
                    throw new RuntimeException("No response from API. " . $this->getDebugRequestExceptionData($e));
                }
                try {
                    $responseArray = $this->serialiser->deserialize($response->getBody(), 'array', $this->format);
                    $this->lastErrorMessage = $responseArray['message'];
                } catch (\Exception $e) {
                    $this->lastErrorMessage = $e->getMessage();
                    throw new RuntimeException("Error from API: malformed message. " . $e->getMessage());
                }

                // regognise specific error codes and launch specific exception classes
                
                if(!isset($responseArray['code'])){
                   $responseArray['code'] = 401;
                   //$responseArray['message'] = isset($responseArray['error_description']) ? $responseArray['error_description']: $responseArray['message'];
                   
                   if(isset($responseArray['error_description'])){
                       $responseArray['message'] = $responseArray['error_description'];
                   }elseif(!isset($responseArray['message'])){
                       $responseArray['message'] = null;
                   }
                }

                switch ($responseArray['code']) {
                    case 404:
                        throw new DisplayableException('Record not found.' . $this->getDebugRequestExceptionData($e));
                    default:
                        throw new RuntimeException($responseArray['message'] . ' ' . $this->getDebugRequestExceptionData($e), $responseArray['code']);
                }
            } else {
                $this->lastErrorMessage = $e->getMessage();
            }

            throw new RuntimeException($e->getMessage() ?: 'Generic error from API');
        }

    }

    /**
     * @param Response $response
     *
     * @return object result of deserialisation
     */
    private function deserialiseResponse($response)
    {
        try {
            $ret = $this->serialiser->deserialize($response->getBody(), 'array', $this->format);
        } catch (\JMS\Serializer\Exception\RuntimeException $e) {
            $msg = 'Cannot deserialise response.';
            if ($this->debug) {
                $msg .= 'Body:' . $response->getBody();
            }
            throw new RuntimeException(
                $e->getMessage() . '.'
                . ($this->debug ? 'Body:' . $response->getBody() : '')
            );
        }

        return $ret;
    }

    /**
     * @param string $class
     * @param string $endpoint
     * @param array $options
     *
     * @return stdClass[] array of entity objects, indexed by PK
     */
    public function getEntities($class, $endpoint, $options = [])
    {
        $responseArray = $this->deserialiseResponse($this->get($endpoint, $options));

        $ret = [];

        foreach ($responseArray['data'] as $row) {
            $entity = $this->serialiser->deserialize(json_encode($row), 'AppBundle\\Entity\\' . $class, 'json');
            $ret[$entity->getId()] = $entity;
        }

        return $ret;
    }


    /**
     * @param string $endpoint
     * @param string $bodyorEntity json_encoded string or Doctrine Entity (it will be serialised before posting)
     * @param string $options serialise group (indicated by @Groups annotation in the client entity)
     *
     * @return array response
     */
    public function postC($endpoint, $bodyorEntity, array $options = [])
    {
        $body = $this->serialiseBodyOrEntity($bodyorEntity, $options);

        if(isset($options['deserialise_group'])){
            unset($options['deserialise_group']);
        }
        $options['body'] = $body;

        $responseArray = $this->deserialiseResponse($this->post($endpoint, $options));
        return $responseArray['data'];
    }

    /**
     * @param string $endpoint
     * @param string $bodyorEntity json_encoded string or Doctrine Entity (it will be serialised before posting)
     *
     * @return array response
     */
    public function putC($endpoint, $bodyorEntity, array $options = [])
    {
        $body = $this->serialiseBodyOrEntity($bodyorEntity, $options);
        
        if(isset($options['deserialise_group'])){
            unset($options['deserialise_group']);
        }

        $options['body'] = $body;

        $responseArray = $this->deserialiseResponse($this->put($endpoint, $options));

        return $responseArray['data'];
    }

    /**
     *
     * @param string $bodyorEntity json_encoded string or Doctrine Entity (it will be serialised before posting)
     * @param array $options
     * @return type
     */
    private function serialiseBodyOrEntity($bodyorEntity, array $options)
    {
        if (is_object($bodyorEntity)) {

            $context = \JMS\Serializer\SerializationContext::create()
                    ->setSerializeNull(true);

            if (!empty($options['deserialise_group'])) {
                $context->setGroups([$options['deserialise_group']]);
            }
            return $this->serialiser->serialize($bodyorEntity, 'json', $context);
        }

        return $bodyorEntity;
    }

    /**
     * @return array $config
     */
    private function getGuzzleClientConfig()
    {
        $config = [ 'base_url' =>  $this->options['base_url'],
                    'defaults' => ['headers' => [ 'Content-Type' => 'application/' . $this->format ],
                                   'verify' => false ]];
        
        // use HTTP 1.0 to avoid "cURL error 56: Problem (2) in the Chunked-Encoded data"
        $config['defaults']['version'] = 1.0;
        
        return $config;
    }
    
    /**
     * @return array
     */
    public function getHistory()
    {
        return $this->history;
    }
    
    
    /**
     * @return string
     */
    public function getLastErrorMessage()
    {
        return $this->lastErrorMessage;
    }

}
