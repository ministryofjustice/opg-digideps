<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', FormTypes\TextType::class)
            ->add('lastname', FormTypes\TextType::class)
            ->add('addressPostcode', FormTypes\TextType::class)
            ->add('save', FormTypes\SubmitType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            if ($user->isLayDeputy()) {
                $form->add('ndrEnabled', FormTypes\CheckboxType::class);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin',
            'validation_groups' => ['admin_add_user'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'admin';
    }
}
