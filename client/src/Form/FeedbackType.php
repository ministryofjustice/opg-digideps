<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class FeedbackType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public const HONEYPOT_FIELD_NAME = 'old_question';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $satisfactionScores = range(5, 1);
        $satisfactionLabels = array_map(function ($score) {
            return $this->translator->trans('form.satisfactionLevel.choices.' . $score, [], 'feedback');
        }, $satisfactionScores);

        $builder
            ->add('specificPage', FormTypes\ChoiceType::class, [
                'choices' => [
                    'The whole site' => true,
                    'A specific page' => false
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('page', FormTypes\TextType::class, [
                'required' => false,
            ])
            ->add('comments', FormTypes\TextareaType::class)
            ->add('name', FormTypes\TextType::class, [
                'required' => false,
            ])
            ->add('email', FormTypes\EmailType::class, [
                'required' => false,
            ])
            ->add('phone', FormTypes\TextType::class, [
                'required' => false,
            ])
            ->add('satisfactionLevel', FormTypes\ChoiceType::class, [
                'choices' => array_combine($satisfactionLabels, $satisfactionScores),
                'expanded' => true,
                'multiple' => false,
                'required' => false,
                'placeholder' => false,
            ])
            ->add(self::HONEYPOT_FIELD_NAME, FormTypes\TextType::class, ['required' => false])
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'feedback',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'feedback';
    }
}
