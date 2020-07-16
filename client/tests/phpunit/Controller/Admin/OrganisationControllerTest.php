<?php declare(strict_types=1);

namespace AppBundle\Controller\Admin;


use AppBundle\Controller\AbstractControllerTestCase;
use AppBundle\Entity\Client;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Service\Logger;
use AppBundle\Service\Time\DateTimeProvider;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Prophecy\Argument;

class OrganisationControllerTest extends AbstractControllerTestCase
{
    /** @var DateTime */
    private $now;

    /** @var User */
    private $loggedInProfAdminUser;

    public function setUp(): void
    {
        parent::setUp();

        $organisation = (new Organisation())
            ->setId(7);

        $this->loggedInProfAdminUser = (new User())
            ->setOrganisations(new ArrayCollection([$organisation]))
            ->setEmail('logged-in-prof-admin@email.com')
            ->setRoleName(User::ROLE_PROF_ADMIN);

        $this->mockLoggedInUser([User::ROLE_PROF_ADMIN], $this->loggedInProfAdminUser);
        $this->now = new DateTime();
    }

    /**
     * @test
     */
    public function editUserAction_user_email_changed_audit_log_created(): void
    {
        $report = (new Report())
            ->setId(4);

        $client = (new Client())
            ->setFirstname('Deakin')
            ->setLastname('Dibb')
            ->setId(5)
            ->setDateOfBirth(new DateTime('6 January 1978'))
            ->setPhone('01213541234')
            ->setEmail('d.dibb@email.com')
            ->setAddress('Strawberry Jam Lane')
            ->setAddress2('California')
            ->setCounty('West Midlands')
            ->setPostcode('B31 1AB')
            ->setCurrentReport($report);

        $updatedClient = (clone $client)
            ->setEmail('deakin.dibb@email.com');

        $this->restClient->get(sprintf('client/%s', $client->getId()), Argument::cetera())->shouldBeCalled()->willReturn($client);
        $this->restClient->put('client/upsert', $updatedClient, ['pa-edit'])->shouldBeCalled();

        $this->injectProphecyService(DateTimeProvider::class, function($dateTimeProvider) {
            $dateTimeProvider->getDateTime()->willReturn($this->now);
        });

        $this->injectProphecyService(Logger::class, function($logger) use($client, $updatedClient) {
            $expectedEvent = [
                'trigger' => 'DEPUTY_USER_EDIT',
                'email_changed_from' => $client->getEmail(),
                'email_changed_to' => $updatedClient->getEmail(),
                'changed_on' => $this->now->format(DateTime::ATOM),
                'changed_by' => $this->loggedInProfAdminUser->getEmail(),
                'subject_full_name' => $updatedClient->getFullName(),
                'subject_role' => 'CLIENT',
                'event' => 'CLIENT_EMAIL_CHANGED',
                'type' => 'audit'
            ];

            $logger->notice('', $expectedEvent)->shouldBeCalled();
        });

        $crawler = $this->client->request('GET', sprintf("/org/client/%s/edit", $client->getId()));
        $button = $crawler->selectButton('Save client details');

        $this->client->submit($button->form(), [
            'org_client_edit[firstname]' => 'Deakin',
            'org_client_edit[lastname]' => 'Dibb',
            'org_client_edit[dateOfBirth][day]' => '6',
            'org_client_edit[dateOfBirth][month]' => '1',
            'org_client_edit[dateOfBirth][year]' => '1978',
            'org_client_edit[phone]' => '01213541234',
            'org_client_edit[email]' => 'deakin.dibb@email.com',
            'org_client_edit[address]' => 'Strawberry Jam Lane',
            'org_client_edit[address2]' => 'California',
            'org_client_edit[county]' => 'West Midlands',
            'org_client_edit[postcode]' => 'B31 1AB',
        ]);
    }
}
