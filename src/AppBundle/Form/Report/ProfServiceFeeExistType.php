<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Translation\TranslatorInterface;

class ProfServiceFeeExistType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $translatorDomain;

    public function __construct(TranslatorInterface $translator, $translatorDomain)
    {
        $this->translator = $translator;
        $this->translatorDomain = $translatorDomain;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currentProfPaymentsReceived', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'constraints' => [new NotBlank(['message' => 'fee.noFeesChoice.notBlank', 'groups' => ['current-prof-payments-received']])],
            ])
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-pa-fee-expense',
            'validation_groups' => function (FormInterface $form) {
                $validationGroups = ['current-prof-fees-received-choice'];

                return $validationGroups;
            },
        ]);
    }

    public function getName()
    {
        return 'prof_service_fees';
    }
}
