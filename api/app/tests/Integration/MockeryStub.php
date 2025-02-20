<?php

/**
 * Mockery extension to quickly create stubs. Support for method calls chained.
 *
 * See unit test for an example of usage
 *
 * </code>
 */
class MockeryStub extends Mockery
{
    /**
     * @param string $class        class name
     * @param array  $expectations array of method->result, where method can be method(arg) or a chain of methods get(1)->get(2)-> ...
     *
     * @return Mockery\MockInterface
     */
    public static function stub($class, array $expectations = [])
    {
        if (in_array('Mockery\MockInterface', class_implements($class))) {
            $mock = $class; // already a mock
        } elseif (is_string($class)) {
            $mock = self::mock($class);
        } else {
            throw new InvalidArgumentException(__METHOD__.' first arument should be a mock or class fullname');
        }

        foreach ($expectations as $shouldReceives => $andReturn) {
            if (false === strpos($shouldReceives, '->')) {
                self::mockShouldReceiveAndReturn($mock, $shouldReceives, $andReturn);
            } else {
                self::chainMock($mock, explode('->', $shouldReceives), $andReturn);
            }
        }

        return $mock;
    }

    /**
     * $mock will return $andReturn when the chained methods are called.
     *
     * @param Mockery\MockExpectation $mock
     * @param array                   $shouldReceivesArray array of methods (with optional params)
     */
    private static function chainMock($mock, array $shouldReceivesArray, $andReturn)
    {
        // start creating the mocks from the last one
        $firstMethod = array_shift($shouldReceivesArray);

        // start from the end of the chain
        while ($currentMethod = array_pop($shouldReceivesArray)) {
            $lastMock = self::mock('Mockery');
            self::mockShouldReceiveAndReturn($lastMock, $currentMethod, $andReturn);
            $andReturn = $lastMock; // next iteraetion will return this mock
        }

        self::mockShouldReceiveAndReturn($mock, $firstMethod, $lastMock);
    }

    /**
     * Add assertion on $mock, with method $shouldReceive should return $andReturn.
     *
     * Supports method(arg1, arg2,..., argN)
     *
     * @param Mockery\MockExpectation $mock
     * @param string                  $shouldReceive method(arg1, arg2,..., argN) or just the method name
     */
    private static function mockShouldReceiveAndReturn($mock, $shouldReceive, $andReturn)
    {
        preg_match('/^(?P<method>\w+)(\((?P<args>[^\(\)]*)\))?$/i', $shouldReceive, $matches);
        if (empty($matches['method'])) {
            throw new InvalidArgumentException('Syntax error. Expected "method" or "method()" or "method(arg1,arg2, ..., argN)" in '.$shouldReceive);
        }
        if (!empty($matches['args'])) {
            $args = explode(',', $matches['args']);
            $mock->shouldReceive($matches['method'])->withArgs($args)->andReturn($andReturn);
        } else {
            $mock->shouldReceive($matches['method'])->withNoArgs()->andReturn($andReturn);
        }
    }
}
