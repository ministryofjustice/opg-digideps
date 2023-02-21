<?php

declare(strict_types=1);

namespace App\Service\Validator;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Router;

class RouteValidator
{
    public function validateRoute(Router $router, string $path): bool
    {
        try {
            $router->generate($path);
        } catch (RouteNotFoundException $e) {
            return false;
        }

        return true;
    }
}
