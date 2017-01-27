<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Constraints;

class FeedbackType extends AbstractType
{
    use Traits\HasTranslatorTrait;
    use Traits\HasSecurityContextTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $satisfactionLevelChoices = array_filter(explode("\n", $this->translate('satisfactionLevelsChoices', [], 'feedback')));
        $helpChoices = array_filter(explode("\n", $this->translate('helpChoices', [], 'feedback')));

        $builder->add('difficulty', 'textarea')
            ->add('ideas', 'textarea')
            ->add('satisfactionLevel', 'choice', [
                'choices' => array_combine($satisfactionLevelChoices, $satisfactionLevelChoices),
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('help', 'choice', [
                'choices' => array_combine($helpChoices, $helpChoices),
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('emailYesNo', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'mapped' => false,
            ])
            ->add('email', 'email', [
                'constraints' => [
                    new Constraints\Email(['message' => 'login.email.inValid']),
                ],
                'data' => $this->getLoggedUserEmail(),
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
