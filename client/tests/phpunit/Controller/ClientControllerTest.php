<?php declare(strict_types=1);

namespace AppBundle\Controller;


use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Logger;
use AppBundle\Service\Time\ClockInterface;
use AppBundle\Service\Time\DateTimeProvider;
use DateTime;

class ClientControllerTest extends AbstractControllerTestCase
{
    /**
     * @var DateTime
     */
    private $now, $orderStartDate;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockLoggedInUser(['ROLE_SUPER_ADMIN']);
        $this->now = new DateTime();
        $this->orderStartDate = new DateTime('-1 Day');
    }
    public function testDischargeConfirmAction(): void
    {
        $userDeputy = (new User())
            ->setFirstname('Bjork')
            ->setLastname('Gudmundsdottir')
            ->setEmail('user-deputy@email.com')
            ->setRoleName('ROLE_LAY_DEPUTY');

        $client = (new Client())
            ->setId(5)
            ->setCaseNumber('12345678')
            ->setCourtDate($this->orderStartDate);

        $clientWithUsers = (clone $client)->addUser($userDeputy);

        $this->restClient->get('v2/client/5', 'Client')->shouldBeCalled()->willReturn($client);
        $this->restClient->delete('client/5/delete')->shouldBeCalled();
        $this->restClient->get('client/5/details', 'Client')->shouldBeCalled()->willReturn($clientWithUsers);

        $this->injectProphecyService(DateTimeProvider::class, function($dateTimeProvider) {
            $dateTimeProvider->getDateTime()->willReturn($this->now);
        });

        $this->injectProphecyService(Logger::class, function($logger) {
            $expectedEvent = [
                'trigger' => 'ADMIN_BUTTON',
                'case_number' => '12345678',
                'discharged_by' => 'logged-in-user@email.com',
                'deputy_name' => 'Bjork Gudmundsdottir',
                'discharged_on' => $this->now->format(DateTime::ATOM),
                'deputyship_start_date' => $this->orderStartDate->format(DateTime::ATOM),
                'event' => AuditEvents::CLIENT_DISCHARGED,
                'type' => 'audit'
            ];

            $logger->notice('', $expectedEvent)->shouldBeCalled();
        });

        $crawler = $this->client->request('GET', "/admin/client/5/discharge");
        $dischargeLink = $crawler->selectLink('Discharge deputy')->link();

        $this->client->click($dischargeLink);
    }
}
