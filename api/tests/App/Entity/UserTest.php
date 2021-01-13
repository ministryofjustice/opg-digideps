<?php

namespace Tests\App\Entity;

use App\Entity\User as Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Tests\App\Entity\Abstracts\EntityTester;

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
