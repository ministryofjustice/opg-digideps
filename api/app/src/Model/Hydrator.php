<?php

declare(strict_types=1);

namespace App\Model;

class Hydrator
{
    /**
     * @param object $object     Any object with setters
     * @param array  $data       An array mapping field names to values
     * @param array  $keySetters An array mapping a field name from $data to a setter method on $object
     */
    public static function hydrateEntityWithArrayData(object $object, array $data, array $keySetters): void
    {
        foreach ($keySetters as $k => $setter) {
            if (array_key_exists($k, $data)) {
                $object->$setter($data[$k]);
            }
        }
    }
}
