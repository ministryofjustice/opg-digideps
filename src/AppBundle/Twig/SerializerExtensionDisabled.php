<?php

namespace AppBundle\Twig;

use JMS\Serializer\SerializerInterface;

class SerializerExtensionDisabled extends \Twig_Extension
{
    protected $serializer;

    public function getName()
    {
        return 'jms_serializer';
    }

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getFilters()
    {
        return [];
    }

    public function getFunctions()
    {
        return [];
    }
}
