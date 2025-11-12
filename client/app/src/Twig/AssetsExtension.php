<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class AssetsExtension.
 */
class AssetsExtension extends AbstractExtension
{
    private ?string $tag = null;

    public function __construct(
        private readonly string $projectDir
    ) {
    }

    /**
     * Get the version name for assets add it to the url to give a versioned url
     */
    public function assetUrlFilter($originalUrl): string
    {
        if (!$this->tag) {
            // list the directories under web/assets
            $assetRoot = $this->projectDir . '/public/assets';

            $timestampedDirectories = scandir($assetRoot, SCANDIR_SORT_ASCENDING);

            // remove the '.', '..', and 'fallback' paths from the list of found directories
            // (we only want the timestamped directories)
            $assetContents = array_values(array_diff($timestampedDirectories, ['..', '.', 'fallback']));

            // use the last directory in the list, as this will be the one most-recently built
            sort($assetContents);
            $this->tag = array_reverse($assetContents)[0];
        }

        return '/assets/' . $this->tag . '/' . $originalUrl;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('assetUrl', [$this, 'assetUrlFilter'])
        ];
    }

    public function getName(): string
    {
        return 'assets_extension';
    }
}
