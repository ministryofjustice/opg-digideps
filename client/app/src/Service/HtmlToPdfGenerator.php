<?php

namespace OPG\Digideps\Frontend\Service;

use Gotenberg\Gotenberg;
use Psr\Log\LoggerInterface;

/**
 * Client to send HTML to htmltopdf container for conversion
 */
class HtmlToPdfGenerator
{
    private string $assetsPath;

    public function __construct(
        private readonly string $url,
        private readonly int $timeoutSeconds,
        private readonly LoggerInterface $logger
    ) {
        $this->assetsPath = '/var/www/public/assets/fallback/stylesheets';
    }

    public function isAlive(): bool
    {
        $ch = curl_init($this->url . '/health');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeoutSeconds);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutSeconds);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);

        return curl_getinfo($ch, CURLINFO_RESPONSE_CODE) === 200;
    }

    public function getPdfFromHtml(string $html, bool $includeAssets = true, ?int $reportId = null): string|false
    {
        $postFields = [
            // required for gotenberg to use the screen media selectors in the CSS, rather than print
            'emulatedMediaType' => 'screen',

            'html' => new \CURLStringFile($html, postname: 'index.html', mime: 'text/html')
        ];

        if ($includeAssets) {
            // add woff2 font files (we don't need the fallback woff fonts)
            $fontFiles = glob($this->assetsPath . '/fonts/*.woff2') ?: [];

            foreach ($fontFiles as $index => $fontFile) {
                $postFields["font_{$index}"] = new \CURLFile($fontFile, mime_type: 'font/woff2', posted_filename: basename($fontFile));
            }

            // read in application.css and munge the font paths
            $css = file_get_contents($this->assetsPath . '/application.css') ?: '';

            if (!empty($css)) {
                /** @var string $css */
                $css = str_replace('url(./fonts/', 'url(', $css);
            }

            $postFields['css_report'] = new \CURLStringFile($css, postname: 'application.css', mime: 'text/css');

            // add the CSS used for checklists
            $postFields['css_checklist'] = new \CURLFile(
                $this->assetsPath . '/formatted-report.css',
                mime_type: 'text/css',
                posted_filename: 'formatted-report.css'
            );
        }

        $ch = curl_init($this->url . '/forms/chromium/convert/html');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeoutSeconds);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutSeconds);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $count = 0;
        $pdfBody = false;
        $httpCode = 0;

        while ($httpCode !== 200 && $count < 3) {
            // will be either string or false because CURLOPT_RETURNTRANSFER option is true
            /** @var string|false $pdfBody */
            $pdfBody = curl_exec($ch);

            $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

            ++$count;
        }

        if ($httpCode !== 200) {
            $msg = "Error while converting HTML to PDF; last response code was {$httpCode}";
            if ($reportId !== null) {
                $msg .= " (for report ID {$reportId})";
            }
            $this->logger->error($msg);
        }

        return $httpCode === 200 ? $pdfBody : false;
    }
}
