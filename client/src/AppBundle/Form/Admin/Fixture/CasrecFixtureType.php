<?php declare(strict_types=1);

namespace AppBundle\Form\Admin\Fixture;


use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CasrecFixtureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('deputyType', ChoiceType::class, [
                'choices' => ['Lay' => User::TYPE_LAY],
                'data' => $options['deputyType']
            ])
            ->add('reportType', ChoiceType::class, [
                'choices' => ['Property and financial affairs high assets' => 'OPG102'],
                'data' => $options['reportType']
            ])
            ->add('createCoDeputy', ChoiceType::class, [
                'choices' => ['Yes' => true, 'No' => false],
                'data' => $options['createCoDeputy']
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-fixtures'
        ])->setRequired(['deputyType', 'reportType', 'createCoDeputy']);
    }
}
