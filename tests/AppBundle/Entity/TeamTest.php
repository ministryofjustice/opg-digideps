<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Team as Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Tests\AppBundle\Entity\Abstracts\EntityTester;

/**
 * Team Entity test
 */
class TeamTest extends EntityTester
{

    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetSetTeamName()
    {
        $data = 'foo';

        $this->entity->setTeamName($data);

        $this->assertEquals($data, $this->entity->getTeamName());
    }

    public function testAddRemoveMembers()
    {
        $data = new ArrayCollection(['foo']);

        $this->entity->setMembers($data);

        $this->assertEquals($data, $this->entity->getMembers());
    }
}
