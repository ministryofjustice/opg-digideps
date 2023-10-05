<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class CssClassCollector extends DataCollector
{
    public function collect(Request $request, Response $response, \Throwable $throwable = null)
    {
        $appClasses = [];
        $govukClasses = [];
        $mojClasses = [];
        $otherClasses = [];

        preg_match_all('/class=("|\')(.+?)\1/', $response->getContent(), $matches);

        foreach ($matches[2] as $classList) {
            foreach (explode(' ', $classList) as $className) {
                if ('' === $className) {
                    continue;
                }

                if ('behat-' === substr($className, 0, 6)) {
                    // Ignore behat classes
                    continue;
                } elseif ('opg-' === substr($className, 0, 4)) {
                    $counter = &$appClasses;
                } elseif ('govuk-' === substr($className, 0, 6)) {
                    $counter = &$govukClasses;
                } elseif ('moj-' === substr($className, 0, 4)) {
                    $counter = &$mojClasses;
                } else {
                    $counter = &$otherClasses;
                }

                if (array_key_exists($className, $counter)) {
                    ++$counter[$className];
                } else {
                    $counter[$className] = 1;
                }
            }
        }

        arsort($appClasses);
        arsort($govukClasses);
        arsort($mojClasses);
        arsort($otherClasses);

        $this->data = [
            'app' => $appClasses,
            'govuk' => $govukClasses,
            'moj' => $mojClasses,
            'other' => $otherClasses,
        ];
    }

    public function reset()
    {
        $this->data = [];
    }

    public function getName()
    {
        return 'css-class-collector';
    }

    public function getData()
    {
        return $this->data;
    }
}
