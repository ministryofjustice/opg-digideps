<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Team;
use AppBundle\Entity\User;

class TeamControllerTest extends AbstractTestController
{
    private $result;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testRetrievesAllTeamMembersForLoggedInUser()
    {
        $proToken = $this->loginAsProf();
        $proDeputy = self::fixtures()->getRepo('User')->findOneByEmail('prof@example.org');
        $team = new Team($proDeputy->getLastname());

        $this
            ->addTeamToUser($team, $proDeputy)
            ->addTeamToUser($team, self::fixtures()->createUser())
            ->addTeamToUser($team, self::fixtures()->createUser())
            ->flushDatabase();

        $this->result = $this->makeRequestAndReturnResults('/team/members', $proToken, ['groups' => 'user-list']);

        $this->assertExpectedNumTeamMembersAreReturned(3);
        $this->assertPayloadForEachTeamMember();
    }

    /**
     * @param Team $team
     * @param User $user
     * @return $this
     */
    private function addTeamToUser(Team $team, User $user)
    {
        $user->addTeam($team);

        return $this;
    }

    private function flushDatabase()
    {
        self::fixtures()->flush();
    }

    /**
     * @param $endpoint
     * @param $authToken
     * @param array $params
     * @return mixed
     */
    private function makeRequestAndReturnResults($endpoint, $authToken, array $params = [])
    {
        $url = sprintf('%s?%s', $endpoint, http_build_query($params));

        $response = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken'   => $authToken,
        ]);

        return $response['data'];
    }

    /**
     * @param $expectedCount
     */
    private function assertExpectedNumTeamMembersAreReturned($expectedCount)
    {
        $this->assertCount($expectedCount, $this->result);
    }

    private function assertPayloadForEachTeamMember()
    {
        foreach ($this->result as $teamMember) {
            $this->assertCount(9, $teamMember);
            $this->assertTrue(array_key_exists('id', $teamMember));
            $this->assertTrue(array_key_exists('team_names', $teamMember));
            $this->assertTrue(array_key_exists('firstname', $teamMember));
            $this->assertTrue(array_key_exists('lastname', $teamMember));
            $this->assertTrue(array_key_exists('email', $teamMember));
            $this->assertTrue(array_key_exists('active', $teamMember));
            $this->assertTrue(array_key_exists('role_name', $teamMember));
            $this->assertTrue(array_key_exists('phone_main', $teamMember));
            $this->assertTrue(array_key_exists('job_title', $teamMember));
        }
    }
}
