<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FeedbackType extends AbstractType
{
    use Traits\HasTranslatorTrait;
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $satisfactionLevelChoices = array_filter(explode("\n", $this->translate('satisfactionLevelsChoices', [], 'feedback')));
        $helpChoices = array_filter(explode("\n", $this->translate('helpChoices', [], 'feedback')));

        $builder->add('difficulty', 'textarea')
                ->add('ideas', 'textarea')
                 ->add('satisfactionLevel', 'choice', array(
                    'choices' => array_combine($satisfactionLevelChoices, $satisfactionLevelChoices),
                    'expanded' => true,
                    'multiple' => false
                  ))
                  ->add('help', 'choice', array(
                     'choices' => array_combine($helpChoices, $helpChoices),
                     'expanded' => true,
                     'multiple' => false
                   ))
                   ->add('save', 'submit');
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
              'translation_domain' => 'feedback'
        ]);
    }
    
    public function getName()
    {
        return 'feedback';
    }
}
