<?php

namespace AppBundle\Form\Odr;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class VisitsCareType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('planMoveNewResidence', 'choice', array(
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,
                ))
                ->add('planMoveNewResidenceDetails', 'textarea')
                ->add('doYouLiveWithClient', 'choice', array(
                        'choices' => ['yes' => 'Yes', 'no' => 'No'],
                        'expanded' => true,
                      ))
                ->add('howOftenDoYouContactClient', 'textarea')

                ->add('doesClientReceivePaidCare', 'choice', array(
                        'choices' => ['yes' => 'Yes', 'no' => 'No'],
                        'expanded' => true,
                      ))

                ->add('howIsCareFunded', 'choice', array(
                        'choices' => ['client_pays_for_all' => 'They pay for all their own care',
                                       'client_gets_financial_help' => 'They get some financial help (for example, from the local authority or NHS)',
                                       'all_care_is_paid_by_someone_else' => 'All is care paid for by someone else (for example, by the local authority or NHS)', ],
                        'expanded' => true,
                      ))

                ->add('whoIsDoingTheCaring', 'textarea')

                ->add('doesClientHaveACarePlan', 'choice', array(
                        'choices' => ['yes' => 'Yes', 'no' => 'No'],
                        'expanded' => true,
                    ))

                ->add('whenWasCarePlanLastReviewed', 'date', ['widget' => 'text',
                                                             'input' => 'datetime',
                                                             'format' => 'dd-MM-yyyy',
                                                             'invalid_message' => 'safeguarding.whenWasCarePlanLastReviewed.invalidMessage',
                                                          ])
                ->add('save', 'submit')

                ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {

                    $data = $event->getData();

                    // Strip out the date field if it's not needed. Having a partial date field breaks things
                    // if the care plan date is hidden as it receives a date that only has a day
                    if (isset($data['doesClientHaveACarePlan']) && $data['doesClientHaveACarePlan'] == 'no') {
                        $data['whenWasCarePlanLastReviewed'] = null;
                    }

                    // whenWasCarePlanLastReviewed: set day=01 if month and year are set
                    if (!empty($data['whenWasCarePlanLastReviewed']['month']) && !empty($data['whenWasCarePlanLastReviewed']['year'])) {
                        $data['whenWasCarePlanLastReviewed']['day'] = '01';
                        $event->setData($data);
                    }

                    $event->setData($data);
                });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'odr-visits-care',
            'validation_groups' => function (FormInterface $form) {

                $data = $form->getData();
                $validationGroups = ['visits-care'];

                if ($data->getPlanMoveNewResidence() == 'yes') {
                    $validationGroups[] = 'plan-move-residence-yes';
                }

                if ($data->getDoYouLiveWithClient() == 'no') {
                    $validationGroups[] = 'visits-care-no';
                }

                if ($data->getDoesClientHaveACarePlan() == 'yes') {
                    $validationGroups[] = 'visits-care-hasCarePlan';
                }

                if ($data->getDoesClientReceivePaidCare() == 'yes') {
                    $validationGroups[] = 'visits-care-paidCare';
                }

                return $validationGroups;
            },
        ]);
    }

    public function getName()
    {
        return 'odr_visits_care';
    }
}
