<?php

namespace App\Form\Ndr;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReportDeclarationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id', FormTypes\HiddenType::class)
                ->add('agree', FormTypes\CheckboxType::class, [
                     'constraints' => new NotBlank(['message' => 'report-declaration.agree.notBlank']),
                 ])
                 ->add('agreedBehalfDeputy', FormTypes\ChoiceType::class, [
                    'choices' => array_flip([
                        // api models contains those keys too. Change them accordingly if needed
                        'only_deputy' => 'agreedBehalfDeputy.only_deputy',
                        'more_deputies_behalf' => 'agreedBehalfDeputy.more_deputies_behalf',
                        'more_deputies_not_behalf' => 'agreedBehalfDeputy.more_deputies_not_behalf',
                    ]),
                    'choice_translation_domain' => 'report-declaration',
                    'translation_domain' => 'report-declaration',
                    'expanded' => true,
                ])
                ->add('agreedBehalfDeputyExplanation', FormTypes\TextareaType::class)
                ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'ndr-declaration',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                $validationGroups = ['declare'];

                if ($data->getAgreedBehalfDeputy() == 'more_deputies_not_behalf') {
                    $validationGroups[] = 'declare-explanation';
                }

                return $validationGroups;
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ndr_declaration';
    }
}
