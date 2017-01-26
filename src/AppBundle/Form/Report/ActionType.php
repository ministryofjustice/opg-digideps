<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Action;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ActionType extends AbstractType
{
    /**
     * @var int
     */
    private $step;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param $step
     */
    public function __construct($step, TranslatorInterface $translator, $clientFirstName)
    {
        $this->step = (int)$step;
        $this->translator = $translator;
        $this->clientFirstName = $clientFirstName;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
        ]);
    }

    public function getName()
    {
        return 'action';
    }
}
