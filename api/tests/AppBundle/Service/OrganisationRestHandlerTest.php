<?php

namespace Tests\App\Service\RestHandler;

use App\Entity\Organisation;
use App\Entity\Repository\OrganisationRepository;
use App\Entity\Repository\UserRepository;
use App\Factory\OrganisationFactory;
use App\Service\RestHandler\OrganisationCreationException;
use App\Service\RestHandler\OrganisationRestHandler;
use App\Service\RestHandler\OrganisationUpdateException;
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

    /**
     * @var OrganisationRestHandler
     */
    private $sut;


    public function setUp(): void
    {
        $this->em = self::prophesize(EntityManager::class);
        $this->validator = self::prophesize(ValidatorInterface::class);
        $this->orgRepository = self::prophesize(OrganisationRepository::class);
        $this->userRepository = self::prophesize(UserRepository::class);
        $this->orgFactory = self::prophesize(OrganisationFactory::class);
        $this->sharedDomains = ['gmail.com'];
        $this->sut = new OrganisationRestHandler(
            $this->em->reveal(),
            $this->validator->reveal(),
            $this->orgRepository->reveal(),
            $this->userRepository->reveal(),
            $this->orgFactory->reveal(),
            $this->sharedDomains
        );
    }

    /**
     * @test
     */
    public function create_validOrgDetails()
    {
        $this->orgRepository->findOneBy(Argument::any())->willReturn(null);
        $this->validator->validate(Argument::any())->willReturn([]);
        $this->orgFactory->createFromEmailIdentifier(Argument::any(), Argument::any(), Argument::any())->willReturn(new Organisation());

        $this->em->persist(Argument::type(Organisation::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();


        self::assertInstanceOf(
            Organisation::class,
            $this->sut->create(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true])
        );
    }

    /**
     * @test
     * @dataProvider missingData
     * @param array $data
     */
    public function create_requiredDataMissing(array $data)
    {
        self::expectException(OrganisationCreationException::class);

        $this->sut->create($data);
    }

    public function missingData()
    {
        return [
            'Null name' =>                              [['name' => null, 'email_identifier' => 'abc.com', 'is_activated' => true]],
            'Null email_identifier' =>                  [['name' => 'ABC', 'email_identifier' => null, 'is_activated' => true]],
            'Null is_activated' =>                      [['email_identifier' => 'abc.com', 'is_activated' => null]],
            'Null name and is_activated' =>             [['name' =>null, 'email_identifier' => 'abc.com', 'is_activated' => null]],
            'Null email_identifier and is_activated' => [['name' => 'ABC', 'email_identifier' => null, 'is_activated' => null]],
            'Null name and email_identifier' =>         [['name' => null, 'email_identifier' => null, 'is_activated' => true]],
            'All null' =>                               [['name' => null, 'email_identifier' => null, 'is_activated' => null]],
            'Data missing' =>                           [['is_activated' => true]],
        ];
    }

    /**
     * @test
     */
    public function create_orgAlreadyExists()
    {
        $this->orgRepository->findOneBy(Argument::any())->willReturn(new Organisation());


        self::expectException(OrganisationCreationException::class);

        $this->sut->create(['name' => 'ABC', 'email_identifier' => 'gmail.com', 'is_activated' => true]);
    }

    /**
     * @test
     */
    public function create_emailIdentifierInSharedDomains()
    {
        $this->orgRepository->findOneBy(Argument::any())->willReturn(null);


        self::expectException(OrganisationCreationException::class);

        $this->sut->create(['name' => 'ABC', 'email_identifier' => 'gmail.com', 'is_activated' => true]);
    }

    /**
     * @test
     */
    public function create_orgValidationFails()
    {
        $this->orgRepository->findOneBy(Argument::any())->willReturn(null);
        $this->validator->validate(Argument::any())->willReturn(['an error']);
        $this->orgFactory->createFromEmailIdentifier(Argument::any(), Argument::any(), Argument::any())->willReturn(new Organisation());


        self::expectException(OrganisationCreationException::class);

        $this->sut->create(['name' => 'ABC', 'email_identifier' => 'abccom', 'is_activated' => true]);
    }

    /**
     * @test
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

        $updatedOrg = $this->sut->update(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], 1);

        self::assertEquals('abc.com', $updatedOrg->getEmailIdentifier());
        self::assertEquals('ABC', $updatedOrg->getName());
        self::assertTrue($updatedOrg->isActivated());
    }

    /**
     * @test
     * @dataProvider missingData
     */
    public function update_missingData($data)
    {
        self::expectException(OrganisationUpdateException::class);

        $this->sut->update($data, 1);
    }

    /**
     * @test
     */
    public function update_orgDoesNotExist()
    {
        $this->orgRepository->find(Argument::any())->willReturn(null);

        $updatedOrg = $this->sut->update(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], 1);

        self::assertNull($updatedOrg);
    }

    /**
     * @test
     */
    public function update_orgEmailIdentifierInUse()
    {
        $originalOrg = new Organisation();
        $originalOrg->setEmailIdentifier('cba@.com');
        $originalOrg->setname('Your Organisation');

        $this->orgRepository->find(Argument::any())->willReturn($originalOrg);
        $this->orgRepository->findOneBy(Argument::any())->willReturn($originalOrg);

        self::expectException(OrganisationUpdateException::class);

        $this->sut->update(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], 1);
    }

    /**
     * @test
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

        $this->sut->update(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], 1);
    }
}
