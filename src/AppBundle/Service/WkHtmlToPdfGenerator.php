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
        //Example from https://github.com/openlabs/docker-wkhtmltopdf-aas/issues/18
        $data = array(
            'contents' => base64_encode($html),
            'options' => array(
                'encoding' => 'utf-8'
            ),
        );
        $dataString = json_encode($data);
        $headers = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataString),
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeoutSeconds);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutSeconds); //timeout in seconds
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
