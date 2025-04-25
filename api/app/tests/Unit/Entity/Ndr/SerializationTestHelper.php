<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Ndr;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;

class SerializationTestHelper
{
    public static function serialize(mixed $data, ?string $group): string
    {
        $serializer = SerializerBuilder::create()->build();

        $context = new SerializationContext();
        if ($group) {
            $context->setGroups($group);
        }

        return $serializer->serialize($data, 'json', $context);
    }
}
