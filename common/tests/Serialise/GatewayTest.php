<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Common\Serialise;

use OPG\Digideps\Common\Serialise\Gateway;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Button;
use Symfony\Component\Form\ButtonBuilder;

class GatewayTest extends TestCase
{
    public function testSerialise(): void
    {
        $object = new \DateTimeImmutable();
        $this->assertSame(\serialize($object), new Gateway()->serialise($object));
    }

    public function testDeserialiseBuildIn(): void
    {
        $serialised = 'O:8:"DateTime":3:{s:4:"date";s:26:"2026-10-14 15:52:58.044330";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}';
        $value = new Gateway()->deserialise('DateTime', $serialised);
        $this->assertInstanceOf('DateTime', $value);
    }

    public function testDeserialiseBad(): void
    {
        $serialised = 'O:34:"JMS\Serializer\Handler\EnumHandler":0:{}';
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageIs('Attempting to unserialise untrusted class JMS\Serializer\Handler\EnumHandler');
        new Gateway()->deserialise('JMS\Serializer\Handler\EnumHandler', $serialised);
    }

    public function testDeserialiseBadNested(): void
    {
        $serialised = "O:45:\"OPG\\Digideps\\Common\\Validating\\ValidatingForm\":1:{s:51:\"\0OPG\\Digideps\\Common\\Validating\\ValidatingForm\0data\";O:29:\"Symfony\\Component\\Form\\Button\":3:{s:37:\"\0Symfony\\Component\\Form\\Button\0parent\";N;s:37:\"\0Symfony\\Component\\Form\\Button\0config\";O:36:\"Symfony\\Component\\Form\\ButtonBuilder\":5:{s:9:\"\0*\0locked\";b:0;s:46:\"\0Symfony\\Component\\Form\\ButtonBuilder\0disabled\";b:0;s:42:\"\0Symfony\\Component\\Form\\ButtonBuilder\0name\";s:1:\"N\";s:48:\"\0Symfony\\Component\\Form\\ButtonBuilder\0attributes\";a:0:{}s:45:\"\0Symfony\\Component\\Form\\ButtonBuilder\0options\";a:0:{}}s:40:\"\0Symfony\\Component\\Form\\Button\0submitted\";b:0;}}";
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageIs('Attempting to unserialise untrusted class Symfony\Bridge\PhpUnit\DeprecationErrorHandler\Configuration');
        new Gateway()->deserialise('OPG\Digideps\Common\Validating\ValidatingForm', $serialised);
    }

    public function testDeserialiseCommon(): void
    {
        $serialised = "O:46:\"OPG\\Digideps\\Common\\Validating\\ValidatingArray\":1:{s:52:\"\0OPG\\Digideps\\Common\\Validating\\ValidatingArray\0data\";a:0:{}}";
        $value = new Gateway()->deserialise('OPG\Digideps\Common\Validating\ValidatingArray', $serialised);
        $this->assertInstanceOf('OPG\Digideps\Common\Validating\ValidatingArray', $value);
    }

    public function testDeserialiseAlreadyLoaded(): void
    {
        $_ = new Button(new ButtonBuilder('X'));
        $serialised = "O:45:\"OPG\\Digideps\\Common\\Validating\\ValidatingForm\":1:{s:51:\"\0OPG\\Digideps\\Common\\Validating\\ValidatingForm\0data\";O:29:\"Symfony\\Component\\Form\\Button\":3:{s:37:\"\0Symfony\\Component\\Form\\Button\0parent\";N;s:37:\"\0Symfony\\Component\\Form\\Button\0config\";O:36:\"Symfony\\Component\\Form\\ButtonBuilder\":5:{s:9:\"\0*\0locked\";b:0;s:46:\"\0Symfony\\Component\\Form\\ButtonBuilder\0disabled\";b:0;s:42:\"\0Symfony\\Component\\Form\\ButtonBuilder\0name\";s:1:\"N\";s:48:\"\0Symfony\\Component\\Form\\ButtonBuilder\0attributes\";a:0:{}s:45:\"\0Symfony\\Component\\Form\\ButtonBuilder\0options\";a:0:{}}s:40:\"\0Symfony\\Component\\Form\\Button\0submitted\";b:0;}}";
        $value = new Gateway()->deserialise('OPG\Digideps\Common\Validating\ValidatingForm', $serialised);
        $this->assertInstanceOf('OPG\Digideps\Common\Validating\ValidatingForm', $value);
    }
}
