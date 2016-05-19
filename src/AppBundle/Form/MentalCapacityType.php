<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MentalCapacityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('hasCapacityChanged', 'choice', array(
                    'choices' => ['changed' => 'Changed', 'stayedSame' => 'Stayed the same'],
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

                $data = $form->getData(); /* @var $data \AppBundle\Entity\Action */
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
