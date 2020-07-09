<?php declare(strict_types=1);

namespace AppBundle\Entity;


use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var DateTime
     */
    private $deputyshipStartDate;

    public function setup(): void
    {
        $this->deputyshipStartDate = new DateTime();
    }

    /**
     * @test
     */
    public function getDeputyShipStartDate()
    {
        $userDeputy = (new User())
            ->setEmail('bjork.gudmundsdottir@email.com')
            ->setRoleName('ROLE_LAY_DEPUTY');

        $courtOrderDeputy = (new CourtOrderDeputy())
            ->setEmail('bjork.gudmundsdottir@email.com');

        $courtOrder = (new CourtOrder())
            ->setDeputies(new ArrayCollection([$courtOrderDeputy]))
            ->setOrderDate($this->deputyshipStartDate);

        $client = (new Client())->setCourtOrders(new ArrayCollection([$courtOrder]));

        self::assertEquals($this->deputyshipStartDate, $client->getDeputyshipStartDate($userDeputy));
    }

    /**
     * @test
     */
    public function getDeputyShipStartDate_named_deputy()
    {
        $namedDeputy = (new NamedDeputy())
            ->setEmail1('emiliana.torrini@email.com');

        $courtOrderDeputy = (new CourtOrderDeputy())
            ->setEmail('emiliana.torrini@email.com');

        $courtOrder = (new CourtOrder())
            ->setDeputies(new ArrayCollection([$courtOrderDeputy]))
            ->setOrderDate($this->deputyshipStartDate);

        $client = (new Client())->setCourtOrders(new ArrayCollection([$courtOrder]));

        self::assertEquals($this->deputyshipStartDate, $client->getDeputyshipStartDate($namedDeputy));
    }

    /**
     * @test
     */
    public function getDeputyShipStartDate_no_court_order()
    {
        $userDeputyNoCourtOrder = (new User())
            ->setEmail('polly.jean.harvey@email.com')
            ->setRoleName('ROLE_LAY_DEPUTY');

        $client = new Client();

        self::assertEquals(null, $client->getDeputyshipStartDate($userDeputyNoCourtOrder));
    }

    /**
     * @test
     */
    public function getDeputyShipStartDate_mismatch_deputies()
    {
        $deputy = (new User())
            ->setEmail('bianca.cassidy@email.com')
            ->setRoleName('ROLE_LAY_DEPUTY');

        $courtOrderDeputy = (new CourtOrderDeputy())
            ->setEmail('sierra.cassidy@email.com');

        $courtOrder = (new CourtOrder())
            ->setDeputies(new ArrayCollection([$courtOrderDeputy]))
            ->setOrderDate($this->deputyshipStartDate);

        $client = (new Client())->setCourtOrders(new ArrayCollection([$courtOrder]));

        self::assertEquals(null, $client->getDeputyshipStartDate($deputy));
    }
}
