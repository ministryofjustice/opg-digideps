<?php declare(strict_types=1);

namespace AppBundle\Controller\Org;


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

class IndexControllerTest extends AbstractControllerTestCase
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
     * @dataProvider emailAddressProvider
     */
    public function clientEditAction_client_email_changed_audit_log_created(?string $oldEmail, ?string $newEmail, string $message): void
    {
        $report = (new Report())
            ->setId(4);

        $client = (new Client())
            ->setFirstname('Deakin')
            ->setLastname('Dibb')
            ->setId(5)
            ->setDateOfBirth(new DateTime('6 January 1978'))
            ->setPhone('01213541234')
            ->setEmail($oldEmail)
            ->setAddress('Strawberry Jam Lane')
            ->setAddress2('California')
            ->setCounty('West Midlands')
            ->setPostcode('B31 1AB')
            ->setCurrentReport($report);

        $updatedClient = (clone $client)
            ->setEmail($newEmail);

        $this->restClient->get(sprintf('client/%s', $client->getId()), Argument::cetera())->shouldBeCalled()->willReturn($client);
        $this->restClient->put('client/upsert', $updatedClient, ['pa-edit'])->shouldBeCalled();

        $this->injectProphecyService(DateTimeProvider::class, function($dateTimeProvider) {
            $dateTimeProvider->getDateTime()->willReturn($this->now);
        });

        $this->injectProphecyService(Logger::class, function($logger) use($oldEmail, $newEmail, $message, $updatedClient) {
            $expectedEvent = [
                'trigger' => 'DEPUTY_USER_EDIT',
                'email_changed_from' => $oldEmail,
                'email_changed_to' => $newEmail,
                'changed_on' => $this->now->format(DateTime::ATOM),
                'changed_by' => $this->loggedInProfAdminUser->getEmail(),
                'subject_full_name' => $updatedClient->getFullName(),
                'subject_role' => 'CLIENT',
                'event' => 'CLIENT_EMAIL_CHANGED',
                'type' => 'audit'
            ];

            $logger->notice($message, $expectedEvent)->shouldBeCalled();
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
            'org_client_edit[email]' => $newEmail,
            'org_client_edit[address]' => 'Strawberry Jam Lane',
            'org_client_edit[address2]' => 'California',
            'org_client_edit[county]' => 'West Midlands',
            'org_client_edit[postcode]' => 'B31 1AB',
        ]);
    }

    public function emailAddressProvider()
    {
        return [
            'Email changed' => ['d.dibb@email.com', 'deakin.dibb@email.com', ''],
            'Email added' => [null, 'deakin.dibb@email.com', ''],
            'Email removed' => ['d.dibb@email.com', null, 'Client email address removed'],
        ];
    }

    /**
     * @test
     */
    public function clientEditAction_not_logged_when_email_remains_the_same(): void
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
            ->setAddress2('New York');

        // Super strange bug requires calling this here to ensure its populated on the entity during put assertion
        $updatedClient->getFullName();

        $this->restClient->get(sprintf('client/%s', $client->getId()), Argument::cetera())->shouldBeCalled()->willReturn($client);
        $this->restClient->put('client/upsert', $updatedClient, ['pa-edit'])->shouldBeCalled();

        $this->injectProphecyService(Logger::class, function($logger) {
            $logger->notice(Argument::cetera())->shouldNotBeCalled();
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
            'org_client_edit[email]' => 'd.dibb@email.com',
            'org_client_edit[address]' => 'Strawberry Jam Lane',
            'org_client_edit[address2]' => 'New York',
            'org_client_edit[county]' => 'West Midlands',
            'org_client_edit[postcode]' => 'B31 1AB',
        ]);
    }
}
