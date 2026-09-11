<?php

namespace OPG\Digideps\Frontend\Form\Report;

use OPG\Digideps\Frontend\Entity\Report\Action;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionType extends AbstractType
{
    private int $step;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->step = (int) ($options['step'] ?? 1);

        if ($this->step === 1) {
            $builder
                ->add('doYouExpectFinancialDecisions', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true,
            ])->add('doYouExpectFinancialDecisionsDetails', FormTypes\TextareaType::class);
        }

        if ($this->step === 2) {
            $builder->add('doYouHaveConcerns', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true,
            ])->add('doYouHaveConcernsDetails', FormTypes\TextareaType::class);
        }

        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-actions',
            'data_class' => Action::class,
            'validation_groups' => function (FormInterface $form) {
                /* @var Action $data */
                $data = $form->getData();

                $validationGroups = [];

                if ($this->step === 1) {
                    $validationGroups = ['action-expect-decisions-choice'];
                    if ($data->getDoYouExpectFinancialDecisions() == 'yes') {
                        $validationGroups = ['action-expect-decisions-details'];
                    }
                }

                if ($this->step === 2) {
                    $validationGroups = ['action-have-concerns-choice'];
                    if ($data->getDoYouHaveConcerns() == 'yes') {
                        $validationGroups = ['action-have-concerns-details'];
                    }
                }

                return $validationGroups;
            },
        ])
        ->setRequired(['step']);
    }

    public function getBlockPrefix(): string
    {
        return 'action';
    }
}
