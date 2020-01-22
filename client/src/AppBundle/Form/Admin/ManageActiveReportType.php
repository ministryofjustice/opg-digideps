<?php

namespace AppBundle\Form\Admin;

use AppBundle\Form\DateType;
use AppBundle\Form\Subscriber\ReportTypeChoicesSubscriber;
use AppBundle\Form\Traits\HasTranslatorTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class ManageActiveReportType extends AbstractType
{
    use HasTranslatorTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dueDateChoice', ReportDueDateType::class)
            ->add('dueDateCustom', DateType::class, [
                'invalid_message' => 'report.dueDate.invalidMessage',
                'mapped'      => false,
                'required'    => false,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'report.dueDate.notBlank', 'groups' => ['due_date_new']]),
                    new Constraints\Date(['message' => 'report.dueDate.invalidMessage', 'groups' => ['due_date_new']]),
                ],
            ])
            ->addEventSubscriber(new ReportTypeChoicesSubscriber($this->translator))
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-clients',
            'compound' => true
        ]);
    }
}
