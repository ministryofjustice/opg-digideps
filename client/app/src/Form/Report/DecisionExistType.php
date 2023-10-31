<?php

namespace App\Form\Report;

use App\Entity\Report\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class DecisionExistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('significantDecisionsMade', FormTypes\ChoiceType::class, [
                // keep in sync with API model constants
                'choices' => [
                    'existPage.form.choices.yes' => Report::SIGNIFICANT_DECISION_MADE,
                    'existPage.form.choices.no' => Report::SIGNIFICANT_DECISION_NOT_MADE,
                ],
                'expanded' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'decision.noDecisionChoice.notBlank',
                        'groups' => 'decisions-exist',
                    ]),
                ],
            ])
            ->add('reasonForNoDecisions', FormTypes\TextareaType::class)
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-decisions',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();

                $validationGroups = ['decisions-exist'];

                if ('No' == $data->getSignificantDecisionsMade()) {
                    $validationGroups[] = 'reason-no-decisions';
                }

                return $validationGroups;
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'decisions_exist';
    }
}
