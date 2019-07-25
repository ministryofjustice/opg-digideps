<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Action;
use AppBundle\Entity\Report\MentalCapacity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MentalCapacityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('hasCapacityChanged', FormTypes\ChoiceType::class, [
                    // keep in sync with API model constants
                    'choices' => [
                        'mentalCapacity.form.hasCapacityChanged.choices.changed' => MentalCapacity::CAPACITY_CHANGED,
                        'mentalCapacity.form.hasCapacityChanged.choices.stayedSame' => MentalCapacity::CAPACITY_STAYED_SAME,
                    ],
                    'expanded' => true,
                ])
                ->add('hasCapacityChangedDetails', FormTypes\TextareaType::class)
                ->add('save', FormTypes\SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-decisions',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData(); /* @var $data Action */
                $validationGroups = ['capacity'];

                if ($data->getHasCapacityChanged() == 'changed') {
                    $validationGroups[] = 'has-capacity-changed-yes';
                }

                return $validationGroups;
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mental_capacity';
    }
}
