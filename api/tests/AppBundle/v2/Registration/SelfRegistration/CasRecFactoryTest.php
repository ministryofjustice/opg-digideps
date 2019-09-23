<?php

namespace Tests\AppBundle\v2\Registration\SelfRegistration;

use AppBundle\Entity\CasRec;
use AppBundle\Service\DateTimeProvider;
use AppBundle\v2\Registration\DTO\LayDeputyshipDto;
use AppBundle\v2\Registration\SelfRegistration\Factory\CasRecCreationException;
use AppBundle\v2\Registration\SelfRegistration\Factory\CasRecFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CasRecFactoryTest extends TestCase
{
    /** @var CasRecFactory */
    private $factory;

    /** @var ValidatorInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $validator;

    /** @var DateTimeProvider | \PHPUnit_Framework_MockObject_MockObject */
    private $dateTimeProvider;

    /** {@inheritDoc} */
    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->dateTimeProvider = $this->createMock(DateTimeProvider::class);

        $this->factory = new CasRecFactory($this->validator, $this->dateTimeProvider);
    }

    /**
     * @test
     */
    public function throwsExceptionIfCreatesInvalidEntity()
    {
        $this->expectException(\AppBundle\v2\Registration\SelfRegistration\Factory\CasRecCreationException::class);
        $constraintList = new ConstraintViolationList([
            new ConstraintViolation('Bad casenumber given', '', [], '', '', ''),
            new ConstraintViolation('Bad postcode given', '', [], '', '', '')
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($constraintList);

        $this->factory->createFromDto($this->buildLayDeputyshipDto());
    }

    /**
     * @test
     */
    public function returnsAValidHydratedCasRecEntity()
    {
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->dateTimeProvider
            ->expects($this->once())
            ->method('getDateTime')
            ->willReturn(new \DateTime('2010-01-03 12:03:23'));

        $result = $this->factory->createFromDto($this->buildLayDeputyshipDto());

        $this->assertInstanceOf(CasRec::class, $result);
        $this->assertEquals('case', $result->getCaseNumber());
        $this->assertEquals('depnum', $result->getDeputyNo());
        $this->assertEquals('depsurname', $result->getDeputySurname());
        $this->assertEquals('clientsurname', $result->getClientLastname());
        $this->assertEquals('postcode', $result->getDeputyPostCode());
        $this->assertEquals('type', $result->getTypeOfReport());
        $this->assertEquals('corref', $result->getCorref());
        $this->assertEquals(true, $result->getColumn('NDR'));
        $this->assertEquals('2010-01-03 12:03:23', $result->getUpdatedAt()->format('Y-m-d H:i:s'));
    }

    /**
     * @return LayDeputyshipDto
     */
    private function buildLayDeputyshipDto(): LayDeputyshipDto
    {
        return (new LayDeputyshipDto())
            ->setCaseNumber('case')
            ->setDeputyNumber('depnum')
            ->setDeputySurname('depsurname')
            ->setClientSurname('clientsurname')
            ->setDeputyPostcode('postcode')
            ->setTypeOfReport('type')
            ->setIsNdrEnabled(true)
            ->setCorref('corref');
    }
}
