<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountNumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $builder->add('part_1', 'integer', [ 'attr' => [ 'min' => 1, 'max' => 1 ]])
               ->add('part_2', 'integer', [ 'attr' => [ 'min' => 1, 'max' => 1 ]] )
               ->add('part_3', 'integer', [ 'attr' => [ 'min' => 1, 'max' => 1 ]] )
               ->add('part_4', 'integer', [ 'attr' => [ 'min' => 1, 'max' => 1 ]] );
    }
    
    public function getParent()
    {
       return 'form'; 
    }
    
    public function getName()
    {
        return 'account_number';
    }
}
