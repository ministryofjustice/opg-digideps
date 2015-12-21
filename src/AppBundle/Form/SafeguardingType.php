<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class SafeguardingType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('doYouLiveWithClient', 'choice', array(
                    	'choices' => ['yes'=>'Yes', 'no'=>'No'],
                    	'expanded' => true
                 	 ))
		        ->add('howOftenDoYouVisit', 'choice', array(
		        	    'choices' => [ 'everyday' => 'everyday',
		        	                   'once_a_week' => 'once_a_week',
		        	                   'once_a_month' => 'once_a_month',
		        	                   'more_than_twice_a_year' => 'more_than_twice_a_year',
		        	                   'once_a_year' => 'once_a_year',
		        	                   'less_than_once_a_year' => 'less_than_once_a_year' ],
		        	    'expanded' => true
		        	))
		        ->add('howOftenDoYouPhoneOrVideoCall', 'choice', array(
		        	    'choices' => [ 'everyday' => 'everyday',
		        	                   'once_a_week' => 'once_a_week',
		        	                   'once_a_month' => 'once_a_month',
		        	                   'more_than_twice_a_year' => 'more_than_twice_a_year',
		        	                   'once_a_year' => 'once_a_year',
		        	                   'less_than_once_a_year' => 'less_than_once_a_year' ],
		        	    'expanded' => true
		        	))
		        ->add('howOftenDoYouWriteEmailOrLetter', 'choice', array(
		        	    'choices' => [ 'everyday' => 'everyday',
		        	                   'once_a_week' => 'once_a_week',
		        	                   'once_a_month' => 'once_a_month',
		        	                   'more_than_twice_a_year' => 'more_than_twice_a_year',
		        	                   'once_a_year' => 'once_a_year',
		        	                   'less_than_once_a_year' => 'less_than_once_a_year' ],
		        	    'expanded' => true
		        	))
		        ->add('howOftenDoesClientSeeOtherPeople', 'choice', array(
		        	    'choices' => [ 'everyday' => 'everyday',
		        	                   'once_a_week' => 'once_a_week',
		        	                   'once_a_month' => 'once_a_month',
		        	                   'more_than_twice_a_year' => 'more_than_twice_a_year',
		        	                   'once_a_year' => 'once_a_year',
		        	                   'less_than_once_a_year' => 'less_than_once_a_year' ],
		        	    'expanded' => true
		        	))
		        ->add('anythingElseToTell', 'textarea')

		        ->add('doesClientReceivePaidCare', 'choice', array(
                    	'choices' => [ 'yes'=>'Yes', 'no'=>'No'],
                    	'expanded' => true
                 	 ))

		        ->add('howIsCareFunded', 'choice', array(
                    	'choices' => [ 'client_pays_for_all'=>'They pay for all their own care', 
                    	               'client_gets_financial_help'=>'They get some financial help (for example, from local authority or NHS)',
                    	               'all_care_is_paid_by_someone_else' => 'All is care paid for by someone else (for example, by the local authority or NHS)' ],
                    	'expanded' => true
                 	 ))
                        
		        ->add('whoIsDoingTheCaring', 'textarea')
                        
                ->add('doesClientHaveACarePlan', 'choice', array(
                        'choices' => [ 'yes'=>'Yes', 'no'=>'No'],
                        'expanded' => true
                    ))
                
		        ->add('whenWasCarePlanLastReviewed', 'date',[ 'widget' => 'text',
			                                                 'input' => 'datetime',
			                                                 'format' => 'dd-MM-yyyy',
			                                                 'invalid_message' => 'safeguarding.whenWasCarePlanLastReviewed.invalidMessage'
			                                              ])
		        ->add('save', 'submit')
				
				->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                
	                $data = $event->getData();
					
					// Strip out the date field if it's not needed. Having a partial date field breaks things
					// if the care plan date is hidden as it receives a date that only has a day
					if (isset($data['doesClientHaveACarePlan']) && $data['doesClientHaveACarePlan'] == 'no') {
						$data['whenWasCarePlanLastReviewed'] = null;
					}
    
                    $event->setData($data);
	            
				});
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
