<?php

namespace AppBundle\Service\BruteForce;

use MockeryStub as m;

// create a simple predis Mock to just return keys

require_once __DIR__ . '/PredisMock.php';

class AttemptsCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttemptsInTime 
     */
    private $object;

    public static function attempts()
    {
        return [ 
                [ [], [0=>false, 100=>false, 1000=>false]  ],
                // 1 attempt in last 60 secs
                [ [1=>10], [0=>true]  ],
                // 2 attempts in last 60 secs
                [ [2=>10], [0=>false, 10=>true]  ],
                // as above with previous history of failures
                [ [2=>10], [0=>false, 1=>true, 2=>true, 3=>true, 14=>false, 15=>true, 100=>false, 200=>false]],
                // two intervals
                [[3 =>  60, 5 =>  120],  [0=>false, 1=>false, 2=>true, 63=>false, 64=>true, 65=>true]],
            ];
    }
    
    /**
     * @dataProvider attempts
     */
    public function testmaxAttemptsReached(array $triggers, array $attemptsTimeStampToExpected)
    {
        $this->redis = new PredisMock();
        $this->logger = m::stub('Symfony\Bridge\Monolog\Logger');
        
        $this->object = new AttemptsChecker($this->redis, 'prefix', 'x', $triggers);
        
        // 1st interval reached
        foreach ($attemptsTimeStampToExpected as $timestamp => $expected) {
             $this->assertEquals($expected, $this->object->registerAttempt($timestamp)->maxAttemptsReached($timestamp));
        }
    }
    
    public function tearDown()
    {
        m::close();
    }

}