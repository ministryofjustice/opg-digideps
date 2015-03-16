<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\DataTransformer\ArrayToStringTransformer;
use Symfony\Component\Validator\Constraints as Constraints;

class AccountNumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $builder->add('part_1', 'text', [ 'max_length' => 1 ] )
               ->add('part_2', 'text', [ 'max_length' => 1 ] )
               ->add('part_3', 'text', [ 'max_length' => 1 ] )
               ->add('part_4', 'text', [ 'max_length' => 1 ] )
               ->addModelTransformer(new ArrayToStringTransformer(['part_1', 'part_2', 'part_3', 'part_4']));
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
