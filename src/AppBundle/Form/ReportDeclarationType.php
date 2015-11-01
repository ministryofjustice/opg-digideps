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
        $builder /*->add('title', 'text')*/
                ->add('id', 'hidden')
                ->add('agree', 'checkbox', [
                     'constraints' => new NotBlank(['message'=>'report-declaration.agree.notBlank']),
                 ])
                ->add('allAgreed', 'choice', array(
                    'choices' => [1=>'Yes', 0=>'No'],
                    'expanded' => true
                ))
                ->add('reasonNotAllAgreed', 'textarea')
                ->add('save', 'submit');
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
            'translation_domain' => 'report-declaration',
            'validation_groups' => function(FormInterface $form){

                $data = $form->getData();
                $validationGroups = ['submitted'];

                if($data->isAllAgreed() == false){
                    $validationGroups[] = "allagreed-no";
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
