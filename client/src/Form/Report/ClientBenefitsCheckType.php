<?php

declare(strict_types=1);

namespace App\Form\Report;

use App\Form\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClientBenefitsCheckType extends AbstractType
{
    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @var int
     */
    private $step;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->step = (int) $options['step'];

        if (1 === $this->step) {
            $builder->add('whenLastCheckedEntitlement', ChoiceType::class, [
                'choices' => $this->getChoices(),
                'expanded' => true,
            ]);
            $builder->add('dateLastCheckedEntitlement', DateType::class);
        }

        $builder->add('save', FormTypes\SubmitType::class);
    }

    private function getChoices()
    {
        return [
            $this->translator->trans('form.whenLastChecked.choices.haveChecked', [], 'report-client-benefits-check') => 'haveChecked',
            $this->translator->trans('form.whenLastChecked.choices.currentlyChecking', [], 'report-client-benefits-check') => 'currentlyChecking',
            $this->translator->trans('form.whenLastChecked.choices.neverChecked', [], 'report-client-benefits-check') => 'neverChecked',
        ];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'report-client-benefits-check',
                'validation_groups' => [
                    1 => ['client-benefits-check'],
                    2 => [],
                ][$this->step],
            ]
        )
            ->setRequired(['step']);
    }

    public function getBlockPrefix()
    {
        return 'client-benefits-check';
    }
}
