<?php

namespace App\Tests\Integration\Controller;

use App\Entity\Client;
use App\Entity\Note;
use App\Entity\User;

class NoteControllerTest extends AbstractTestController
{
    // users
    private static $tokenDeputy;
    private static $tokenAdmin;
    private static $tokenPa;
    private static $tokenPa2;
    private static $tokenPa3;

    // lay
    private static $deputy1;
    private static $client1;

    // pa
    private static $pa1;
    private static $pa1Client1;
    private static $pa1Client1Note1;
    private static $pa1Client2;
    private static $pa2;
    private static $pa3;
    private static $pa3Client1;

    public function setUp(): void
    {
        parent::setUp();

        if (null === self::$tokenAdmin) {
            self::$tokenDeputy = $this->loginAsDeputy();
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenPa = $this->loginAsPa();
            self::$tokenPa2 = $this->loginAsPaAdmin();
            self::$tokenPa3 = $this->loginAsPaTeamMember();
        }

        // deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);

        // pa 1
        self::$pa1 = self::fixtures()->getRepo('User')->findOneByEmail('pa@example.org');
        self::$pa1Client1 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client1']);
        self::$pa1Client1Note1 = self::fixtures()->createNote(self::$pa1Client1, self::$pa1, 'cat', 'title', 'content');
        self::$pa1Client2 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client2']);
        // pa2 (same team as pa1)
        self::$pa2 = self::fixtures()->getRepo('User')->findOneByEmail('pa_admin@example.org')->addClient(self::$pa1Client1);

        // pa 3 with other client (other team)
        self::$pa3 = self::fixtures()->getRepo('User')->findOneByEmail('pa_team_member@example.org');
        self::$pa3Client1 = self::fixtures()->createClient(self::$pa3, ['setFirstname' => 'pa2Client1']);

        $org = self::fixtures()->createOrganisation('Example', rand(1, 999999).'example.org', true);
        self::fixtures()->flush();
        self::fixtures()->addClientToOrganisation(self::$pa1Client1->getId(), $org->getId());
        self::fixtures()->addUserToOrganisation(self::$pa1->getId(), $org->getId());
        self::fixtures()->addUserToOrganisation(self::$pa2->getId(), $org->getId());

        self::fixtures()->flush()->clear();
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testAdd()
    {
        $this->markTestIncomplete('needs endpoint to rely on client Id first');
    }

    public function testgetOneById()
    {
        $noteId = self::$pa1Client1Note1->getId();
        $url = '/note/'.$noteId;

        // assert Auth and ACL
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
        $this->assertEndpointAllowedFor('GET', $url, self::$tokenPa2);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenPa3);

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
        ])['data'];

        $this->assertEquals($noteId, $data['id']);
        $this->assertEquals('cat', $data['category']);
        $this->assertEquals('title', $data['title']);
        $this->assertEquals('content', $data['content']);
        $this->assertEquals(self::$pa1->getId(), $data['created_by']['id']);
        $this->assertEquals(true, time() - strtotime($data['created_on']) < 3600);
    }

    public function testupdateNote()
    {
        $noteId = self::$pa1Client1Note1->getId();
        $url = '/note/'.$noteId;

        // assert Auth
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenPa3);

        // assert PUT
        $data = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
            'data' => [
                'category' => 'cat-edited',
                'title' => 'title-edited',
                'content' => 'content-edited',
            ],
        ])['data'];

        $note = self::$pa2 = self::fixtures()->getRepo('Note')->find($data);

        $this->assertEquals($noteId, $note->getId());
        $this->assertEquals('cat-edited', $note->getCategory());
        $this->assertEquals('title-edited', $note->getTitle());
        $this->assertEquals('content-edited', $note->getContent());
        $this->assertEquals(true, time() - $note->getCreatedOn()->getTimestamp() < 3600);

        // assert cannot change others' notes
        // assert PUT
        $data = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => false,
            'AuthToken' => self::$tokenPa2,
            'data' => [
                'category' => 'cat-edited2',
                'title' => 'title-edited2',
                'content' => 'content-edited2',
            ],
        ])['data'];
    }

    /**
     * @depends testgetOneById
     * @depends testupdateNote
     */
    public function testDeleteCreator()
    {
        $paUser = self::fixtures()->getRepo(User::class)->findOneBy(['email' => 'pa@example.org']);
        $newUserEmail = rand(1, 99999).'user-to-be-deleted@example.org';

        $newUser = (new User())
            ->setEmail($newUserEmail)
            ->setFirstname('Art')
            ->setLastname('Work')
            ->setRoleName(User::ROLE_PA_NAMED);

        $client = (new Client())
            ->addUser($paUser)
            ->addUser($newUser)
            ->setFirstname('Mona')
            ->setLastname('Lisa');

        $note = (new Note($client, 'fake-category', 'fake-title', 'fake-content'))
            ->setClient($client)
            ->setCreatedBy($newUser);

        self::fixtures()->persist($note);
        self::fixtures()->persist($client);
        self::fixtures()->persist($newUser);
        self::fixtures()->flush();

        $url = '/note/'.$note->getId();
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
        ])['data'];

        self::assertEquals($newUserEmail, $data['created_by']['email']);

        self::fixtures()->remove($newUser);
        self::fixtures()->flush();

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
        ])['data'];

        self::assertNull($data['created_by']);
    }
}
