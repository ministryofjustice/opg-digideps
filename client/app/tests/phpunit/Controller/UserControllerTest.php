<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Tests\Unit\Controller\AbstractTestController;

class UserControllerTest extends AbstractTestController
{
    private static $user;

    private static $tokenTeamMember;

    public function setUp(): void
    {
        parent::setUp();

        if (null === self::$tokenTeamMember) {
            self::$tokenTeamMember = $this->loginAsPaTeamMember();
        }

        self::$user = self::fixtures()->createUser();

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

    public function testAddAuth()
    {
        $url = '/user/details';

        $this->assertEndpointNeedsAuth('PUT', $url);

        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenTeamMember);
    }

    /** @test */
    public function addUserToOrganisation(): void
    {
        $url = '/user/details';

        self::$tokenTeamMember = $this->loginAsPaTeamMember();

        $return = $this->assertJsonRequest('PUT', $url, [
            'data' => [
                'role_name' => User::ROLE_PA_TEAM_MEMBER, // org team member role
                'firstname' => 'n',
                'lastname' => 's',
                'email' => 'n.s@example.org',
            ],
            'mustSucceed' => true,
            'AuthToken' => self::$tokenTeamMember,
        ]);

        $user = $this->fixtures()->clear()->getRepo('User')->find($return['data']['id']);

        $this->assertEquals('n', $user->getFirstname());
        $this->assertEquals('s', $user->getLastname());
        $this->assertEquals('n.s@example.org', $user->getEmail());
        self::assertTrue($user->getPreRegisterValidatedDate() instanceof \DateTime);
    }
}
