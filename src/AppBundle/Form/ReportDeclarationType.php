<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormInterface;

class ReportDeclarationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id', 'hidden')
                ->add('agree', 'checkbox', [
                     'constraints' => new NotBlank(['message' => 'report-declaration.agree.notBlank']),
                 ])
                 ->add('agreedBehalfDeputy', 'choice', array(
                    'choices' => [
                        // api models contains those keys too. Change them accordingly if needed
                        'only_deputy' => 'I am the only deputy',
                        'more_deputies_behalf' => 'There is more than one deputy, and I’m signing on everyone’s behalf',
                        'more_deputies_not_behalf' => 'There is more than one deputy, but I’m not signing on everyone’s behalf',
                    ],
                    'expanded' => true,
                ))
                ->add('agreedBehalfDeputyExplanation', 'textarea')
                ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-declaration',
            'validation_groups' => function (FormInterface $form) {

                $data = $form->getData();
                $validationGroups = ['submitted', 'declare'];

                if ($data->getAgreedBehalfDeputy() == 'more_deputies_not_behalf') {
                    $validationGroups[] = 'declare-explanation';
                }

                return $validationGroups;
            },
        ]);
    }

    public function getName()
    {
        return 'report_declaration';
    }
}
