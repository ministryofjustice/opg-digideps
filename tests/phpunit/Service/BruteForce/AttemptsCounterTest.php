<?php

namespace AppBundle\Service\BruteForce;

use MockeryStub as m;

require_once __DIR__ . '/PredisMock.php';

class AttemptsCounterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BruteForceChecker 
     */
    private $object;

    public function testReachedLevelWarning()
    {
        $this->redis = new PredisMock();
        $this->logger = m::stub('Symfony\Bridge\Monolog\Logger');
        
        $this->object = new AttemptsCounter($this->redis);
        
        // trigger warning with more than 2 attempts
        $this->object->addTrigger('email', 2);
        
        foreach([false, false, true, true] as $i => $expected) {
            $this->object->setKey('email')->registerAttempt('email');
            $this->assertEquals($expected, $this->object->reachedWarning('email'), "Loop index: $i");
        }
        
        $this->object->resetAttempts();
        
        $this->assertFalse($this->object->setKey('email')->registerAttempt('email')->reachedWarning('email'));
    }
    
    public function tearDown()
    {
        m::close();
    }


}