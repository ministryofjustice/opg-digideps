<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<string, array>
 */
class ArrayToStringTransformer implements DataTransformerInterface
{
    private $keys;

    public function __construct(array $keys = [])
    {
        $this->keys = $keys;
    }

    /**
     * @param string $value
     */
    public function transform(mixed $value): array
    {
        $length = strlen($value);
        $keyCount = count($this->keys);

        $sub = floor($length / $keyCount);
        $remainder = $length % $keyCount;

        $result = [];

        $start = 0;
        $counter = 0;

        foreach ($this->keys as $key) {
            ++$counter;

            if ((0 != $remainder) && ($counter == $keyCount)) {
                $result[$key] = substr($value, $start);
            } else {
                $result[$key] = substr($value, $start, $sub);
            }
            $start = $start + $sub;
        }

        return $result;
    }

    public function reverseTransform($value)
    {
        return implode('', $value);
    }
}
