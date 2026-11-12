<?php

namespace OPG\Digideps\Frontend\Service;

/**
 * Client to connect to docker-htmltopdf-aas
 * https://github.com/openlabs/docker-htmltopdf-aas.
 */
class HtmlToPdfGenerator
{
    private string $basePath;

    public function __construct(
        private readonly string $url,
        private readonly int $timeoutSeconds
    ) {
        $this->basePath = '/var/www/public/assets/fallback/stylesheets';
    }
    /**
     * @return bool true if working
     */
    public function isAlive(): bool
    {
        $pdf = $this->getPdfFromHtml('test');

        if ($pdf === false) {
            $pdf = '';
        }

        return strlen($pdf) > 700 && preg_match('/PDF-\d/', $pdf);
    }

    public function getPdfFromHtml(string $html): string|false
    {
        $postFields = [
            'files[0]' => new \CURLStringFile(
                $html,
                'index.html',
                'text/html'
            ),
            'files[1]' => new \CURLFile(
                $this->basePath . '/application.css',
                'text/css',
                'application.css'
            ),
        ];

        $fontFiles = glob(
            $this->basePath . '/fonts/*.woff'
        );

        $i = 2;

        foreach ($fontFiles as $fontFile) {
            $postFields["files[$i]"] = new \CURLFile(
                $fontFile,
                'font/woff',
                basename($fontFile)
            );

            ++$i;
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeoutSeconds);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutSeconds);
        curl_setopt($ch, CURLOPT_URL, $this->url . '/forms/chromium/convert/html');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        $httpCode = 0;
        $count = 0;
        $pdfBody = false;

        while ($httpCode !== 200 && $count < 3) {
            $pdfBody = curl_exec($ch);

            if (!is_string($pdfBody)) {
                $pdfBody = '';
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            ++$count;
        }

        curl_close($ch);

        return $httpCode === 200 ? $pdfBody : false;
    }
}
