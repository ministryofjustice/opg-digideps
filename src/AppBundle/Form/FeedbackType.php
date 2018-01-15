<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class FeedbackType extends AbstractType
{
    use Traits\HasTranslatorTrait;
    use Traits\TokenStorageTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $satisfactionLevelChoices = array_filter(explode("\n", $this->translate('satisfactionLevelsChoices', [], 'feedback')));
        $helpChoices = array_filter(explode("\n", $this->translate('helpChoices', [], 'feedback')));

        $builder
            ->add('satisfactionLevel', 'choice', [
                'choices' => array_combine($satisfactionLevelChoices, $satisfactionLevelChoices),
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('save', 'submit');

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (empty($data['emailYesNo']) || $data['emailYesNo'] != 'yes') {
                $data['email'] = null;
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
              'translation_domain' => 'feedback',
        ]);
    }

    public function getName()
    {
        return 'feedback';
    }
}
