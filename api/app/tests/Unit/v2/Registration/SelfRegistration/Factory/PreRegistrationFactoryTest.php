<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\SelfRegistration\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\Test;
use DateTime;
use Exception;
use App\Entity\PreRegistration;
use App\Service\DateTimeProvider;
use App\v2\Registration\DTO\LayDeputyshipDto;
use App\v2\Registration\SelfRegistration\Factory\PreRegistrationCreationException;
use App\v2\Registration\SelfRegistration\Factory\PreRegistrationFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PreRegistrationFactoryTest extends TestCase
{
    private PreRegistrationFactory $factory;

    /** @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject */
    private MockObject $validator;

    /** @var DateTimeProvider|\PHPUnit_Framework_MockObject_MockObject */
    private MockObject $dateTimeProvider;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->dateTimeProvider = $this->createMock(DateTimeProvider::class);

        $this->factory = new PreRegistrationFactory($this->validator, $this->dateTimeProvider);
    }

    #[Test]
    public function throwsExceptionIfCreatesInvalidEntity(): void
    {
        $this->expectException(PreRegistrationCreationException::class);
        $constraintList = new ConstraintViolationList([
            new ConstraintViolation('Bad casenumber given', '', [], '', '', ''),
            new ConstraintViolation('Bad postcode given', '', [], '', '', ''),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($constraintList);

        $this->factory->createFromDto($this->buildLayDeputyshipDto());
    }

    #[Test]
    public function returnsAValidHydratedCasRecEntity(): void
    {
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        /** @var PreRegistration $result */
        $result = $this->factory->createFromDto($this->buildLayDeputyshipDto());

        $this->assertInstanceOf(PreRegistration::class, $result);
        $this->assertEquals('case', $result->getCaseNumber());
        $this->assertEquals('depnum', $result->getDeputyUid());
        $this->assertEquals('depsurname', $result->getDeputySurname());
        $this->assertEquals('clientsurname', $result->getClientLastname());
        $this->assertEquals('postcode', $result->getDeputyPostCode());
        $this->assertEquals('depaddress1', $result->getDeputyAddress1());
        $this->assertEquals('depaddress2', $result->getDeputyAddress2());
        $this->assertEquals('depaddress3', $result->getDeputyAddress3());
        $this->assertEquals('depaddress4', $result->getDeputyAddress4());
        $this->assertEquals('depaddress5', $result->getDeputyAddress5());
        $this->assertEquals('type', $result->getTypeOfReport());
        $this->assertEquals('pfa', $result->getOrderType());
        $this->assertEquals(true, $result->getNdr());
        $this->assertEquals('2011-06-14', $result->getOrderDate()->format('Y-m-d'));
        $this->assertEquals(false, $result->getIsCoDeputy());
    }

    /**
     * @throws Exception
     */
    private function buildLayDeputyshipDto(): LayDeputyshipDto
    {
        return (new LayDeputyshipDto())
            ->setCaseNumber('case')
            ->setDeputyUid('depnum')
            ->setDeputyFirstname('depfirstname')
            ->setDeputySurname('depsurname')
            ->setClientFirstname('clientfirstname')
            ->setClientSurname('clientsurname')
            ->setClientAddress1('clientaddress1')
            ->setClientAddress2('clientaddress2')
            ->setClientAddress3('clientaddress3')
            ->setClientAddress4('clientaddress4')
            ->setClientAddress5('clientaddress5')
            ->setClientPostcode('clientpostcode')
            ->setDeputyAddress1('depaddress1')
            ->setDeputyAddress2('depaddress2')
            ->setDeputyAddress3('depaddress3')
            ->setDeputyAddress4('depaddress4')
            ->setDeputyAddress5('depaddress5')
            ->setDeputyPostcode('postcode')
            ->setTypeOfReport('type')
            ->setIsNdrEnabled(true)
            ->setOrderType('pfa')
            ->setOrderDate(new DateTime('2011-06-14'))
            ->setIsCoDeputy(false)
            ->setHybrid('hybrid');
    }
}
