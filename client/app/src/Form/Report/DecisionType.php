<?php

namespace App\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DecisionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder/* ->add('title', FormTypes\TextType::class) */
        ->add('description', FormTypes\TextareaType::class)
            ->add('clientInvolvedBoolean', FormTypes\ChoiceType::class, [
                'choices' => array_flip([1 => 'Yes', 0 => 'No']),
                'expanded' => true,
            ])
            ->add('clientInvolvedDetails', FormTypes\TextareaType::class)
            ->add('save', FormTypes\SubmitType::class);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            foreach (['description', 'clientInvolvedDetails'] as $field) {
                if (isset($data[$field]) && is_string($data[$field])) {
                    $cleaned = trim($data[$field]);
                    $cleaned = preg_replace("/[ \t]+/", ' ', $cleaned);
                    $cleaned = preg_replace("/(\r\n|\r|\n){2,}/", "\n", $cleaned);
                    $data[$field] = $cleaned;
                }
            }

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-decisions',
            'validation_groups' => ['decision-description', 'decision-client-involved', 'decision-client-involved-details'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'decision';
    }
}
