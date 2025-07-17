<?php

declare(strict_types=1);

namespace App\Model;

class Hydrator
{
    public static function hydrateEntityWithArrayData($object, array $data, array $keySetters): void
    {
        foreach ($keySetters as $k => $setter) {
            if (array_key_exists($k, $data)) {
                $object->$setter($data[$k]);
            }
        }
    }
}
