<?php
namespace AppBundle\Service;

use Mockery as m;
use Symfony\Component\Form\Form;
use \Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;

class FormErrorsFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $error1 = m::mock('Symfony\Component\Form\FormError')
            ->shouldReceive('getMessage')->andReturn('error1')
            ->getMock();

        $error2 = m::mock('Symfony\Component\Form\FormError')
            ->shouldReceive('getMessage')->andReturn('error2')
            ->getMock();

        $element1 = m::mock('Symfony\Component\Form\FormInterface')
            ->shouldReceive('getName')->andReturn('subelement1')
            ->shouldReceive('getErrors')->andReturn([$error2])
            ->shouldReceive('getIterator')->andReturn(new \ArrayIterator([]))
            ->getMock();

        $iterator = new \ArrayIterator([$element1]);

        $form = m::mock('Symfony\Component\Form\FormInterface')
            ->shouldReceive('getName')->andReturn('root')
            ->shouldReceive('getErrors')->andReturn([$error1])
            ->shouldReceive('getIterator')->andReturn($iterator)
            ->getMock();


        $object = new FormErrorsFormatter;

        $actual = $object->toArray($form);
        $this->assertEquals(['root' => ['error1'], 'root_subelement1' => ['error2']], $actual);
    }


}