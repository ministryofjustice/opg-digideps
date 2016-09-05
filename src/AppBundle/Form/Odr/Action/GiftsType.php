<?php

namespace AppBundle\Form\Odr\Action;

use AppBundle\Entity\Odr\Odr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GiftsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('actionGiveGiftsToClient', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ))
            ->add('actionGiveGiftsToClientDetails', 'textarea')
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => function (FormInterface $form) {

                $data = $form->getData();
                /* @var $data Odr */
                $validationGroups = ['action-give-gifts'];

                if ($data->getActionGiveGiftsToClient() == 'yes') {
                    $validationGroups[] = 'action-give-gifts-details';
                }

                return $validationGroups;
            },
            'translation_domain' => 'odr-action-gifts',
        ]);
    }

    public function getName()
    {
        return 'odr_action_gift';
    }
}
