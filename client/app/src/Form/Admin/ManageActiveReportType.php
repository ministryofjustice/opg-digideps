<?php

namespace OPG\Digideps\Frontend\Form\Admin;

use OPG\Digideps\Frontend\Form\DateType;
use OPG\Digideps\Frontend\Form\Subscriber\ReportTypeChoicesSubscriber;
use OPG\Digideps\Frontend\Form\Traits\HasTranslatorTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class ManageActiveReportType extends AbstractType
{
    use HasTranslatorTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateType::class, [
                'invalid_message' => 'report.startDate.invalidMessage',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'report.startDate.notBlank', 'groups' => ['startEndDates']]),
                    new Constraints\Date(['message' => 'report.startDate.invalidMessage', 'groups' => ['startEndDates ']]),
                ],
            ])
            ->add('endDate', DateType::class, [
                'invalid_message' => 'report.endDate.invalidMessage',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'report.endDate.notBlank', 'groups' => ['startEndDates']]),
                    new Constraints\Date(['message' => 'report.endDate.invalidMessage', 'groups' => ['startEndDates ']]),
                ],
            ])
            ->add('dueDateChoice', ReportDueDateType::class)
            ->add('dueDateCustom', DateType::class, [
                'invalid_message' => 'report.dueDate.invalidMessage',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'report.dueDate.notBlank', 'groups' => ['due_date_new']]),
                    new Constraints\Date(['message' => 'report.dueDate.invalidMessage', 'groups' => ['due_date_new']]),
                ],
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-clients',
            'compound' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'manage_report';
    }
}
