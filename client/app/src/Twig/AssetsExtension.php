<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

// todo remove assetic from config and use it's

/**
 * Class AssetsExtension.
 */
class AssetsExtension extends AbstractExtension
{
    private ?string $tag = null;

    public function __construct(private string $projectDir)
    {
    }

    /** Get the version name for assets add it to the url to give a versioned url
     * Like assetic except The minification and versioning is done with Webpack.
     *
     * @return string
     */
    public function assetUrlFilter($originalUrl)
    {
        return '/assets/'.$this->getTag().'/'.$originalUrl;
    }

    public function assetSourceFilter($originalUrl)
    {
        $tag = $this->getTag();
        $source = file_get_contents($this->projectDir.'/public/assets/'.$tag.'/'.$originalUrl);

        return $source;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        if (!$this->tag) {
            // List the files in the web/assets folder
            $assetRoot = $this->projectDir.'/public/assets';
            $assetContents = array_diff(scandir($assetRoot, SCANDIR_SORT_ASCENDING), ['..', '.']);

            file_put_contents('php://stderr', print_r($assetContents, true));

            // set the value to the folder we find.
            $this->tag = array_values($assetContents)[0];
        }
        file_put_contents('php://stderr', print_r(' JIMMY ', true));
        file_put_contents('php://stderr', print_r($this->tag, true));

        return $this->tag;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('assetUrl', [$this, 'assetUrlFilter']),
            new TwigFilter('assetSource', [$this, 'assetSourceFilter']),
        ];
    }

    public function getName()
    {
        return 'assets_extension';
    }
}
