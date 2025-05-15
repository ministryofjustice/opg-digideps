<?php

namespace App\Tests\Integration\Controller\Ndr;

use App\Entity\Ndr\Ndr;
use App\Entity\Report\ReportSubmission;
use App\Tests\Integration\Controller\AbstractTestController;

class NdrControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $ndr1;
    private static $document1;
    private static $deputy2;
    private static $client2;
    private static $ndr2;
    private static $tokenAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }

        // deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(
            self::$deputy1,
            [
                'setFirstname' => 'c1',
                'setCourtDate' => new \DateTime('2018-11-01'),
            ]
        );
        self::$ndr1 = self::fixtures()->createNdr(self::$client1);
        self::$document1 = self::fixtures()->createDocument(self::$ndr1, 'ndr.pdf');

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$ndr2 = self::fixtures()->createNdr(self::$client2);

        self::fixtures()->flush();
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testSubmitNotAllAgree()
    {
        $this->assertEquals(false, self::$ndr1->getSubmitted());

        $ndrId = self::$ndr1->getId();
        $url = '/ndr/'.$ndrId.'/submit?documentId='.self::$document1->getId();

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'submit_date' => '2015-12-30',
                'agreed_behalf_deputy' => 'more_deputies_not_behalf',
                'agreed_behalf_deputy_explanation' => 'abdexplanation',
            ],
        ]);

        // assert account created with transactions
        $ndr = self::fixtures()->clear()->getRepo('Ndr\Ndr')->find($ndrId);
        /* @var $ndr Ndr */
        $this->assertEquals(true, $ndr->getSubmitted());
        $this->assertEquals('more_deputies_not_behalf', $ndr->getAgreedBehalfDeputy());
        $this->assertEquals('abdexplanation', $ndr->getAgreedBehalfDeputyExplanation());
    }

    public function testSubmit()
    {
        $this->assertEquals(false, self::$ndr1->getSubmitted());

        $ndrId = self::$ndr1->getId();
        $url = '/ndr/'.$ndrId.'/submit?documentId='.self::$document1->getId();

        $ret = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'submit_date' => '2015-12-30',
                'agreed_behalf_deputy' => 'only_deputy',
                'agreed_behalf_deputy_explanation' => 'should not be saved',
            ],
        ])['data'];

        // assert account created with transactions

        $ndr = self::fixtures()->clear()->getRepo('Ndr\Ndr')->find($ndrId);
        /* @var $ndr Ndr */
        $this->assertEquals(true, $ndr->getSubmitted());
        $this->assertEquals('only_deputy', $ndr->getAgreedBehalfDeputy());
        $this->assertEquals(null, $ndr->getAgreedBehalfDeputyExplanation());
        $this->assertEquals('2015-12-30', $ndr->getSubmitDate()->format('Y-m-d'));

        /* @var $reportSubmission ReportSubmission */
        $reportSubmission = self::fixtures()->clear()->getRepo(ReportSubmission::class)->findOneBy(['ndr' => $ndr], ['id' => 'DESC']);
        $this->assertCount(1, $reportSubmission->getDocuments());
    }

    public function testNdrExistsOnClient()
    {
        $clientId = self::$client1->getId();
        $url = '/ndr/client/'.$clientId;

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertTrue($data);
    }

    public function testNdrDoesNotExistOnClient()
    {
        $clientWithNoNdr = $this->fixtures()->createClient(
            self::$deputy1,
            [
                'setFirstname' => 'c2',
                'setCourtDate' => new \DateTime('2018-11-01'),
            ]
        );
        $this->fixtures()->persist($clientWithNoNdr);
        $this->fixtures()->flush();

        $url = '/ndr/client/'.$clientWithNoNdr->getId();

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertFalse($data);
    }
}
