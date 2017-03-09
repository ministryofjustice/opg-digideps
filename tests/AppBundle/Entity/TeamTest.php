<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Team as Entity;
use AppBundle\Entity\Team;
use Doctrine\Common\Collections\ArrayCollection;
use Tests\AppBundle\Entity\Abstracts\EntityTester;

/**
 * Team Entity test
 */
class TeamTest extends EntityTester
{
    /**
     * Override as ctor args are needed  
     */
    public function setUp()
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
