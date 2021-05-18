<?php

namespace App\Form\Admin;

use App\Entity\User;
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

        $listener = $this->getEventListener($options['user']);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, $listener);
    }

    private function getEventListener(User $operatingUser): \Closure
    {
        return function (FormEvent $event) use ($operatingUser) {
            $user = $event->getData();
            $form = $event->getForm();

            if ($user->isLayDeputy()) {
                $form->add('ndrEnabled', FormTypes\CheckboxType::class);
            }

            $adminManagerCanEdit = $operatingUser->isAdminManager() && $user->isLayDeputy();

            if ($operatingUser->isSuperAdmin() || $adminManagerCanEdit) {
                $form->add('email', FormTypes\EmailType::class);
            }
        };
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin',
            'validation_groups' => ['admin_edit_user'],
        ])->setRequired(['user']);
    }

    public function getBlockPrefix()
    {
        return 'admin';
    }
}
