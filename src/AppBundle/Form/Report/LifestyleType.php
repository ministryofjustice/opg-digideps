<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Lifestyle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LifestyleType extends AbstractType
{
    /**
     * @var int
     */
    private $step;

    /**
     * LifestyleType constructor.
     *
     * @param $step
     */
    public function __construct($step)
    {
        $this->step = (int) $step;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-lifestyle',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                /* @var $data Lifestyle */
                $validationGroups = [
                    1 => ['lifestyle-care-appointments'],
                    2=> ($data->getDoesClientUndertakeSocialActivities() == 'yes')
                        ?['lifestyle-undertake-social-activities', 'lifestyle-activity-details-yes']
                        :['lifestyle-undertake-social-activities', 'lifestyle-activity-details-no'],
                ][$this->step];

                return $validationGroups;
            },
        ]);
    }

    public function getName()
    {
        return 'lifestyle';
    }
}
