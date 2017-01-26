<?php

namespace AppBundle\Service;

/**
 * Client to connect to docker-wkhtmltopdf-aas
 * https://github.com/openlabs/docker-wkhtmltopdf-aas.
 */
class WkHtmlToPdfGenerator
{
    /**
     * @var string
     */
    private $url;

    /**
     * @param string $url
     */
    public function __construct($url, $timeoutSeconds)
    {
        $this->url = $url;
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /**
     * @return bool true if working
     */
    public function isAlive()
    {
        $pdf = $this->getPdfFromHtml('test');

        return strlen($pdf) > 5000 && preg_match('/PDF-\d/', $pdf);
    }

    /**
     * @param string $html
     *
     * @return string pdf
     */
    public function getPdfFromHtml($html)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeoutSeconds);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutSeconds); //timeout in seconds
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']); // Assuming you're requesting JSON
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $body = json_encode([
            'contents' => base64_encode($html),
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        return curl_exec($ch);
    }
}
