<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportChangeDueDateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('dueDateChoice', 'choice', [
                'choices' => [
                    0 => 'reportChangeDueDate.form.dueDateChoice.choices.keep',
                    3 => 'reportChangeDueDate.form.dueDateChoice.choices.3weeks',
                    4 => 'reportChangeDueDate.form.dueDateChoice.choices.4weeks',
                    5 => 'reportChangeDueDate.form.dueDateChoice.choices.5weeks',
                ],
                'expanded' => true,
                'multiple' => false,
                'mapped' => false
            ])
            ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-clients',
            'name'               => 'report',
        ]);
    }
}
