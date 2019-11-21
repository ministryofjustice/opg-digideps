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
     * @group acs
     */
    public function createFromFullEmail_determinesEmailIdentiferFromTheFullGivenEmail(
        string $fullEmail,
        string $expectedEmailIdentifier,
        string $expectedName,
        ?string $name
    )
    {
        $organisation = $this->factory->createFromFullEmail($name, $fullEmail, true);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals($expectedName, $organisation->getName());
        $this->assertEquals($expectedEmailIdentifier, $organisation->getEmailIdentifier());
        $this->assertTrue($organisation->isActivated());
    }

    /**
     * @return array
     */
    public function getEmailVariations(): array
    {
        return [
            ['fullEmail' => 'name@foo.com', 'expectedEmailIdentifier' => 'name@foo.com', 'expectedName' => 'Your Organisation', 'name' => null],
            ['fullEmail' => 'name@Bar.co.uk', 'expectedEmailIdentifier' => 'name@bar.co.uk', 'expectedName' => 'Your Organisation', 'name' => null],
            ['fullEmail' => 'name@private.com', 'expectedEmailIdentifier' => 'private.com', 'expectedName' => 'Your Organisation', 'name' => null],
            ['fullEmail' => 'main-contact@private.com', 'expectedEmailIdentifier' => 'private.com', 'expectedName' => 'Your Organisation', 'name' => null],
            ['fullEmail' => 'private.com', 'expectedEmailIdentifier' => 'private.com', 'expectedName' => 'Your Organisation', 'name' => null],
            ['fullEmail' => 'jbloggs@private.com', 'expectedEmailIdentifier' => 'private.com', 'expectedName' => 'Private Inc.' , 'name' => 'Private Inc.']


        ];
    }

    /**
     * @test
     * @group acs
     */
    public function createFromEmailIdentifier_createsOrganisationUsingGivenArgAsEmailIdentifier()
    {
        $organisation = $this->factory->createFromEmailIdentifier(null, 'Foo.Com', false);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals('Your Organisation', $organisation->getName());
        $this->assertEquals('foo.com', $organisation->getEmailIdentifier());
        $this->assertFalse($organisation->isActivated());
    }

    /**
     * @test
     * @dataProvider getInvalidEmailInputs
     * @param $emailIdentifier
     * @group acs
     */
    public function createFromFullEmail_throwsExceptionIfGivenBadData($emailIdentifier)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->factory->createFromFullEmail(null, $emailIdentifier);
    }


    /**
     * @test
     * @dataProvider getInvalidEmailIdentifierInputs
     * @param $emailIdentifier
     * @group acs
     */
    public function createFromEmailIdentifier_throwsExceptionIfGivenBadData($emailIdentifier)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->factory->createFromEmailIdentifier(null, $emailIdentifier);
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
