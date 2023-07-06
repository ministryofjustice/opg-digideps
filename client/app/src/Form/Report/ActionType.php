<?php

namespace App\Form\Report;

use App\Entity\Report\Action;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ActionType extends AbstractType
{
    private $clientFirstName;

    /**
     * @var int
     */
    private $step;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->step = (int) $options['step'];
        $this->translator = $options['translator'];
        $this->clientFirstName = $options['clientFirstName'];

        if (1 === $this->step) {
            $builder
                ->add('doYouExpectFinancialDecisions', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true,
            ])->add('doYouExpectFinancialDecisionsDetails', FormTypes\TextareaType::class);
        }

        if (2 === $this->step) {
            $builder->add('doYouHaveConcerns', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true,
            ])->add('doYouHaveConcernsDetails', FormTypes\TextareaType::class);
        }

        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-actions',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                /* @var $data Action */
                $validationGroups = [];

                if (1 === $this->step) {
                    $validationGroups = ['action-expect-decisions-choice'];
                    if ('yes' == $data->getDoYouExpectFinancialDecisions()) {
                        $validationGroups = ['action-expect-decisions-details'];
                    }
                }

                if (2 === $this->step) {
                    $validationGroups = ['action-have-concerns-choice'];
                    if ('yes' == $data->getDoYouHaveConcerns()) {
                        $validationGroups = ['action-have-concerns-details'];
                    }
                }

                return $validationGroups;
            },
        ])
        ->setRequired(['step', 'translator', 'clientFirstName'])
        ->setAllowedTypes('translator', TranslatorInterface::class);
    }

    public function getBlockPrefix()
    {
        return 'action';
    }
}
