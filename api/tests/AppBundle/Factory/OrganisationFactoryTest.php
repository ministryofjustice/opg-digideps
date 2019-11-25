<?php declare(strict_types=1);

namespace Tests\AppBundle\Factory;

use AppBundle\Entity\Organisation;
use AppBundle\Factory\OrganisationFactory;
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
     * @dataProvider getEmailVariations
     * @param $fullEmail
     * @param $expectedEmailIdentifier
     */
    public function createFromFullEmail_determinesEmailIdentiferFromTheFullGivenEmail(
        string $fullEmail,
        string $expectedEmailIdentifier
    )
    {
        $organisation = $this->factory->createFromFullEmail('Foo Inc.', $fullEmail, true);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals('Foo Inc.', $organisation->getName());
        $this->assertEquals($expectedEmailIdentifier, $organisation->getEmailIdentifier());
        $this->assertTrue($organisation->isActivated());
    }

    /**
     * @return array
     */
    public function getEmailVariations(): array
    {
        return [
            ['fullEmail' => 'name@foo.com', 'expectedEmailIdentifier' => 'name@foo.com'],
            ['fullEmail' => 'name@Bar.co.uk', 'expectedEmailIdentifier' => 'name@bar.co.uk'],
            ['fullEmail' => 'name@private.com', 'expectedEmailIdentifier' => 'private.com'],
            ['fullEmail' => 'main-contact@private.com', 'expectedEmailIdentifier' => 'private.com'],
            ['fullEmail' => 'private.com', 'expectedEmailIdentifier' => 'private.com'],
            ['fullEmail' => 'jbloggs@private.com', 'expectedEmailIdentifier' => 'private.com']
        ];
    }

    /**
     * @test
     */
    public function createFromEmailIdentifier_createsOrganisationUsingGivenArgAsEmailIdentifier()
    {
        $organisation = $this->factory->createFromEmailIdentifier('Foo Corp', 'Foo.Com', false);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals('Foo Corp', $organisation->getName());
        $this->assertEquals('foo.com', $organisation->getEmailIdentifier());
        $this->assertFalse($organisation->isActivated());
    }

    /**
     * @test
     * @dataProvider getInvalidEmailInputs
     * @param $emailIdentifier
     */
    public function createFromFullEmail_throwsExceptionIfGivenBadData($emailIdentifier)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->factory->createFromFullEmail('Test Co.', $emailIdentifier);
    }


    /**
     * @test
     * @dataProvider getInvalidEmailIdentifierInputs
     * @param $emailIdentifier
     */
    public function createFromEmailIdentifier_throwsExceptionIfGivenBadData($emailIdentifier)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->factory->createFromEmailIdentifier('Test Co.', $emailIdentifier);
    }

    /**
     * @return array
     */
    public function getInvalidEmailInputs(): array
    {
        return [
            ['emailIdentifier' => ''],
            ['emailIdentifier' => '@@private.com'],
        ];
    }


    /**
     * @return array
     */
    public function getInvalidEmailIdentifierInputs(): array
    {
        return [
            ['emailIdentifier' => ''],
            ['emailIdentifier' => '@@private.com'],
        ];
    }

}
