<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        return 'feedback_report';
    }
}
