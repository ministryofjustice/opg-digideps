<?php
namespace AppBundle\Twig;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormError;
use Symfony\Component\Translation\TranslatorInterface;
use AppBundle\Service\DateFormatter;

class ComponentsExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Environment
     */
    private $environment;
    
    /**
     * @var TranslatorInterface
     */
    private $translator;
    
    
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    
    public function initRuntime(\Twig_Environment $environment)
    {
        parent::initRuntime($environment);
        $this->environment = $environment;
    }
    
    public function getFunctions()
    {
        return [];
    }
    
    public function getFilters()
    {
        return [
            // used for formatted report
            'country_name' => new \Twig_SimpleFilter('country_name', function($value) {
                return \Symfony\Component\Intl\Intl::getRegionBundle()->getCountryName($value);
            })
        ];
    }
    
    public function getName()
    {
        return 'components_extension';
    }
}