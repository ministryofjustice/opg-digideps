<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Translation\TranslatorInterface;

class ProfServicePreviousFeesEstimateType extends AbstractType
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
            ->add('previousProfFeesEstimateGiven', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'constraints' => [new NotBlank(['message' => 'fee.previousProfFeesEstimateGivenChoice.notBlank', 'groups' => ['current-prof-payments-received']])],
            ])
            ->add('profFeesEstimateSccoReason', 'textarea')
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => $this->translatorDomain,
            'validation_groups' => function (FormInterface $form) {
                $validationGroups = ['previous-prof-fees-estimate-choice'];

                return $validationGroups;
            },
        ]);
    }

    public function getName()
    {
        return 'prof_service_fees';
    }
}
