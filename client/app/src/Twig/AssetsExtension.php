<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig extensions for asset URLs
 */
class AssetsExtension extends AbstractExtension
{
    // the timestamp of the build, used to version assets and bust HTTP caching after a release
    private ?string $tag = null;

    public function __construct(
        private readonly string $projectDir
    ) {
    }

    /**
     * Add the version tag to an asset path
     */
    public function assetUrlFilter(string $assetPath): string
    {
        if (is_null($this->tag)) {
            // list the directories under web/assets
            $assetDirs = scandir($this->projectDir . '/public/assets', SCANDIR_SORT_ASCENDING);

            // just direct the request to the fallback directory if directory cannot be scanned
            if (!$assetDirs) {
                return "/assets/fallback/$assetPath";
            }

            // remove the '.', '..', and 'fallback' paths from the list of found directories
            // (we only want the timestamp directories)
            $timestampDirs = array_diff($assetDirs, ['..', '.', 'fallback']);

            // use the last directory in the list, as this will be the one most-recently built
            $this->tag = end($timestampDirs);
        }

        return '/assets/' . $this->tag . '/' . $assetPath;
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
