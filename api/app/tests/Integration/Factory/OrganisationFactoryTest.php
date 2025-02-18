<?php

declare(strict_types=1);

namespace App\Tests\Integration\Factory;

use App\Entity\Organisation;
use App\Factory\OrganisationFactory;
use PHPUnit\Framework\TestCase;

class OrganisationFactoryTest extends TestCase
{
    /** @var OrganisationFactory */
    private $factory;

    /** @var string[] */
    private $sharedDomains;

    protected function setUp(): void
    {
        $this->sharedDomains = ['foo.com', 'bar.co.uk', 'example.com'];

        $this->factory = new OrganisationFactory($this->sharedDomains);
    }

    /**
     * @test
     *
     * @dataProvider getEmailVariations
     */
    public function createFromFullEmailDeterminesEmailIdentiferFromTheFullGivenEmail(
        string $fullEmail,
        string $expectedEmailIdentifier,
    ) {
        $organisation = $this->factory->createFromFullEmail('Foo Inc.', $fullEmail, true);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals('Foo Inc.', $organisation->getName());
        $this->assertEquals($expectedEmailIdentifier, $organisation->getEmailIdentifier());
        $this->assertTrue($organisation->isActivated());
    }

    public function getEmailVariations(): array
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

    /**
     * @test
     */
    public function createFromEmailIdentifierCreatesOrganisationUsingGivenArgAsEmailIdentifier()
    {
        $organisation = $this->factory->createFromEmailIdentifier('Foo Corp', 'Foo.Com', false);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals('Foo Corp', $organisation->getName());
        $this->assertEquals('foo.com', $organisation->getEmailIdentifier());
        $this->assertFalse($organisation->isActivated());
    }

    /**
     * @test
     *
     * @dataProvider getInvalidEmailInputs
     */
    public function createFromFullEmailThrowsExceptionIfGivenBadData($name, $emailIdentifier)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->factory->createFromFullEmail($name, $emailIdentifier);
    }

    /**
     * @test
     *
     * @dataProvider getInvalidEmailIdentifierInputs
     */
    public function createFromEmailIdentifierThrowsExceptionIfGivenBadData($name, $emailIdentifier)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->factory->createFromEmailIdentifier($name, $emailIdentifier);
    }

    public function getInvalidEmailInputs(): array
    {
        return [
            ['name' => '', 'emailIdentifier' => 'test.com'],
            ['name' => 'name', 'emailIdentifier' => ''],
            ['name' => 'name', 'emailIdentifier' => '@@private.com'],
        ];
    }

    public function getInvalidEmailIdentifierInputs(): array
    {
        return [
            ['name' => '', 'emailIdentifier' => 'f@test.com'],
            ['name' => 'name', 'emailIdentifier' => ''],
            ['name' => 'name', 'emailIdentifier' => '@@private.com'],
        ];
    }
}
