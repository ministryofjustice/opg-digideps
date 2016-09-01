<?php

namespace AppBundle\Form\Odr\Action;

use AppBundle\Entity\Odr\Odr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfoType extends AbstractType
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
                /* @var $data Odr */
                $validationGroups = ['action-more-info'];

                if ($data->getActionMoreInfo() == 'yes') {
                    $validationGroups[] = 'action-more-info-details';
                }

                return $validationGroups;
            },
            'translation_domain' => 'odr-action-info',
        ]);
    }

    public function getName()
    {
        return 'odr_action_info';
    }
}
