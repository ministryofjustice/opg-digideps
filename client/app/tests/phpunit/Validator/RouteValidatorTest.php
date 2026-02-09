<?php

declare(strict_types=1);

namespace DigidepsTests\Validator;

use App\Validator\RouteValidator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class RouteValidatorTest extends KernelTestCase
{
    private RouterInterface $router;

    public function setUp(): void
    {
        self::bootKernel();

        /** @var RouterInterface $router */
        $router = self::$kernel->getContainer()->get('router');
        $this->router = $router;
    }

    /**
     * @dataProvider getPossibleRoutes
     */
    public function testValidateRoutes(string $path, bool $return): void
    {
        $context = (new RequestContext())->setMethod(Request::METHOD_GET);
        if ($return) {
            self::assertTrue(RouteValidator::validateRoute($this->router, $path, $context));
        } else {
            self::assertFalse(RouteValidator::validateRoute($this->router, $path, $context));
        }
    }

    public function getPossibleRoutes(): array
    {
        return [
            ['path' => '/deputyship-details', 'return' => true],
            ['path' => 'https://google.com/', 'return' => false],
            ['path' => '../../../deputyship-details', 'return' => false],
            ['path' => '/courtorder/12345678', 'return' => true],
        ];
    }
}
