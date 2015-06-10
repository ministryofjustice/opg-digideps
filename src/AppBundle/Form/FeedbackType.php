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
                  ->add('help', 'choice', array(
                     'choices' => [ 'No, I filled in this form myself'=>'No, I filled in this form myself', 
                                    'I have difficulty using computers so someone filled in this form for me' =>'I have difficulty using computers so someone filled in this form for me', 
                                    'I used an accessibility tool such as a screen reader' => 'I used an accessibility tool such as a screen reader',
                                    'I had some other kind of help' => 'I had some other kind of help'],
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
        return 'feedback';
    }
}
