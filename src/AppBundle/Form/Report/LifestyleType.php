<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Lifestyle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LifestyleType extends AbstractType
{
    /**
     * @var int
     */
    private $step;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->step = (int) $options['step'];

        if ($this->step === 1) {
            $builder->add('careAppointments', 'textarea', []);
        }

        if ($this->step === 2) {
            $builder->add('doesClientUndertakeSocialActivities', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                 'expanded' => true,
            ]);

            $builder->add('activityDetailsYes', 'textarea', []);
            $builder->add('activityDetailsNo', 'textarea', []);
        }

        $builder->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-lifestyle',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                /* @var $data Lifestyle */
                $validationGroups = [
                    1 => ['lifestyle-care-appointments'],
                    2 => array_merge(
                        ['lifestyle-undertake-social-activities'],
                        $data->getDoesClientUndertakeSocialActivities() == 'yes'
                        ? ['lifestyle-activity-details-yes']
                        : [],
                        $data->getDoesClientUndertakeSocialActivities() == 'no'
                        ? ['lifestyle-activity-details-no']
                        : []
                    )
                ][$this->step];

                return $validationGroups;
            },
        ])
        ->setRequired(['step']);
    }

    public function getBlockPrefix()
    {
        return 'lifestyle';
    }
}
