<?php

namespace App\Service;

/**
 * Client to connect to docker-htmltopdf-aas
 * https://github.com/openlabs/docker-htmltopdf-aas.
 */
class HtmlToPdfGenerator
{
    public function __construct(
        private readonly string $url,
        private readonly int $timeoutSeconds
    ) {
    }

    /**
     * @return bool true if working
     */
    public function isAlive(): bool
    {
        $pdf = $this->getPdfFromHtml('test');

        if (false === $pdf) {
            $pdf = '';
        }

        return strlen($pdf) > 700 && preg_match('/PDF-\d/', $pdf);
    }

    public function getPdfFromHtml(string $html): string|false
    {
        // Example from https://github.com/openlabs/docker-htmltopdf-aas/issues/18
        $data = [
            'contents' => base64_encode($html),
            'options' => [
                'encoding' => 'utf-8',
            ],
        ];
        $dataString = json_encode($data);
        $headers = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataString),
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeoutSeconds);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutSeconds); // timeout in seconds
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $httpCode = 0;
        $count = 0;
        $pdfBody = false;
        while (200 !== $httpCode and $count < 3) {
            $pdfBody = curl_exec($ch);

            if (!is_string($pdfBody)) {
                $pdfBody = '';
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            ++$count;
        }

        curl_close($ch);

        return $pdfBody;
    }
}
