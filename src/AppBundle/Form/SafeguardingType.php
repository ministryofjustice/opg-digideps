<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SafeguardingType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('doYouLiveWithClient', 'choice', array(
                    	'choices' => ['yes'=>'response.yes', 'no'=>'response.no'],
                    	'expanded' => true
                 	 ))
		        ->add('howOftenDoYouVisit', 'choice', array(
		        	    'choices' => [ 'everyday' => 'response.everyday',
		        	                   'once_a_week' => 'response.once_a_week',
		        	                   'once_a_month' => 'response.once_a_month',
		        	                   'more_than_twice_a_year' => 'response.more_than_twice_a_year',
		        	                   'once_a_year' => 'response.once_a_year',
		        	                   'less_than_once_a_year' => 'response.less_than_once_a_year' ],
		        	    'expanded' => true
		        	))
		        ->add('howOftenDoYouPhoneOrVideoCall', 'choice', array(
		        	    'choices' => [ 'everyday' => 'response.everyday',
		        	                   'once_a_week' => 'response.once_a_week',
		        	                   'once_a_month' => 'response.once_a_month',
		        	                   'more_than_twice_a_year' => 'response.more_than_twice_a_year',
		        	                   'once_a_year' => 'response.once_a_year',
		        	                   'less_than_once_a_year' => 'response.less_than_once_a_year' ],
		        	    'expanded' => true
		        	))
		        ->add('howOftenDoYouWriteEmailOrLetter', 'choice', array(
		        	    'choices' => [ 'everyday' => 'response.everyday',
		        	                   'once_a_week' => 'response.once_a_week',
		        	                   'once_a_month' => 'response.once_a_month',
		        	                   'more_than_twice_a_year' => 'response.more_than_twice_a_year',
		        	                   'once_a_year' => 'response.once_a_year',
		        	                   'less_than_once_a_year' => 'response.less_than_once_a_year' ],
		        	    'expanded' => true
		        	))
		        ->add('howOftenDoesClientSeeOtherPeople', 'choice', array(
		        	    'choices' => [ 'everyday' => 'response.everyday',
		        	                   'once_a_week' => 'response.once_a_week',
		        	                   'once_a_month' => 'response.once_a_month',
		        	                   'more_than_twice_a_year' => 'response.more_than_twice_a_year',
		        	                   'once_a_year' => 'response.once_a_year',
		        	                   'less_than_once_a_year' => 'response.less_than_once_a_year' ],
		        	    'expanded' => true
		        	))
		        ->add('anythingElseToTell', 'textarea')

		        ->add('doesClientReceivePaidCare', 'choice', array(
                    	'choices' => [ 'yes'=>'response.yes', 'no'=>'response.no'],
                    	'expanded' => true
                 	 ))

		        ->add('howIsCareFunded', 'choice', array(
                    	'choices' => [ 'client_pays_for_all'=>'response.client_pays_for_all', 
                    	               'client_gets_financial_help'=>'response.client_gets_financial_help',
                    	               'all_care_is_paid_by_someone_else' => 'response.all_care_is_paid_by_someone_else' ],
                    	'expanded' => true
                 	 ))
                        
		        ->add('whoIsDoingTheCaring', 'textarea')
                        
                ->add('doesClientHaveACarePlan', 'choice', array(
                        'choices' => [ 'yes'=>'response.yes', 'no'=>'response.no'],
                        'expanded' => true
                    ))
                
		        ->add('whenWasCarePlanLastReviewed', 'date',[ 'widget' => 'text',
			                                                 'input' => 'datetime',
			                                                 'format' => 'dd-MM-yyyy',
			                                                 'invalid_message' => 'safeguarding.whenWasCarePlanLastReviewed.invalidMessage'
			                                              ])
		        ->add('save', 'submit');
	}

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
            'translation_domain' => 'report-safeguarding',
            'validation_groups' => function(FormInterface $form){

            	$data = $form->getData();
            	$validationGroups = ['safeguarding'];

            	if($data->getDoYouLiveWithClient() == "no"){
            		$validationGroups[] = "safeguarding-no";
            	}

            	if($data->getDoesClientHaveACarePlan() == "yes"){
            		$validationGroups[] = "safeguarding-hasCarePlan";
            	}

            	if($data->getDoesClientReceivePaidCare() == "yes"){
            		$validationGroups[] = "safeguarding-paidCare";
            	}
                
            	return $validationGroups;
            },
        ]);
    }
    
    public function getName() 
    {
        return 'safeguarding';
    }
}

?>