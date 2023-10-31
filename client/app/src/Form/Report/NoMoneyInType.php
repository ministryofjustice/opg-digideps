<?php

namespace App\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class NoMoneyInType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reasonForNoMoneyIn', FormTypes\TextareaType::class, [
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'moneyIn.reasonForNoMoneyIn.notBlank',
                        'groups' => 'reason-for-no-money',
                    ]),
                    new Constraints\Length([
                        'min' => 3,
                        'minMessage' => 'moneyIn.reasonForNoMoneyIn.minLength',
                        'groups' => 'reason-for-no-money',
                    ]),
                    new Constraints\Length([
                        'max' => 256,
                        'maxMessage' => 'moneyIn.reasonForNoMoneyIn.maxLength',
                        'groups' => 'reason-for-no-money',
                    ]),
            ]])
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-money-in',
            'validation_groups' => ['reason-for-no-money'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'reason-for-no-money';
    }
}
