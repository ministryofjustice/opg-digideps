<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Action;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

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
        $this->step            = (int) $options['step'];
        $this->translator      = $options['translator'];
        $this->clientFirstName = $options['clientFirstName'];

        if ($this->step === 1) {
            $builder
                ->add('doYouExpectFinancialDecisions', 'choice', [
                'choices'  => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ])->add('doYouExpectFinancialDecisionsDetails', 'textarea');
        }

        if ($this->step === 2) {
            $builder->add('doYouHaveConcerns', 'choice', [
                'choices'  => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ])->add('doYouHaveConcernsDetails', 'textarea');
        }

        $builder->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-actions',
            'validation_groups'  => function (FormInterface $form) {
                $data = $form->getData();
                /* @var $data Action */
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
        ->setRequired(['step', 'translator', 'clientFirstName'])
        ->setAllowedTypes(['translator', TranslatorInterface::class]);
    }

    public function getName()
    {
        return 'action';
    }
}
