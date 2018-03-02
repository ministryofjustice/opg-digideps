<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @TODO remove if not used after incomplete report have been merged and  no further changes are required
 */
class ReportResubmitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id', 'hidden')
                ->add('agree', 'checkbox', [
                     'constraints' => new NotBlank(['message' => 'report.reSubmission.agree.notBlank']),
                 ])
                ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-overview',
        ]);
    }

    public function getName()
    {
        return 'report_resubmit';
    }
}
