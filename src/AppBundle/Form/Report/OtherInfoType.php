<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OtherInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('actionMoreInfo', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ))
            ->add('actionMoreInfoDetails', 'textarea')
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
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

    public function getName()
    {
        return 'more_info';
    }
}
