<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class DecisionExistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hasDecisions', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'constraints' => [new NotBlank(['message' => 'decision.noDecisionChoice.notBlank', 'groups' => ['decisions-exist']])],
            ])
            ->add('reasonForNoDecisions', 'textarea')
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-decisions',
            'validation_groups' => function (FormInterface $form) {
                $validationGroups = ['decisions-exist'];
                if ($form['hasDecisions']->getData() === 'no') {
                    $validationGroups = ['reason-no-decisions'];
                }

                return $validationGroups;
            },
        ]);
    }

    public function getName()
    {
        return 'decision_exist';
    }
}
