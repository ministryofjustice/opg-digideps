<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class FeedbackReportType extends AbstractType
{
    use Traits\HasTranslatorTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $satisfactionScores = range(5, 1);
        $satisfactionLabels = array_map(function($score) {
            return $this->translate('satisfactionLevelsChoices.' . $score, [], 'feedback');
        }, $satisfactionScores);

        $builder
            ->add('satisfactionLevel', FormTypes\ChoiceType::class, [
                'choices' => array_combine($satisfactionLabels, $satisfactionScores),
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('comments', FormTypes\TextareaType::class);

        if ($options['include_page_information']) {
            $builder
                ->add('page', FormTypes\TextType::class, [
                    'required' => false,
                ]);
        }

        if ($options['include_contact_information']) {
            $builder
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
                ]);
        }

        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'feedback',
            'include_contact_information' => false,
            'include_page_information' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'feedback_report';
    }
}
