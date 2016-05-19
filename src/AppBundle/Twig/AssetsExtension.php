<?php

namespace AppBundle\Twig;

// todo remove assetic from config and use it's 

/**
 * Class AssetsExtension.
 */
class AssetsExtension extends \Twig_Extension
{
    /** @var  string $tag */
    private $tag;

    /** @var string $rootDir */
    private $rootDir;

    /**
     * @param type \String
     * @param type $params
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /** Get the version name for assets add it to the url to give a versioned url
     * Like assetic except The minification and versioning is done with gulp.
     *
     * @return string
     */
    public function assetUrlFilter($originalUrl)
    {
        return '/assets/'.$this->getTag().'/'.$originalUrl;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        if (!$this->tag) {
            // List the files in the web/assets folder
            $assetRoot = $this->rootDir.'/../web/assets';
            $assetContents = array_diff(scandir($assetRoot), array('..', '.'));

            // set the value to the folder we find.
            $this->tag = array_values($assetContents)[0];
        }

        return $this->tag;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('assetUrl', array($this, 'assetUrlFilter')),
        );
    }

    public function getName()
    {
        return 'assets_extension';
    }
}
