<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;

class RouteValidatorTest extends KernelTestCase
{
    private RouterInterface $router;

    public function setUp(): void
    {
        $this->router = self::$kernel->getContainer()->get('router');
    }

    /**
     * @test
     * @dataProvider getPossibleRoutes
     */
    public function ValidateRoutes(string $path, bool $return)
    {
        if ($return) {
            self::assertTrue(RouteValidator::validateRoute($this->router, $path));
        } else {
            self::assertFalse(RouteValidator::validateRoute($this->router, $path));
        }
    }

    public function getPossibleRoutes()
    {
        return [
            ['path' => '/deputyship-details', 'return' => true],
            ['path' => 'https://google.com/', 'return' => false],
            ['path' => '../../../deputyship-details', 'return' => false],
            ['path' => '/report/62/overview', 'return' => false], // no user context, so will fail
        ];
    }
}
