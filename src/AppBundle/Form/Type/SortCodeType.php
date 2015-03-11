<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SortCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $builder->add('part_1', 'integer', [ 'attr' => [ 'min' => 2, 'max' => 2 ]])
               ->add('part_2', 'integer', [ 'attr' => [ 'min' => 2, 'max' => 2 ]] )
               ->add('part_3', 'integer', [ 'attr' => [ 'min' => 2, 'max' => 2 ]] );
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