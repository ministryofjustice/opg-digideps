<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class ConcernType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('doYouExpectFinancialDecisions', 'choice', array(
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true
                ))
                ->add('doYouExpectFinancialDecisionsDetails', 'textarea')
                ->add('doYouHaveConcerns', 'choice', array(
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true
                ))
                ->add('doYouHaveConcernsDetails', 'textarea')
                ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-concern',
            'validation_groups' => function(FormInterface $form){

            	$data = $form->getData(); /* @var $data \AppBundle\Entity\Concern */
            	$validationGroups = ['concern'];

            	if($data->getDoYouExpectFinancialDecisions() == "yes"){
            		$validationGroups[] = "expect-decisions-yes";
            	}
                
                if($data->getDoYouHaveConcerns() == "yes"){
            		$validationGroups[] = "have-concerns-yes";
            	}
                
            	return $validationGroups;
            }
        ]);
    }

    public function getName()
    {
        return 'concern';
    }

}
