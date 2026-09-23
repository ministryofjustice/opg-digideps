<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Helpers;

class ObjectTester
{
    /**
     * @template T of object
     * @param T $obj
     * @param \ReflectionClass<T> $reflection
     */
    public static function testObjectWithReflection(object $obj, \ReflectionClass $reflection): array
    {
        $errors = [];
        foreach ($reflection->getProperties() as $property) {
            try {
                $_ = $property->getValue($obj);
                if ($property->getType() === null) {
                    $errors[] = [
                        $property->getName(),
                        $property->getDeclaringClass()->getName(),
                        ''
                    ];
                }
            } catch (\Throwable $_) {
                $errors[] = [
                    $property->getName(),
                    $property->getDeclaringClass()->getName(),
                    "{$property->getType()}"
                ];
            }
        }

        if ($reflection->getParentClass()) {
            $errors = [...self::testObjectWithReflection($obj, $reflection->getParentClass()), ...$errors];
        }

        return $errors;
    }
}
