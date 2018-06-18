<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\DataTransformer\ArrayToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;

class SortCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sort_code_part_1', FormTypes\TextType::class, ['attr'=> ['maxlength' => 2]])
               ->add('sort_code_part_2', FormTypes\TextType::class, ['attr'=> ['maxlength' => 2]])
               ->add('sort_code_part_3', FormTypes\TextType::class, ['attr'=> ['maxlength' => 2]])
               ->addModelTransformer(new ArrayToStringTransformer(['sort_code_part_1', 'sort_code_part_2', 'sort_code_part_3']));
    }

    public function getParent()
    {
        //TODO fix ?
        return 'form';
    }

    public function getBlockPrefix()
    {
        return 'sort_code';
    }
}
