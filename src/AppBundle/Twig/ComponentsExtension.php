<?php

namespace AppBundle\Twig;

use Symfony\Component\Translation\TranslatorInterface;

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
            'country_name' => new \Twig_SimpleFilter('country_name', function ($value) {
                return \Symfony\Component\Intl\Intl::getRegionBundle()->getCountryName($value);
            }),
            'money_format' => new \Twig_SimpleFilter('money_format', function ($string) {
                return number_format($string, 2, '.', ',');
            }),
        ];
    }

    public function getName()
    {
        return 'components_extension';
    }
}
