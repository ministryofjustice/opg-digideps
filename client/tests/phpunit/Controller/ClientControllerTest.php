<?php declare(strict_types=1);

namespace AppBundle\Controller\Admin;


use AppBundle\Guzzle\MockClient;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Client as SymfonyClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;


class ClientControllerTest extends WebTestCase
{

    const ADMIN_EMAIL = 'admin@publicguardian.gov.uk';
    const ADMIN_PASSWORD = 'Abcd1234';
    const BASIC_AUTH_CREDS = ['PHP_AUTH_USER' => self::ADMIN_EMAIL, 'PHP_AUTH_PW' => self::ADMIN_PASSWORD];

    /**
     * @group acs
     */
    public function testOrganisationsAssociatedWithClientAreDisplayed()
    {
//        $org = new Organisation();
//        $org->setName('Test Org');
//
//        $this->getEntityManager()->persist($org);
//
//        $client = new Client();
//        $client->setId(1);
//        $client->setCaseNumber('someCaseNumber');
//        $client->setFirstname('John');
//        $client->setLastname('Doe');
//        $client->setTotalReportCount(0);
//        $client->setOrganisation([$org]);
//
//        $this->getEntityManager()->persist($client);
//        $this->getEntityManager()->flush();
//
//        /** @var \Symfony\Component\HttpKernel\Client $client */
//        $client = $this->getService('test.client');
//
//        $crawler = $client->request(
//            Request::METHOD_GET,
//            sprintf('/%s/details', $client->getId()),
//            [],
//            [],
//            self::BASIC_AUTH_CREDS
//        );
//
//        print_r($crawler->html());

        $kernel = static::bootKernel();

        /** @var SymfonyClient $client */
        $client = $kernel->getContainer()->get('test.client');

//        $client = static::createClient();

        /** @var MockClient $api */
        $api = $client->getContainer()->get('guzzle_json_http_client');

        $responseJSON = <<<JSON
{
    "success": true,
    "data": {
        "id": 18,
        "users": [
            {
                "id_of_client_with_details": 18,
                "pa_team_name": null,
                "is_co_deputy": false,
                "id": 31,
                "firstname": "Lay",
                "lastname": "Dep",
                "email": "red-squirrel@publicguardian.gov.uk",
                "active": true,
                "registration_date": "2019-08-19 16:25:15",
                "registration_token": "",
                "token_date": "2019-08-19 16:25:15",
                "role_name": "ROLE_LAY_DEPUTY",
                "ga_tracking_id": null,
                "phone_main": "07987123123",
                "phone_alternative": null,
                "last_logged_in": "2019-08-19 16:26:44",
                "deputy_no": null,
                "ndr_enabled": true,
                "ad_managed": null,
                "job_title": null,
                "agree_terms_use": null,
                "agree_terms_use_date": null,
                "co_deputy_client_confirmed": false,
                "address1": "16 Deputy Road",
                "address2": "Beeston",
                "address3": "Notts",
                "address_postcode": "SW11AA",
                "address_country": "GB"
            }
        ],
        "reports": [
            {
                "available_sections": [
                    "decisions",
                    "contacts",
                    "visitsCare",
                    "bankAccounts",
                    "moneyTransfers",
                    "moneyIn",
                    "moneyOut",
                    "assets",
                    "debts",
                    "gifts",
                    "balance",
                    "actions",
                    "otherInfo",
                    "deputyExpenses",
                    "documents"
                ],
                "is_due": false,
                "has106flag": false,
                "report_title": "propertyAffairsGeneral",
                "status": {
                    "decisions_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "contacts_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "visits_care_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "bank_accounts_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "money_transfer_state": {
                        "state": "done",
                        "nOfRecords": 0
                    },
                    "money_in_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "money_out_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "money_in_short_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "money_out_short_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "balance_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "is_ready_to_submit": false,
                    "assets_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "debts_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "pa_fees_expenses_state": {
                        "state": "done",
                        "nOfRecords": 0
                    },
                    "prof_current_fees_state": {
                        "state": "done",
                        "nOfRecords": 0
                    },
                    "prof_deputy_costs_state": {
                        "state": "done",
                        "nOfRecords": 0
                    },
                    "prof_deputy_costs_estimate_state": {
                        "state": "done",
                        "nOfRecords": 0
                    },
                    "actions_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "other_info_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "documents_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "expenses_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "gifts_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "lifestyle_state": {
                        "state": "not-started",
                        "nOfRecords": 0
                    },
                    "status": "notStarted"
                },
                "id": 13,
                "type": "102",
                "start_date": "2018-11-01",
                "due_date": "2019-11-26",
                "end_date": "2019-10-01",
                "submit_date": null,
                "un_submit_date": null,
                "submitted": null,
                "report_seen": true,
                "agreed_behalf_deputy": null,
                "agreed_behalf_deputy_explanation": null,
                "wish_to_provide_documentation": null,
                "current_prof_payments_received": null,
                "previous_prof_fees_estimate_given": null,
                "prof_fees_estimate_scco_reason": null,
                "unsubmitted_sections_list": null,
                "checklist": null,
                "no_asset_to_add": null,
                "reason_for_no_contacts": null,
                "reason_for_no_decisions": null,
                "no_transfers_to_add": null
            }
        ],
        "ndr": {
            "report_title": "ndr",
            "id": 18,
            "visits_care": null,
            "no_asset_to_add": null,
            "submitted": null,
            "start_date": "2019-08-19T16:25:56+01:00",
            "submit_date": null,
            "agreed_behalf_deputy": null,
            "agreed_behalf_deputy_explanation": null
        },
        "organisations": {
            "id": 1,
            "name": "Test Org",
            "emailIdentifier": "testOrg.com",
            "isActivated": 1,
            "users": []
        },
        "case_number": "12355555",
        "email": "",
        "phone": "07987123122",
        "address": "16 Client Road",
        "address2": "Beeston",
        "county": "Notts",
        "postcode": "NG12LK",
        "country": "GB",
        "firstname": "Client",
        "lastname": "Jones",
        "court_date": "2018-11-01",
        "date_of_birth": null,
        "archived_at": null,
        "deleted_at": null
    },
    "message": ""
}
JSON;

        $api->append(new Response(200, ['Content-Type' => 'application/json'], $responseJSON));

        $crawler = $client->request(Request::METHOD_GET, '/admin/client/14/details', [], [], self::BASIC_AUTH_CREDS);

        self::assertContains('Test Org', $crawler->html());
    }

    protected static function getService($id)
    {
        return self::$kernel->getContainer()->get($id);
    }
}
