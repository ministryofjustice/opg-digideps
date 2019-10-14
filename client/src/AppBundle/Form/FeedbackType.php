<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class FeedbackType extends AbstractType
{
    use Traits\HasTranslatorTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $satisfactionScores = range(5, 1);
        $satisfactionLabels = array_map(function($score) {
            return $this->translate('form.satisfactionLevel.choices.' . $score, [], 'feedback');
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
                'constraints' => [
                    new Constraints\Email(['message' => 'login.email.inValid'])
                ]
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
