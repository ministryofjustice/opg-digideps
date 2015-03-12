<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\DataTransformer\ArrayToStringTransformer;
use Symfony\Component\Validator\Constraints as Constraints;

class SortCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $builder->add('sort_code_part_1', 'text', ['attr' => [ 'min' => 2, 'max' => 2 ]])
               ->add('sort_code_part_2', 'text',['attr' => [ 'min' => 2, 'max' => 2 ]])
               ->add('sort_code_part_3', 'text', ['attr' => [ 'min' => 2, 'max' => 2 ]])
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