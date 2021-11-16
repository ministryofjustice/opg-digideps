<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\UserResearch\UserResearchResponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class UserResearchResponseType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $deputyshipLengthTransKeys = [
            UserResearchResponse::UNDER_ONE,
            UserResearchResponse::ONE_TO_FIVE,
            UserResearchResponse::SIX_TO_TEN,
            UserResearchResponse::OVER_TEN,
        ];

        $deputyshipLengthLabels = array_map(function ($length) {
            return $this->translator->trans('form.deputyshipLength.choices.'.$length, [], 'report-post-submission-user-research');
        }, $deputyshipLengthTransKeys);

        $typesOfResearchTransKeys = ['surveys', 'videoCall', 'phone', 'inPerson'];
        $typesOfResearchLabels = array_map(function ($researchType) {
            return $this->translator->trans('form.agreedResearchTypes.choices.'.$researchType, [], 'report-post-submission-user-research');
        }, $typesOfResearchTransKeys);

        $deviceAccessTransKeys = ['yes', 'no'];
        $deviceAccessLabels = array_map(function ($response) {
            return $this->translator->trans('form.hasAccessToVideoCallDevice.choices.'.$response, [], 'report-post-submission-user-research');
        }, $deviceAccessTransKeys);

        $builder
            ->add('deputyshipLength', ChoiceType::class, [
                'choices' => array_combine($deputyshipLengthLabels, $deputyshipLengthTransKeys),
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'placeholder' => false,
            ])
            ->add('agreedResearchTypes', ChoiceType::class, [
                'choices' => array_combine($typesOfResearchLabels, $typesOfResearchTransKeys),
                'expanded' => true,
                'multiple' => true,
                'required' => true,
                'placeholder' => false,
            ])
            ->add('hasAccessToVideoCallDevice', ChoiceType::class, [
                'choices' => array_combine($deviceAccessLabels, $deviceAccessTransKeys),
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'placeholder' => false,
            ])
            ->add('submitButton', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-post-submission-user-research',
        ]);
    }
}
