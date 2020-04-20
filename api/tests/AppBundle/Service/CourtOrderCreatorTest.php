<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\CourtOrder;
use AppBundle\Entity\CourtOrderDeputyAddress;
use AppBundle\Entity\CourtOrderDeputy;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\CourtOrderRepository;
use AppBundle\Entity\User;
use AppBundle\Service\CourtOrderCreator;
use AppBundle\v2\DTO\CourtOrderDeputyAddressDto;
use AppBundle\v2\DTO\CourtOrderDeputyDto;
use AppBundle\v2\DTO\CourtOrderDto;
use AppBundle\v2\Factory\CourtOrderDeputyFactory;
use AppBundle\v2\Factory\CourtOrderFactory;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CourtOrderCreatorTest extends WebTestCase
{
    public function testCreateCourtOrder()
    {
        $orderDto = $this->generateOrderDto();
        $report = $this->generateReport();

        $courtOrder = (new CourtOrder())->setCaseNumber($orderDto->getCaseNumber());
        $courtOrderFactory = self::prophesize(CourtOrderFactory::class);
        $courtOrderFactory->create($orderDto, $report->getClient(), $report)->shouldBeCalled()->willReturn($courtOrder);

        $sut = new CourtOrderCreator(
            self::prophesize(EntityManagerInterface::class)->reveal(),
            self::prophesize(CourtOrderRepository::class)->reveal(),
            $courtOrderFactory->reveal(),
            self::prophesize(CourtOrderDeputyFactory::class)->reveal(),
        );

        $sutCourtOrder = $sut->upsertCourtOrder($orderDto, $report);

        self::assertEquals($courtOrder, $sutCourtOrder);
    }

    public function testUpdateCourtOrder()
    {
        $orderDto = $this->generateOrderDto();
        $report = $this->generateReport($orderDto->getCaseNumber());

        $courtOrder = (new CourtOrder());
        $courtOrderRepository = self::prophesize(CourtOrderRepository::class);
        $courtOrderRepository->findOneBy([
            'caseNumber' => $orderDto->getCaseNumber(),
            'type' => $orderDto->getType()
        ])->shouldBeCalled()->willReturn($courtOrder);

        $courtOrderFactory = self::prophesize(CourtOrderFactory::class);
        $courtOrderFactory->create(Argument::any())->shouldNotBeCalled()->willReturn();

        $sut = new CourtOrderCreator(
            self::prophesize(EntityManagerInterface::class)->reveal(),
            $courtOrderRepository->reveal(),
            $courtOrderFactory->reveal(),
            self::prophesize(CourtOrderDeputyFactory::class)->reveal(),
        );

        $sut->upsertCourtOrder($orderDto, $report);

        self::assertEquals($orderDto->getOrderDate(), $courtOrder->getOrderDate());
    }

    public function testCreateDeputy()
    {
        $deputyDto = $this->generateDeputyDto();

        $courtOrder = (new CourtOrder());

        $organisation = self::prophesize(Organisation::class);
        $organisation->getId()->shouldBeCalled()->willReturn(28);

        $deputy = (new CourtOrderDeputy())
            ->setDeputyNumber($deputyDto->getDeputyNumber());

        $factory = self::prophesize(CourtOrderDeputyFactory::class);
        $factory->create($deputyDto, $courtOrder)->shouldBeCalled()->willReturn($deputy);

        $sut = new CourtOrderCreator(
            self::prophesize(EntityManagerInterface::class)->reveal(),
            self::prophesize(CourtOrderRepository::class)->reveal(),
            self::prophesize(CourtOrderFactory::class)->reveal(),
            $factory->reveal(),
        );

        $sutDeputy = $sut->upsertCourtOrderDeputy($deputyDto, $courtOrder, $organisation->reveal());

        self::assertEquals($deputy, $sutDeputy);
        self::assertEquals($organisation->reveal()->getId(), $sutDeputy->getOrganisation()->getId());
    }

    public function testUpdateDeputyIfSameDeputyNumber()
    {
        $deputyDto = $this->generateDeputyDto()
            ->setFirstname('Matteas');

        $deputyDto->getAddress()->setAddressLine1('1 Scotland Street');

        $organisation = self::prophesize(Organisation::class);
        $organisation->getId()->shouldBeCalled()->willReturn(28);

        $deputy = (new CourtOrderDeputy())
            ->setDeputyNumber($deputyDto->getDeputyNumber())
            ->setFirstname('William')
            ->addAddress(new CourtOrderDeputyAddress());

        $courtOrder = (new CourtOrder())
            ->addDeputy($deputy);

        $sut = new CourtOrderCreator(
            self::prophesize(EntityManagerInterface::class)->reveal(),
            self::prophesize(CourtOrderRepository::class)->reveal(),
            self::prophesize(CourtOrderFactory::class)->reveal(),
            self::prophesize(CourtOrderDeputyFactory::class)->reveal(),
        );

        $sutDeputy = $sut->upsertCourtOrderDeputy($deputyDto, $courtOrder, $organisation->reveal());

        self::assertEquals($deputy->getDeputyNumber(), $sutDeputy->getDeputyNumber());
        self::assertEquals('Matteas', $sutDeputy->getFirstname());
        self::assertEquals('1 Scotland Street', $sutDeputy->getAddresses()[0]->getAddressLine1());
        self::assertEquals($organisation->reveal()->getId(), $sutDeputy->getOrganisation()->getId());
    }

    public function testReplaceDeputyIfDifferentDeputyNumber()
    {
        $deputyDto = $this->generateDeputyDto()
            ->setFirstname('Matteas');

        $organisation = self::prophesize(Organisation::class);

        $deputy = (new CourtOrderDeputy())
            ->setDeputyNumber('3904')
            ->setFirstname('William');

        $report = $this->generateReport();
        $courtOrder = (new CourtOrder())
            ->setCaseNumber('24892382')
            ->setType(CourtOrder::SUBTYPE_HW)
            ->setSupervisionLevel(CourtOrder::LEVEL_GENERAL)
            ->setOrderDate(DateTime::createFromFormat('Y-m-d', '2015-11-25'))
            ->setClient($report->getClient())
            ->addReport($report)
            ->addDeputy($deputy);

        $em = self::prophesize(EntityManagerInterface::class);
        $em->flush()->shouldBeCalled()->willReturn();
        $em->remove($courtOrder)->shouldBeCalled()->willReturn();
        $em->persist(Argument::type(CourtOrder::class))->shouldBeCalled()->willReturn();
        $em->persist(Argument::type(CourtOrderDeputy::class))->shouldBeCalled()->willReturn();

        $courtOrderFactory = self::prophesize(CourtOrderFactory::class);
        $courtOrderFactory->create(
            Argument::allOf(
                Argument::type(CourtOrderDto::class),
                Argument::which('getCaseNumber', '24892382'),
                Argument::which('getType', CourtOrder::SUBTYPE_HW),
                Argument::which('getSupervisionLevel', CourtOrder::LEVEL_GENERAL)
            ),
            $report->getClient(),
            $report
        )
            ->shouldBeCalled()
            ->willReturn(new CourtOrder());

        $deputyFactory = self::prophesize(CourtOrderDeputyFactory::class);
        $deputyFactory->create($deputyDto, Argument::type(CourtOrder::class))->shouldBeCalled()->willReturn(new CourtOrderDeputy());

        $sut = new CourtOrderCreator(
            $em->reveal(),
            self::prophesize(CourtOrderRepository::class)->reveal(),
            $courtOrderFactory->reveal(),
            $deputyFactory->reveal(),
        );

        $sutDeputy = $sut->upsertCourtOrderDeputy($deputyDto, $courtOrder, $organisation->reveal());

        self::assertEquals(CourtOrderDeputy::class, get_class($sutDeputy));
    }

    public function testBalkIfLayOnOrder()
    {
        $layDeputy = self::prophesize(CourtOrderDeputy::class);
        $layDeputy->getUser()->shouldBeCalled()->willReturn(new User());

        $courtOrder = self::prophesize(CourtOrder::class);
        $courtOrder->getDeputies()->shouldBeCalled()->willReturn(new ArrayCollection([$layDeputy->reveal()]));

        $sut = new CourtOrderCreator(
            self::prophesize(EntityManagerInterface::class)->reveal(),
            self::prophesize(CourtOrderRepository::class)->reveal(),
            self::prophesize(CourtOrderFactory::class)->reveal(),
            self::prophesize(CourtOrderDeputyFactory::class)->reveal(),
        );

        $this->expectException('RuntimeException', 'Case number already used by lay deputy');

        $sut->upsertCourtOrderDeputy(new CourtOrderDeputyDto(), $courtOrder->reveal(), new Organisation());
    }

    private function generateOrderDto()
    {
        return (new CourtOrderDto())
            ->setCaseNumber(strval(mt_rand(10000000, 99999999)))
            ->setType(CourtOrder::SUBTYPE_HW)
            ->setSupervisionLevel(CourtOrder::LEVEL_GENERAL)
            ->setOrderDate(DateTime::createFromFormat('Y-m-d', '2016-05-04'));
    }

    private function generateDeputyDto()
    {
        $address = new CourtOrderDeputyAddressDto();

        return (new CourtOrderDeputyDto())
            ->setDeputyNumber(strval(mt_rand(10000000, 99999999)))
            ->setFirstname('Lupe')
            ->setSurname('Stanger')
            ->setEmail('l.stanger@gmail.example')
            ->setAddress($address);
    }

    private function generateReport($caseNumber = '00000000')
    {
        return new Report(
            (new Client())->setCaseNumber($caseNumber),
            Report::TYPE_102,
            DateTime::createFromFormat('Y-m-d', '2016-05-04'),
            DateTime::createFromFormat('Y-m-d', '2017-05-03')
        );
    }
}
