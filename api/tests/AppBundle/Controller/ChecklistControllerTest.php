<?php declare(strict_types=1);

namespace Tests\AppBundle\Controller;

class ChecklistControllerTest extends AbstractTestController
{
    public function setUp(): void
    {

    }



    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }
}
