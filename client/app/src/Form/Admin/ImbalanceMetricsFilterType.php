<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImbalanceMetricsFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'startDate',
                FormTypes\DateType::class,
                [
                    'widget' => 'text',
                    'input' => 'datetime',
                    'format' => 'dd-MM-yyyy',
                    'invalid_message' => 'Enter a valid date',
                ]
            )
            ->add(
                'endDate',
                FormTypes\DateType::class,
                [
                    'widget' => 'text',
                    'input' => 'datetime',
                    'format' => 'dd-MM-yyyy',
                    'invalid_message' => 'Enter a valid date',
                ]
            )
            ->add(
                'submitAndDownload',
                FormTypes\SubmitType::class
            )
            ->addEventListener(
                FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']
            );
    }

    public function onPostSubmit(FormEvent $event)
    {
        $entity = $event->getData();

        if ($entity->getEndDate() instanceof \DateTime) {
            $endDate = $entity->getEndDate();
            $entity->setEndDate($endDate->setTime(23, 59, 59));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-metrics',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'admin';
    }
}
