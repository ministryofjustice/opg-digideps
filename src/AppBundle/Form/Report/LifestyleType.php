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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $clientFirstName;

    /**
     * @param $step
     */
    public function __construct($step, TranslatorInterface $translator, $clientFirstName)
    {
        $this->step = (int) $step;
        $this->translator = $translator;
        $this->clientFirstName = $clientFirstName;
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
        }


        if ($this->step === 3) {
            $builder->add('activityDetails', 'textarea', []);
        }

        $builder->add('save', 'submit');
    }

    private function translate($key)
    {
        return $this->translator->trans($key, ['%client%' => $this->clientFirstName], 'report-lifestyle');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-lifestyle',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                /* @var $data Lifestyle */
                $validationGroups = [
                    1 => [],
                    2 => [],
                    3 => []
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
