<?php

namespace AppBundle\Service\File\Scanner;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ScannerEndpointResolverTest extends TestCase
{
    /**
     * @test
     */
    public function throwsExceptionWhenUnableToResolve(): void
    {
        $this->expectException(\RuntimeException::class);

        $resolver = new ScannerEndpointResolver();
        $resolver->resolve($this->buildMockFileOfType('unsupported'));
    }

    /**
     * @test
     * @dataProvider inputs
     *
     * @param MockObject $file
     * @param string $expectedEndpoint
     */
    public function resolvesToScannerEndpoint(MockObject $file, string $expectedEndpoint): void
    {
        $resolver = new ScannerEndpointResolver();
        $endpoint = $resolver->resolve($file);
        $this->assertEquals($expectedEndpoint, $endpoint);
    }

    /**
     * @return array
     */
    public function inputs(): array
    {
        return [
            [$this->buildMockFileOfType('application/pdf'), ScannerEndpointResolver::PDF_ENDPOINT],
            [$this->buildMockFileOfType('image/png'), ScannerEndpointResolver::PNG_ENDPOINT],
            [$this->buildMockFileOfType('image/jpeg'), ScannerEndpointResolver::JPEG_ENDPOINT]
        ];
    }

    /**
     * @param string $mimeType
     * @return MockObject
     */
    private function buildMockFileOfType(string $mimeType): MockObject
    {
        $file = $this->getMockBuilder(UploadedFile::class)->disableOriginalConstructor()->getMock();
        $file
            ->expects($this->once())
            ->method('getMimeType')
            ->willReturn($mimeType);

        return $file;
    }
}
