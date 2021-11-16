<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;

// todo remove assetic from config and use it's

/**
 * Class AssetsExtension.
 */
class AssetsExtension extends AbstractExtension
{
    /** @var string $tag */
    private $tag;

    /**
     * @param string $rootDir
     */
    public function __construct(private $rootDir)
    {
    }

    /** Get the version name for assets add it to the url to give a versioned url
     * Like assetic except The minification and versioning is done with Webpack.
     *
     * @return string
     */
    public function assetUrlFilter($originalUrl)
    {
        return '/assets/' . $this->getTag() . '/' . $originalUrl;
    }

    public function assetSourceFilter($originalUrl)
    {
        $tag = $this->getTag();

        return file_get_contents($this->rootDir . '/public/assets/' . $tag . '/' . $originalUrl);
    }

    /**
     * @return string
     */
    public function getTag()
    {
        if (!$this->tag) {
            // List the files in the web/assets folder
            $assetRoot = $this->rootDir . '/public/assets';
            $assetContents = array_diff(scandir($assetRoot, SCANDIR_SORT_DESCENDING), ['..', '.']);

            // set the value to the folder we find.
            $this->tag = array_values($assetContents)[0];
        }

        return $this->tag;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('assetUrl', [$this, 'assetUrlFilter']),
            new \Twig_SimpleFilter('assetSource', [$this, 'assetSourceFilter']),
        ];
    }

    public function getName()
    {
        return 'assets_extension';
    }
}
