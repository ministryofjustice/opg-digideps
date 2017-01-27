<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DataTransformer\ArrayToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SortCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sort_code_part_1', 'text', ['max_length' => 2])
               ->add('sort_code_part_2', 'text', ['max_length' => 2])
               ->add('sort_code_part_3', 'text', ['max_length' => 2])
               ->addModelTransformer(new ArrayToStringTransformer(['sort_code_part_1', 'sort_code_part_2', 'sort_code_part_3']));
    }

    public function getParent()
    {
        return 'form';
    }

    public function getName()
    {
        return 'sort_code';
    }
}
