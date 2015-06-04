<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('difficulty', 'textarea')
                ->add('ideas', 'textarea')
                 ->add('satisfactionLevel', 'choice', array(
                    'choices' => [ 'very satisfied'=>'Very satisfied', 
                                   'satisfied' =>'Satisfied', 
                                   'neither satisfied or dissatisfied' => 'Neither satisfied or dissatisfied',
                                   'dissatisfied' => 'Dissatisfied',
                                   'very dissatisfied' => 'Very dissatisfied' ],
                    'expanded' => true,
                    'multiple' => false
                  ))
                 ->add('send', 'submit');
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
              'translation_domain' => 'feedback'
        ]);
    }
    
    public function getName()
    {
        return 'feedback';
    }
}
