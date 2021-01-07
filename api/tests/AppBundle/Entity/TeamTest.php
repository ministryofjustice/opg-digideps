<?php

namespace Tests\App\Entity;

use App\Entity\Team;
use App\Entity\Team as Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Tests\App\Entity\Abstracts\EntityTester;

/**
 * Team Entity test
 */
class TeamTest extends EntityTester
{
    /**
     * Override as ctor args are needed
     */
    public function setUp(): void
    {
        $this->entity = new Team('t1');
    }

    public function testGetTeamName()
    {
        $this->assertEquals('t1', $this->entity->getTeamName());
    }

    public function testAddRemoveMembers()
    {
        $data = new ArrayCollection(['foo']);

        $this->entity->setMembers($data);

        $this->assertEquals($data, $this->entity->getMembers());
    }
}
