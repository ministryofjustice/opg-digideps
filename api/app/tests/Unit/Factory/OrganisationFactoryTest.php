<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use InvalidArgumentException;
use App\Entity\Organisation;
use App\Factory\OrganisationFactory;
use PHPUnit\Framework\TestCase;

final class OrganisationFactoryTest extends TestCase
{
    private OrganisationFactory $factory;

    /** @var string[] */
    private array $sharedDomains;

    protected function setUp(): void
    {
        $this->sharedDomains = ['foo.com', 'bar.co.uk', 'example.com'];

        $this->factory = new OrganisationFactory($this->sharedDomains);
    }

    #[DataProvider('getEmailVariations')]
    #[Test]
    public function createFromFullEmailDeterminesEmailIdentiferFromTheFullGivenEmail(
        string $fullEmail,
        string $expectedEmailIdentifier
    ): void {
        $organisation = $this->factory->createFromFullEmail('Foo Inc.', $fullEmail, true);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals('Foo Inc.', $organisation->getName());
        $this->assertEquals($expectedEmailIdentifier, $organisation->getEmailIdentifier());
        $this->assertTrue($organisation->isActivated());
    }

    public static function getEmailVariations(): array
    {
        return [
            ['fullEmail' => 'name@foo.com', 'expectedEmailIdentifier' => 'name@foo.com'],
            ['fullEmail' => 'name@Bar.co.uk', 'expectedEmailIdentifier' => 'name@bar.co.uk'],
            ['fullEmail' => 'name@private.com', 'expectedEmailIdentifier' => 'private.com'],
            ['fullEmail' => 'main-contact@private.com', 'expectedEmailIdentifier' => 'private.com'],
            ['fullEmail' => 'private.com', 'expectedEmailIdentifier' => 'private.com'],
            ['fullEmail' => 'jbloggs@private.com', 'expectedEmailIdentifier' => 'private.com'],
        ];
    }

    #[Test]
    public function createFromEmailIdentifierCreatesOrganisationUsingGivenArgAsEmailIdentifier(): void
    {
        $organisation = $this->factory->createFromEmailIdentifier('Foo Corp', 'Foo.Com', false);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals('Foo Corp', $organisation->getName());
        $this->assertEquals('foo.com', $organisation->getEmailIdentifier());
        $this->assertFalse($organisation->isActivated());
    }

    #[DataProvider('getInvalidEmailInputs')]
    #[Test]
    public function createFromFullEmailThrowsExceptionIfGivenBadData(string $name, string $emailIdentifier): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory->createFromFullEmail($name, $emailIdentifier);
    }

    #[DataProvider('getInvalidEmailIdentifierInputs')]
    #[Test]
    public function createFromEmailIdentifierThrowsExceptionIfGivenBadData(string $name, string $emailIdentifier): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory->createFromEmailIdentifier($name, $emailIdentifier);
    }

    public static function getInvalidEmailInputs(): array
    {
        return [
            ['name' => '', 'emailIdentifier' => 'test.com'],
            ['name' => 'name', 'emailIdentifier' => ''],
            ['name' => 'name', 'emailIdentifier' => '@@private.com'],
        ];
    }

    public static function getInvalidEmailIdentifierInputs(): array
    {
        return [
            ['name' => '', 'emailIdentifier' => 'f@test.com'],
            ['name' => 'name', 'emailIdentifier' => ''],
            ['name' => 'name', 'emailIdentifier' => '@@private.com'],
        ];
    }
}
