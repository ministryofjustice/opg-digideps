<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service;

use Doctrine\ORM\EntityManager;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Factory\OrganisationFactory;
use OPG\Digideps\Backend\Repository\OrganisationRepository;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\Service\RestHandler\OrganisationCreationException;
use OPG\Digideps\Backend\Service\RestHandler\OrganisationRestHandler;
use OPG\Digideps\Backend\Service\RestHandler\OrganisationUpdateException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class OrganisationRestHandlerTest extends TestCase
{
    private EntityManager&MockObject $em;
    private ValidatorInterface&MockObject $validator;
    private OrganisationRepository&MockObject $orgRepository;
    private OrganisationFactory&MockObject $orgFactory;
    private OrganisationRestHandler $sut;

    public function setUp(): void
    {
        $this->em = self::createMock(EntityManager::class);
        $this->validator = self::createMock(ValidatorInterface::class);
        $this->orgRepository = self::createMock(OrganisationRepository::class);
        $userRepository = self::createMock(UserRepository::class);
        $this->orgFactory = self::createMock(OrganisationFactory::class);
        $sharedDomains = ['gmail.com'];
        $this->sut = new OrganisationRestHandler(
            $this->em,
            $this->validator,
            $this->orgRepository,
            $userRepository,
            $this->orgFactory,
            $sharedDomains
        );
    }

    #[Test]
    public function createValidOrgDetails(): void
    {
        $this->orgRepository->expects(self::once())->method('findOneBy')->willReturn(null);

        $this->validator->expects(self::once())->method('validate')->willReturn(new ConstraintViolationList());

        $this->orgFactory->expects(self::once())->method('createFromEmailIdentifier')->willReturn(new Organisation('foo', 'example.org'));

        $this->em->expects(self::once())->method('persist')->with(self::isInstanceOf(Organisation::class));

        $this->em->expects(self::once())->method('flush');

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
        $this->orgRepository->expects(self::once())->method('findOneBy')->willReturn(new Organisation('ABC', 'gmail.com', true));

        self::expectException(OrganisationCreationException::class);

        $this->sut->create(['name' => 'ABC', 'email_identifier' => 'gmail.com', 'is_activated' => true]);
    }

    #[Test]
    public function createEmailIdentifierInSharedDomains(): void
    {
        $this->orgRepository->expects(self::once())->method('findOneBy')->willReturn(null);

        self::expectException(OrganisationCreationException::class);

        $this->sut->create(['name' => 'ABC', 'email_identifier' => 'gmail.com', 'is_activated' => true]);
    }

    #[Test]
    public function createOrgValidationFails(): void
    {
        $this->orgRepository->expects(self::once())->method('findOneBy')->willReturn(null);

        $this->validator->expects(self::once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation('an error', null, [], null, null, null)
            ]));

        $this->orgFactory->expects(self::once())->method('createFromEmailIdentifier')->willReturn(new Organisation('bar', 'example.org'));

        self::expectException(OrganisationCreationException::class);

        $this->sut->create(['name' => 'ABC', 'email_identifier' => 'abccom', 'is_activated' => true]);
    }

    #[Test]
    public function updateValidOrgDetails(): void
    {
        $originalOrg = new Organisation('Your Organisation', 'cba@.com');

        $this->orgRepository->expects(self::once())->method('find')->willReturn($originalOrg);
        $this->orgRepository->expects(self::once())->method('findOneBy')->willReturn(null);

        $this->validator->expects(self::once())->method('validate')->willReturn(new ConstraintViolationList());

        $this->em->expects(self::once())->method('persist')->with(self::isInstanceOf(Organisation::class));

        $this->em->expects(self::once())->method('flush');

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
        $this->orgRepository->expects(self::once())->method('find')->willReturn(null);

        $updatedOrg = $this->sut->update(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], 1);

        self::assertNull($updatedOrg);
    }

    #[Test]
    public function updateOrgEmailIdentifierInUse(): void
    {
        $originalOrg = new Organisation('Your Organisation', 'cba@.com');

        $this->orgRepository->expects(self::once())->method('find')->willReturn($originalOrg);

        $this->orgRepository->expects(self::once())->method('findOneBy')->willReturn($originalOrg);

        self::expectException(OrganisationUpdateException::class);

        $this->sut->update(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], 1);
    }

    #[Test]
    public function updateOrgValidationFails(): void
    {
        $originalOrg = new Organisation('Your Organisation', 'cba@.com');

        $this->orgRepository->expects(self::once())->method('find')->willReturn($originalOrg);
        $this->orgRepository->expects(self::once())->method('findOneBy')->willReturn(null);

        $this->validator->expects(self::once())->method('validate')->willReturn(new ConstraintViolationList([
            new ConstraintViolation('an error', null, [], null, null, null)
        ]));

        self::expectException(OrganisationCreationException::class);

        $this->sut->update(['name' => 'ABC', 'email_identifier' => 'abc.com', 'is_activated' => true], 1);
    }
}
