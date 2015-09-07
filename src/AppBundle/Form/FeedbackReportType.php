<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FeedbackReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                 ->add('satisfactionLevel', 'choice', array(
                    'choices' => [ 'very satisfied'=>'Very satisfied', 
                                   'satisfied' =>'Satisfied', 
                                   'neither satisfied or dissatisfied' => 'Neither satisfied or dissatisfied',
                                   'dissatisfied' => 'Dissatisfied',
                                   'very dissatisfied' => 'Very dissatisfied' ],
                    'expanded' => true,
                    'multiple' => false
                  ))
                   ->add('save', 'submit');
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
              'translation_domain' => 'feedback'
        ]);
    }
    
    public function getName()
    {
        return 'feedback_report';
    }
}
