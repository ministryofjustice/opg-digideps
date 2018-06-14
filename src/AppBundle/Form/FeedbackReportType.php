<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType; use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackReportType extends AbstractType
{
    use Traits\HasTranslatorTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $satisfactionLevel = array_filter(explode("\n", $this->translate('satisfactionLevelsChoices', [], 'feedback')));

        $builder
                 ->add('satisfactionLevel', FormTypes\ChoiceType::class, [
                    'choices' => array_combine($satisfactionLevel, $satisfactionLevel),
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
