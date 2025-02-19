<?php

namespace App\Tests\Integration\Entity\Abstracts;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Abstract entity teste.
 */
abstract class EntityTester extends MockeryTestCase
{
    /**
     * Holds the entity.
     *
     * @var object
     */
    protected $entity;

    public function setUp(): void
    {
        if (empty($this->entityClass)) {
            $this->markTestSkipped('Entity not defined');
        }
        $entityClass = $this->entityClass;

        $this->entity = new $entityClass();
    }

    public function tearDown(): void
    {
        unset($this->entity);
    }
}
