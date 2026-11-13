<?php

namespace OPG\Digideps\Frontend\Service;

use Gotenberg\Exceptions\GotenbergApiErrored;
use Gotenberg\Gotenberg;
use Gotenberg\Stream;
use Psr\Log\LoggerInterface;

/**
 * Client to send HTML to htmltopdf container for conversion
 */
class HtmlToPdfGenerator
{
    private string $basePath;

    public function __construct(
        private readonly string $url,
        private readonly LoggerInterface $logger
    ) {
        $this->basePath = '/var/www/public/assets/fallback/stylesheets';
    }

    public function isAlive(): bool
    {
        $pdf = $this->getPdfFromHtml('test', includeAssets: false);

        if ($pdf === false) {
            $pdf = '';
        }

        return strlen($pdf) > 700 && preg_match('/PDF-\d/', $pdf);
    }

    public function getPdfFromHtml(string $html, bool $includeAssets = true): string|false
    {
        $assets = [];

        if ($includeAssets) {
            // add font files
            $fontFiles = glob($this->basePath . '/fonts/*.woff*') ?: [];

            foreach ($fontFiles as $fontFile) {
                $assets[] = Stream::path($fontFile, basename($fontFile));
            }

            // read in application.css and munge the font paths
            $css = file_get_contents($this->basePath . '/application.css') ?: '';
            if (!empty($css)) {
                /** @var string $css */
                $css = str_replace('url(./fonts/', 'url(', $css);

                $assets[] = Stream::string('application.css', $css);
            }
        }

        $request = Gotenberg::chromium($this->url)
            ->pdf()
            ->emulateScreenMediaType()
            ->assets(...$assets)
            ->html(Stream::string('index.html', $html));

        $httpCode = 0;
        $count = 0;
        $pdfBody = '';

        while ($httpCode !== 200 && $count < 3) {
            try {
                $response = Gotenberg::send($request);
                $httpCode = $response->getStatusCode();
                $pdfBody = $response->getBody()->getContents();
            } catch (GotenbergApiErrored $e) {
                $this->logger->error('Error while converting HTML to PDF: ' . $e->getMessage());
            }

            ++$count;
        }

        return $httpCode === 200 ? $pdfBody : false;
    }
}
