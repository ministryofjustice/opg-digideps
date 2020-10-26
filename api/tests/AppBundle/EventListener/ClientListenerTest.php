<?php declare(strict_types=1);


namespace Tests\AppBundle\EventListener;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\EventListener\ClientListener;
use AppBundle\Service\Audit\AuditEvents;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use PHPUnit\Framework\TestCase;

class ClientListenerTest extends TestCase
{
    /** @test */
    public function preUpdate_client_is_deleted()
    {
        $now = new DateTime();
        $deputyshipStartDate = new DateTime('01-01-2020');

        $loggedInUser = (new User())
            ->setFirstname('Tucker')
            ->setLastname('Martine');

        $originalClient = (new Client())
            ->setCourtDate($deputyshipStartDate);

        $deputy = (new User())
            ->addClient($originalClient)
            ->setFirstname('Laura')
            ->setLastname('Veirs');

        $expectedAuditLog = [
            'trigger' => 'ADMIN_BUTTON',
            'case_number' => '12345678',
            'discharged_by' => 'Tucker Martine',
            'deputy_name' => 'Laura Veirs',
            'discharged_on' => $now->format(DateTime::ATOM),
            'deputyship_start_date' => $deputyshipStartDate->format(DateTime::ATOM),
            'event' => 'CLIENT_DISCHARGED',
            'type' => 'audit'
        ];

        $em = self::prophesize(EntityManager::class);

        $changeSet = ['deletedAt' => [null, '2020-10-26 17:30:35']];
        $preUpdateEvent = new PreUpdateEventArgs($originalClient, $em->reveal(), $changeSet);

        $sut = new ClientListener();
        $sut->preUpdate($originalClient, $preUpdateEvent);

        self::assertEquals($expectedAuditLog, $sut->logEvents[0], 'Expected the event in logEvents to match the expected event but it doesn\'t');
    }
}
