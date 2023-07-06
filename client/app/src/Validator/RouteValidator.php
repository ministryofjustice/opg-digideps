<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;

class RouteValidator
{
    public static function validateRoute(RouterInterface $router, string $path): bool
    {
        try {
            $router->match($path);
        } catch (ResourceNotFoundException $e) {
            return false;
        }

        return true;
    }
}
