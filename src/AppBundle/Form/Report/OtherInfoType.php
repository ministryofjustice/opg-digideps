<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OtherInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('actionMoreInfo', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ])
            ->add('actionMoreInfoDetails', 'textarea')
            ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                /* @var $data Report */
                $validationGroups = ['more-info'];

                if ($data->getActionMoreInfo() == 'yes') {
                    $validationGroups[] = 'more-info-details';
                }

                return $validationGroups;
            },
            'translation_domain' => 'report-more-info',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'more_info';
    }
}
