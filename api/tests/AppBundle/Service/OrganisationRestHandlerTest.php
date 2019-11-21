<?php

namespace Tests\AppBundle\Service\RestHandler;

use AppBundle\Entity\Organisation;
use AppBundle\Entity\Repository\OrganisationRepository;
use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Factory\OrganisationFactory;
use AppBundle\Service\RestHandler\OrganisationCreationException;
use AppBundle\Service\RestHandler\OrganisationRestHandler;
use AppBundle\Service\RestHandler\OrganisationUpdateException;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrganisationRestHandlerTest extends TestCase
{
    /**
     * @var ObjectProphecy|EntityManager
     */
    private $em;

    /**
     * @var ObjectProphecy|ValidatorInterface
     */
    private $validator;

    /**
     * @var ObjectProphecy|OrganisationRepository
     */
    private $orgRepository;

    /**
     * @var ObjectProphecy|OrganisationFactory
     */
    private $orgFactory;

    /**
     * @var ObjectProphecy|UserRepository
     */
    private $userRepository;

    /**
     * @var array
     */
    private $sharedDomains;


    public function setUp(): void
    {
        $this->em = self::prophesize(EntityManager::class);
        $this->validator = self::prophesize(ValidatorInterface::class);
        $this->orgRepository = self::prophesize(OrganisationRepository::class);
        $this->userRepository = self::prophesize(UserRepository::class);
        $this->orgFactory = self::prophesize(OrganisationFactory::class);
        $this->sharedDomains = ['gmail.com'];
    }

    /**
     * @test
     * @dataProvider validData
     * @group acs
     */
    public function create_validOrgDetails(array $data)
    {
        $this->orgRepository->findOneBy(Argument::any())->willReturn(null);
        $this->validator->validate(Argument::any())->willReturn([]);
        $this->orgFactory->createFromEmailIdentifier(Argument::any(), Argument::any(), Argument::any())->willReturn(new Organisation());

        $this->em->persist(Argument::type(Organisation::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $sut = $this->generateSut();

        self::assertInstanceOf(Organisation::class, $sut->create($data));
    }

    public function validData()
    {
        return [
            'Org with name' => [['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], "ABC"],
            'Org with no name' => [['name' => null, 'email_identifier' => 'abc.com', 'is_activated' => false], "Your Organisation"],
        ];
    }

    /**
     * @test
     * @dataProvider missingData
     * @group acs
     * @param array $data
     */
    public function create_requiredDataMissing(array $data)
    {
        $sut = $this->generateSut();

        self::expectException(OrganisationCreationException::class);

        $sut->create($data);
    }

    public function missingData()
    {
        return [
            'Null email_identifier' => [['email_identifier' => null, 'is_activated' => true]],
            'Null is_activated' => [['email_identifier' => 'abc.com', 'is_activated' => null]],
            'Null email_identifier and is_activated' => [['email_identifier' => null, 'is_activated' => null]],
            'Data missing' => [['is_activated' => true]],
        ];
    }

    /**
     * @test
     * @group acs
     */
    public function create_orgAlreadyExists()
    {
        $this->orgRepository->findOneBy(Argument::any())->willReturn(new Organisation());

        $sut = $this->generateSut();

        self::expectException(OrganisationCreationException::class);

        $sut->create(['name' => 'ABC', 'email_identifier' => 'gmail.com', 'is_activated' => true]);
    }

    /**
     * @test
     * @group acs
     */
    public function create_emailIdentifierInSharedDomains()
    {
        $this->orgRepository->findOneBy(Argument::any())->willReturn(null);

        $sut = $this->generateSut();

        self::expectException(OrganisationCreationException::class);

        $sut->create(['name' => 'ABC', 'email_identifier' => 'gmail.com', 'is_activated' => true]);
    }

    /**
     * @test
     * @group acs
     */
    public function create_orgValidationFails()
    {
        $this->orgRepository->findOneBy(Argument::any())->willReturn(null);
        $this->validator->validate(Argument::any())->willReturn(['an error']);
        $this->orgFactory->createFromEmailIdentifier(Argument::any(), Argument::any(), Argument::any())->willReturn(new Organisation());

        $sut = $this->generateSut();

        self::expectException(OrganisationCreationException::class);

        $sut->create(['email_identifier' => 'abc.com', 'is_activated' => true]);
    }

    /**
     * @test
     * @group acs
     */
    public function update_validOrgDetails()
    {
        $originalOrg = new Organisation();
        $originalOrg->setEmailIdentifier('cba@.com');
        $originalOrg->setname('Your Organisation');

        $this->orgRepository->find(Argument::any())->willReturn($originalOrg);
        $this->orgRepository->findOneBy(Argument::any())->willReturn(null);
        $this->validator->validate(Argument::any())->willReturn([]);

        $this->em->persist(Argument::type(Organisation::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $sut = $this->generateSut();
        $updatedOrg = $sut->update(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], 1);

        self::assertEquals('abc.com', $updatedOrg->getEmailIdentifier());
        self::assertEquals('ABC', $updatedOrg->getName());
        self::assertTrue($updatedOrg->isActivated());
    }

    /**
     * @test
     * @dataProvider missingData
     * @group acs
     */
    public function update_missingData($data)
    {
        $sut = $this->generateSut();

        self::expectException(OrganisationUpdateException::class);

        $sut->update($data, 1);
    }

    /**
     * @test
     * @group acs
     */
    public function update_orgDoesNotExist()
    {
        $this->orgRepository->find(Argument::any())->willReturn(null);

        $sut = $this->generateSut();
        $updatedOrg = $sut->update(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], 1);

        self::assertNull($updatedOrg);
    }

    /**
     * @test
     * @group acs
     */
    public function update_orgEmailIdentifierInUse()
    {
        $originalOrg = new Organisation();
        $originalOrg->setEmailIdentifier('cba@.com');
        $originalOrg->setname('Your Organisation');

        $this->orgRepository->find(Argument::any())->willReturn($originalOrg);
        $this->orgRepository->findOneBy(Argument::any())->willReturn($originalOrg);

        self::expectException(OrganisationUpdateException::class);

        $sut = $this->generateSut();
        $sut->update(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], 1);
    }

    /**
     * @test
     * @group acs
     */
    public function update_orgValidationFails()
    {
        $originalOrg = new Organisation();
        $originalOrg->setEmailIdentifier('cba@.com');
        $originalOrg->setname('Your Organisation');

        $this->orgRepository->find(Argument::any())->willReturn($originalOrg);
        $this->orgRepository->findOneBy(Argument::any())->willReturn(null);
        $this->validator->validate(Argument::any())->willReturn(['an error']);

        self::expectException(OrganisationCreationException::class);

        $sut = $this->generateSut();
        $sut->update(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], 1);
    }

    private function generateSut()
    {
        return new OrganisationRestHandler(
            $this->em->reveal(),
            $this->validator->reveal(),
            $this->orgRepository->reveal(),
            $this->userRepository->reveal(),
            $this->orgFactory->reveal(),
            $this->sharedDomains
        );
    }
}
