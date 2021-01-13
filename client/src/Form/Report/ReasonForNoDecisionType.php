<?php

namespace App\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class ReasonForNoDecisionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reasonForNoDecisions', FormTypes\TextareaType::class, ['constraints' => [new Constraints\NotBlank(['message' => 'decision.no-decision-reason.notBlank'])]])
            ->add('save', FormTypes\SubmitType::class);

        //if (array_key_exists ( 'action' , $options )) {
        $builder->setAction($options['action']);
        //}
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
              'translation_domain' => 'report-decisions',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'reason_for_no_decision';
    }
}
