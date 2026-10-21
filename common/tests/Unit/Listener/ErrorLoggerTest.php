<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Common\Unit\Listener;

use OPG\Digideps\Common\Listener\ErrorLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ErrorLoggerTest extends TestCase
{
    public function testOnKernelException(): void
    {
        try {
            $this->method6();
        } catch (\Throwable $throwable) {
            /**
             * (Still need to teach phpstan that final classes can be mocked)
             * @phpstan-ignore-next-line
             */
            $exceptionEvent = $this->createStub(ExceptionEvent::class);
            /**
             * (Still need to teach phpstan that final classes can be mocked)
             * @phpstan-ignore-next-line
             */
            $exceptionEvent->method('getThrowable')->willReturn($throwable);
            $logger = $this->createMock(LoggerInterface::class);
            $logger->expects($this->once())->method('error')->with('{"class":"Exception","file":"ErrorLoggerTest.php","line":62,"message":"Very expected exception","previous":[],"trace":[{"file":"ErrorLoggerTest.php","class":"Tests\\\\OPG\\\\Digideps\\\\Common\\\\Unit\\\\Listener\\\\ErrorLoggerTest","function":"method1","line":57,"types":[]},{"file":"ErrorLoggerTest.php","class":"Tests\\\\OPG\\\\Digideps\\\\Common\\\\Unit\\\\Listener\\\\ErrorLoggerTest","function":"method2","line":52,"types":[]},{"file":"ErrorLoggerTest.php","class":"Tests\\\\OPG\\\\Digideps\\\\Common\\\\Unit\\\\Listener\\\\ErrorLoggerTest","function":"method3","line":47,"types":["int","null"]},{"file":"ErrorLoggerTest.php","class":"Tests\\\\OPG\\\\Digideps\\\\Common\\\\Unit\\\\Listener\\\\ErrorLoggerTest","function":"method4","line":42,"types":[]},{"file":"ErrorLoggerTest.php","class":"Tests\\\\OPG\\\\Digideps\\\\Common\\\\Unit\\\\Listener\\\\ErrorLoggerTest","function":"method5","line":37,"types":["null","string"]},{"file":"ErrorLoggerTest.php","class":"Tests\\\\OPG\\\\Digideps\\\\Common\\\\Unit\\\\Listener\\\\ErrorLoggerTest","function":"method6","line":17,"types":[]}]}');
            new ErrorLogger($logger, 6)->onKernelException($exceptionEvent);
        }
    }

    private function method6(): never
    {
        $this->method5(null, 'Hello World');
    }

    private function method5(?int $x, ?string $y): never
    {
        $this->method4();
    }

    private function method4(): never
    {
        $this->method3(5, null);
    }

    private function method3(?int $x, ?string $y): never
    {
        $this->method2();
    }

    private function method2(): never
    {
        $this->method1();
    }

    private function method1(): never
    {
        throw new \Exception("Very expected exception");
    }
}
