<?php

namespace AppBundle\v2\DTO;

trait DtoPropertySetterTrait
{
    /**
     * @param $dto
     * @param array $data
     * @param array $exclude
     */
    private function setPropertiesFromData($dto, array $data, array $exclude = [])
    {
        foreach ($data as $property => $value) {
            $setter = sprintf('set%s', ucfirst($property));

            if (isset($data[$property]) && method_exists($dto, $setter) && !in_array($property, $exclude)) {
                $dto->{$setter}($value);
            }
        }
    }
}
