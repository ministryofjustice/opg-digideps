<?php

namespace App\Form\Admin;

use App\Entity\Report\ReviewChecklist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewChecklistType extends AbstractType
{
    const SAVE_ACTION = 'save';
    const SUBMIT_ACTION = 'submit';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('answers', FullReviewChecklistType::class)
            ->add('decision', FormTypes\ChoiceType::class, [
                'choices' => [
                    'checklistPage.form.finalDecision.fullReviewOptions.satisfied' => 'satisfied',
                    'checklistPage.form.finalDecision.fullReviewOptions.furtherCaseworkRequired' => 'further-casework-required',
                    'checklistPage.form.finalDecision.fullReviewOptions.escalate' => 'escalate',
                ],
                'expanded' => true,
            ])
            ->add(self::SAVE_ACTION, FormTypes\SubmitType::class, [
                'validation_groups' => false,
            ])
            ->add(self::SUBMIT_ACTION, FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReviewChecklist::class,
            'translation_domain' => 'admin-checklist',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'full-review';
    }
}
