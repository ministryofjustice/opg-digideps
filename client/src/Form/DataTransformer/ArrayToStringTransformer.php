<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class ArrayToStringTransformer implements DataTransformerInterface
{
    public function __construct(private array $keys = [])
    {
    }

    public function transform($string)
    {
        $length = strlen($string);
        $keyCount = count($this->keys);

        $sub = floor($length / $keyCount);
        $remainder = $length % $keyCount;

        $result = [];

        $start = 0;
        $counter = 0;

        foreach ($this->keys as $value) {
            ++$counter;

            if (($remainder != 0) && ($counter == $keyCount)) {
                $result[$value] = substr($string, $start);
            } else {
                $result[$value] = substr($string, $start, $sub);
            }
            $start = $start + $sub;
        }

        return $result;
    }

    public function reverseTransform($array)
    {
        return implode('', $array);
    }
}
