<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\RestHandler;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Entity\Organisation;
use App\Factory\OrganisationFactory;
use App\Repository\OrganisationRepository;
use App\Repository\UserRepository;
use App\Service\RestHandler\OrganisationCreationException;
use App\Service\RestHandler\OrganisationRestHandler;
use App\Service\RestHandler\OrganisationUpdateException;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class OrganisationRestHandlerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|EntityManager $em;
    private ObjectProphecy|ValidatorInterface $validator;
    private ObjectProphecy|OrganisationRepository $orgRepository;
    private ObjectProphecy|OrganisationFactory $orgFactory;
    private ObjectProphecy|UserRepository $userRepository;
    private array $sharedDomains;
    private OrganisationRestHandler $sut;

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

    #[Test]
    public function createValidOrgDetails(): void
    {
        $this->orgRepository->findOneBy(Argument::any())->willReturn(null);
        $this->validator->validate(Argument::any())->willReturn(new ConstraintViolationList());
        $this->orgFactory->createFromEmailIdentifier(Argument::any(), Argument::any(), Argument::any())->willReturn(new Organisation());

        $this->em->persist(Argument::type(Organisation::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        self::assertInstanceOf(
            Organisation::class,
            $this->sut->create(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true])
        );
    }

    #[DataProvider('missingData')]
    #[Test]
    public function createRequiredDataMissing(array $data): void
    {
        self::expectException(OrganisationCreationException::class);

        $this->sut->create($data);
    }

    public static function missingData(): array
    {
        return [
            'Null name' => [['name' => null, 'email_identifier' => 'abc.com', 'is_activated' => true]],
            'Null email_identifier' => [['name' => 'ABC', 'email_identifier' => null, 'is_activated' => true]],
            'Null is_activated' => [['email_identifier' => 'abc.com', 'is_activated' => null]],
            'Null name and is_activated' => [['name' => null, 'email_identifier' => 'abc.com', 'is_activated' => null]],
            'Null email_identifier and is_activated' => [['name' => 'ABC', 'email_identifier' => null, 'is_activated' => null]],
            'Null name and email_identifier' => [['name' => null, 'email_identifier' => null, 'is_activated' => true]],
            'All null' => [['name' => null, 'email_identifier' => null, 'is_activated' => null]],
            'Data missing' => [['is_activated' => true]],
        ];
    }

    #[Test]
    public function createOrgAlreadyExists(): void
    {
        $this->orgRepository->findOneBy(Argument::any())->willReturn(new Organisation());

        self::expectException(OrganisationCreationException::class);

        $this->sut->create(['name' => 'ABC', 'email_identifier' => 'gmail.com', 'is_activated' => true]);
    }

    #[Test]
    public function createEmailIdentifierInSharedDomains(): void
    {
        $this->orgRepository->findOneBy(Argument::any())->willReturn(null);

        self::expectException(OrganisationCreationException::class);

        $this->sut->create(['name' => 'ABC', 'email_identifier' => 'gmail.com', 'is_activated' => true]);
    }

    #[Test]
    public function createOrgValidationFails(): void
    {
        $this->orgRepository->findOneBy(Argument::any())->willReturn(null);
        $this->validator->validate(Argument::any())->willReturn(new ConstraintViolationList([
            new ConstraintViolation('an error', null, [], null, null, null)
        ]));
        $this->orgFactory->createFromEmailIdentifier(Argument::any(), Argument::any(), Argument::any())->willReturn(new Organisation());

        self::expectException(OrganisationCreationException::class);

        $this->sut->create(['name' => 'ABC', 'email_identifier' => 'abccom', 'is_activated' => true]);
    }

    #[Test]
    public function updateValidOrgDetails(): void
    {
        $originalOrg = new Organisation();
        $originalOrg->setEmailIdentifier('cba@.com');
        $originalOrg->setname('Your Organisation');

        $this->orgRepository->find(Argument::any())->willReturn($originalOrg);
        $this->orgRepository->findOneBy(Argument::any())->willReturn(null);
        $this->validator->validate(Argument::any())->willReturn(new ConstraintViolationList());

        $this->em->persist(Argument::type(Organisation::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $updatedOrg = $this->sut->update(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], 1);

        self::assertEquals('abc.com', $updatedOrg->getEmailIdentifier());
        self::assertEquals('ABC', $updatedOrg->getName());
        self::assertTrue($updatedOrg->isActivated());
    }

    #[DataProvider('missingData')]
    #[Test]
    public function updateMissingData(array $data): void
    {
        self::expectException(OrganisationUpdateException::class);

        $this->sut->update($data, 1);
    }

    #[Test]
    public function updateOrgDoesNotExist(): void
    {
        $this->orgRepository->find(Argument::any())->willReturn(null);

        $updatedOrg = $this->sut->update(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], 1);

        self::assertNull($updatedOrg);
    }

    #[Test]
    public function updateOrgEmailIdentifierInUse(): void
    {
        $originalOrg = new Organisation();
        $originalOrg->setEmailIdentifier('cba@.com');
        $originalOrg->setname('Your Organisation');

        $this->orgRepository->find(Argument::any())->willReturn($originalOrg);
        $this->orgRepository->findOneBy(Argument::any())->willReturn($originalOrg);

        self::expectException(OrganisationUpdateException::class);

        $this->sut->update(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], 1);
    }

    #[Test]
    public function updateOrgValidationFails(): void
    {
        $originalOrg = new Organisation();
        $originalOrg->setEmailIdentifier('cba@.com');
        $originalOrg->setname('Your Organisation');

        $this->orgRepository->find(Argument::any())->willReturn($originalOrg);
        $this->orgRepository->findOneBy(Argument::any())->willReturn(null);
        $this->validator->validate(Argument::any())->willReturn(new ConstraintViolationList([
            new ConstraintViolation('an error', null, [], null, null, null)
        ]));

        self::expectException(OrganisationCreationException::class);

        $this->sut->update(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], 1);
    }
}
