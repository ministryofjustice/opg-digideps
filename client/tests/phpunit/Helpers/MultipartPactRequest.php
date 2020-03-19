<?php declare(strict_types=1);

namespace DigidepsTests\Helpers;

use GuzzleHttp\Psr7\MultipartStream;

class MultipartPactRequest
{
    private $parts = [];

    public function addPart(string $name, $contents, $rules = null)
    {
        $this->parts[$name] = [
            'name' => $name,
            'contents' => $contents,
            'rules' => $rules
        ];
    }

    public function getExampleBody(): string
    {
        $encodedParts = array_map(function ($part) {
            if (is_array($part['contents'])) {
                return [
                    'name' => $part['name'],
                    'contents' => json_encode($part['contents']),
                ];
            } else {
                return $part;
            }
        }, $this->parts);

        return (new MultipartStream($encodedParts))->getContents();
    }

    public function getRegex($name): string
    {
        $part = $this->parts[$name];
        $regex = str_replace(['{', '}'], ['\{', '\}'], json_encode($part['contents']));

        // Replace example values with rules
        array_walk_recursive($part['rules'], function($value, $key) use (&$regex) {
            $regex = preg_replace(
                '/"' . $key . '":("?)(' . $value . ')\1/',
                '"' . $key . '":$1' . $value . '$1',
                $regex
            );
        });

        return $regex;
    }
}
