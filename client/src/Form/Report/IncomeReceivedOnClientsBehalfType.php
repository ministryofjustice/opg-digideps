<?php

declare(strict_types=1);

namespace App\Form\Report;

use App\Entity\Report\IncomeReceivedOnClientsBehalf;
use App\EventListener\AtLeastOneRequiredListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IncomeReceivedOnClientsBehalfType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('incomeType', TextType::class, ['required' => true]);
        $builder->add('amount', NumberType::class, ['required' => false]);
        $builder->add('amountDontKnow', CheckboxType::class, ['required' => false]);

        $builder->addEventSubscriber(
            new AtLeastOneRequiredListener(
            'amount',
            'amountDontKnow')
        );

        // amountDontKnow is not a property of IncomeReceivedOnClientsBehalf. If ticked, set amount to null and
        // always unset amountDontKnow before submission
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        if (isset($data['amountDontKnow']) && $data['amountDontKnow']) {
            $data['amount'] = null;
        }

        $event->setData($data);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IncomeReceivedOnClientsBehalf::class,
            'allow_add' => true,
            'allow_extra_fields' => true,
        ]);
    }
}
