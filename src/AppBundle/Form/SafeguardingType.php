<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SafeguardingType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('doYouLiveWithClient', 'choice', array(
                    	'choices' => ['response.yes'=>'response.yes', 'response.no'=>'response.no'],
                    	'expanded' => true
                 	 ))
		        ->add('howOftenDoYouVisit', 'choice', array(
		        	    'choices' => [ 'response.everyday' => 'response.everyday',
		        	                   'response.once_a_week' => 'response.once_a_week',
		        	                   'response.once_a_month' => 'response.once_a_month',
		        	                   'response.more_than_twice_a_year' => 'response.more_than_twice_a_year',
		        	                   'response.once_a_year' => 'response.once_a_year',
		        	                   'response.less_than_once_a_year' => 'response.less_than_once_a_year' ],
					    'expanded' => true
		        	))
		        ->add('howOftenDoYouPhoneOrVideoCall', 'choice', array(
		        	    'choices' => [ 'response.everyday' => 'response.everyday',
		        	                   'response.once_a_week' => 'response.once_a_week',
		        	                   'response.once_a_month' => 'response.once_a_month',
		        	                   'response.more_than_twice_a_year' => 'response.more_than_twice_a_year',
		        	                   'response.once_a_year' => 'response.once_a_year',
		        	                   'response.less_than_once_a_year' => 'response.less_than_once_a_year' ],
					   'expanded' => true
		        	))
		        ->add('howOftenDoYouWriteEmailOrLetter', 'choice', array(
		        	    'choices' => [ 'response.everyday' => 'response.everyday',
		        	                   'response.once_a_week' => 'response.once_a_week',
		        	                   'response.once_a_month' => 'response.once_a_month',
		        	                   'response.more_than_twice_a_year' => 'response.more_than_twice_a_year',
		        	                   'response.once_a_year' => 'response.once_a_year',
		        	                   'response.less_than_once_a_year' => 'response.less_than_once_a_year' ],
					   'expanded' => true
		        	))
		        ->add('howOftenDoesClientSeeOtherPeople', 'choice', array(
		        	    'choices' => [ 'response.everyday' => 'response.everyday',
		        	                   'response.once_a_week' => 'response.once_a_week',
		        	                   'response.once_a_month' => 'response.once_a_month',
		        	                   'response.more_than_twice_a_year' => 'response.more_than_twice_a_year',
		        	                   'response.once_a_year' => 'response.once_a_year',
		        	                   'response.less_than_once_a_year' => 'response.less_than_once_a_year' ],
					   'expanded' => true
		        	))
		        ->add('anythingElseToTell', 'textarea')

		        ->add('doesClientReceiveCare', 'choice', array(
                    	'choices' => [ 'response.yes'=>'response.yes', 'response.no'=>'response.no'],
                    	'expanded' => true
                 	 ))

		        ->add('howIsCareFunded', 'choice', array(
                    	'choices' => [ 'response.client_pays_for_all'=>'response.client_pays_for_all', 
                    	               'response.client_gets_financial_help'=>'response.client_gets_financial_help',
                    	               'response.all_care_is_paid_by_someone_else' => 'response.all_care_is_paid_by_someone_else' ],
                    	'expanded' => true
                 	 ))

		        ->add('whoIsDoingTheCaring', 'textarea')

		        ->add('whenWasCarePlanLastReviewed', 'date',[ 'widget' => 'text',
			                                                 'input' => 'datetime',
			                                                 'format' => 'dd-MM-yyyy',
			                                                 'invalid_message' => 'invalid date'
			                                              ]);
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
            'translation_domain' => 'report-safeguarding',
        ]);
    }
    
    public function getName() 
    {
        return 'safeguarding';
    }
}

?>