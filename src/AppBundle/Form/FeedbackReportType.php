<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FeedbackReportType extends AbstractType
{
    use Traits\HasTranslatorTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $satisfactionLevel = array_filter(explode("\n", $this->translate('satisfactionLevelsChoices', [], 'feedback')));

        $builder
                 ->add('satisfactionLevel', 'choice', array(
                    'choices' => array_combine($satisfactionLevel, $satisfactionLevel),
                    'expanded' => true,
                    'multiple' => false,
                  ))
                   ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
              'translation_domain' => 'feedback',
        ]);
    }

    public function getName()
    {
        return 'feedback_report';
    }
}
