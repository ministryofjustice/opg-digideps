<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Action;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('doYouExpectFinancialDecisions', 'choice', array(
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,
                ))
                ->add('doYouExpectFinancialDecisionsDetails', 'textarea')
                ->add('doYouHaveConcerns', 'choice', array(
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,
                ))
                ->add('doYouHaveConcernsDetails', 'textarea')
                ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-action',
            'validation_groups' => function (FormInterface $form) {

                $data = $form->getData(); /* @var $data Action */
                $validationGroups = ['action'];

                if ($data->getDoYouExpectFinancialDecisions() == 'yes') {
                    $validationGroups[] = 'expect-decisions-yes';
                }

                if ($data->getDoYouHaveConcerns() == 'yes') {
                    $validationGroups[] = 'have-actions-yes';
                }

                return $validationGroups;
            },
        ]);
    }

    public function getName()
    {
        return 'action';
    }
}
