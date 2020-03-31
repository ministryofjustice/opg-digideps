<?php declare(strict_types=1);

namespace DigidepsTests\Helpers;

use GuzzleHttp\Psr7\MultipartStream;
use RuntimeException;

function array_walk_recursive_include_branches(array &$array, callable $callback) {
    foreach ($array as $k => &$v) {
        $callback($v, $k);
        if (is_array($v)) {
            array_walk_recursive_include_branches($v, $callback);
        }
    }
}

class MultipartPactRequest
{
    private $parts = [];

    /**
     * Add an expected part to the request
     */
    public function addPart(string $name, $contents): MultipartPactRequest
    {
        $this->parts[$name] = [
            'name' => $name,
            'contents' => $contents
        ];

        return $this;
    }

    private function isPactMatcher($node): bool
    {
        if (is_array($node) && $node['json_class'] && substr($node['json_class'], 0, 6) === 'Pact::') {
            if ($node['json_class'] === 'Pact::Term') {
                return true;
            } else {
                throw new RuntimeException('Cannot support non-regex-based matchers in multipart requests. Please use a regex matcher instead.');
            }
        } else {
            return false;
        }
    }

    /**
     * Generate an example of a request part
     */
    private function getExamplePart($part)
    {
        if (is_array($part['contents'])) {
            $contents = $part['contents'];
            array_walk_recursive_include_branches($contents, function (&$leaf) {
                if ($this->isPactMatcher($leaf)) {
                    $leaf = $leaf['data']['generate'];
                }
            });

            return [
                'name' => $part['name'],
                'contents' => json_encode($contents),
            ];
        } else {
            return $part;
        }
    }

    /**
     * Generate an example request body. This substitutes the values found in matchers
     */
    public function getExampleBody(): string
    {
        $encodedParts = array_map([$this, 'getExamplePart'], $this->parts);

        return (new MultipartStream($encodedParts))->getContents();
    }

    /**
     * Get a regex statement for a part. This uses the regex statements found in matchers
     */
    public function getRegex($name): string
    {
        $part = $this->parts[$name];

        $contents = $part['contents'];
        $replacements = [
            'match' => [],
            'replace' => [],
        ];

        array_walk_recursive_include_branches($contents, function (&$leaf) use (&$replacements) {
            if ($this->isPactMatcher($leaf)) {
                $id = uniqid();

                if (is_int($leaf['data']['generate']) || is_float($leaf['data']['generate'])) {
                    $replacements['match'][] = '"' . $id . '"';
                } else {
                    $replacements['match'][] = $id;
                }

                $replacements['replace'][] = trim($leaf['data']['matcher']['s'], '^$/');
                $leaf = $id;
            }
        });

        $baseRegex = preg_quote(json_encode($contents));
        return str_replace($replacements['match'], $replacements['replace'], $baseRegex);
    }
}
