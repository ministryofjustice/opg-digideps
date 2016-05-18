<?php

namespace AppBundle\Service;

use Mockery as m;

class FormErrorsFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        // subelement1
        $subError1 = m::mock('Symfony\Component\Form\FormError')
            ->shouldReceive('getMessage')->andReturn('error2')
            ->getMock();

        $subElement1 = m::mock('Symfony\Component\Form\FormInterface')
            ->shouldReceive('getName')->andReturn('subelement1')
            ->shouldReceive('getErrors')->andReturn([$subError1])
            ->shouldReceive('getIterator')->andReturn(new \ArrayIterator([]))
            ->getMock();

        $iterator = new \ArrayIterator([$subElement1]);

        // root
        $error1 = m::mock('Symfony\Component\Form\FormError')
            ->shouldReceive('getMessage')->andReturn('error1')
            ->getMock();

        $form = m::mock('Symfony\Component\Form\FormInterface')
            ->shouldReceive('getName')->andReturn('root')
            ->shouldReceive('getErrors')->andReturn([$error1])
            ->shouldReceive('getIterator')->andReturn($iterator)
            ->getMock();

        $object = new FormErrorsFormatter();

        $actual = $object->toArray($form);
        $this->assertEquals(['root' => ['error1'], 'root_subelement1' => ['error2']], $actual);
    }
}
