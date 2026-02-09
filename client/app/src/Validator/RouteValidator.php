<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class RouteValidator
{
    public static function validateRoute(RouterInterface $router, string $path, RequestContext $context): bool
    {
        // reject suspicious paths
        if (str_starts_with($path, '../') || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return false;
        }

        $router->setContext($context);

        try {
            $router->match($path);
        } catch (ResourceNotFoundException) {
            return false;
        }

        return true;
    }
}
