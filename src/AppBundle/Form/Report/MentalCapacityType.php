<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Action;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Entity\Report\MentalCapacity;

class MentalCapacityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('hasCapacityChanged', 'choice', array(
                    // keep in sync with API model constants
                    'choices' => [
                        MentalCapacity::CAPACITY_CHANGED => 'Changed',
                        MentalCapacity::CAPACITY_STAYED_SAME => 'Stayed the same',
                    ],
                    'expanded' => true,
                ))
                ->add('hasCapacityChangedDetails', 'textarea')
                ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-mental-capacity',
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

    public function getName()
    {
        return 'mental_capacity';
    }
}
