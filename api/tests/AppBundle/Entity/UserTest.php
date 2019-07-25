<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\User as Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Tests\AppBundle\Entity\Abstracts\EntityTester;

/**
 * User Entity test
 */
class UserTest extends EntityTester
{

    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetSetTeams()
    {
        $teams = new ArrayCollection(['foo']);

        $this->entity->setTeams($teams);

        $this->assertEquals($teams, $this->entity->getTeams());
    }

    public function testAddRemoveTeams()
    {
        $teams = new ArrayCollection(['foo']);

        $this->entity->setTeams($teams);

        $this->assertEquals($teams, $this->entity->getTeams());
    }
}
