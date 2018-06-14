<?php

namespace AppBundle\Form\Ndr;

use AppBundle\Entity\Ndr\Ndr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
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
            ->add('id', FormTypes\HiddenType::class);

        if ($this->step === 1) {
            $builder
                ->add('actionGiveGiftsToClient', FormTypes\ChoiceType::class, [
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,
                ])
                ->add('actionGiveGiftsToClientDetails', FormTypes\TextareaType::class);
        }

        if ($this->step === 2) {
            $builder->add('actionPropertyMaintenance', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ]);
        }

        if ($this->step === 3) {
            $builder->add('actionPropertySellingRent', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ]);
        }

        if ($this->step === 4) {
            $builder->add('actionPropertyBuy', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ]);
        }

        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function getBlockPrefix()
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
