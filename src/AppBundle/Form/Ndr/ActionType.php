<?php

namespace AppBundle\Form\Ndr;

use AppBundle\Entity\Ndr\Ndr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionType extends AbstractType
{
    private $step;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->step = (int) $options['step'];

        $builder
            ->add('id', 'hidden');

        if ($this->step === 1) {
            $builder
                ->add('actionGiveGiftsToClient', 'choice', [
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,
                ])
                ->add('actionGiveGiftsToClientDetails', 'textarea');
        }

        if ($this->step === 2) {
            $builder->add('actionPropertyMaintenance', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ]);
        }

        if ($this->step === 3) {
            $builder->add('actionPropertySellingRent', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ]);
        }

        if ($this->step === 4) {
            $builder->add('actionPropertyBuy', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ]);
        }

        $builder->add('save', 'submit');
    }

    public function getName()
    {
        return 'actions';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'ndr-actions',
            'validation_groups' => function (FormInterface $form) {
                $ndr = $form->getData();
                /* @var $ndr Ndr */

                return [
                    1 => ($ndr->getActionGiveGiftsToClient() == 'yes')
                        ? ['action-give-gifts', 'action-give-gifts-details']
                        : ['action-give-gifts'],
                    2 => ['action-property-maintenance'],
                    3 => ['action-property-selling-rent'],
                    4 => ['action-property-buy'],
                ][$this->step];
            },
        ])
        ->setRequired(['step']);
    }
}
