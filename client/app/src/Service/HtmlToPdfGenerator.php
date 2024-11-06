<?php

namespace App\Service;

/**
 * Client to connect to docker-htmltopdf-aas
 * https://github.com/openlabs/docker-htmltopdf-aas.
 */
class HtmlToPdfGenerator
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $timeoutSeconds;

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

        //        file_put_contents('php://stderr', print_r(strlen($pdf), TRUE));
        //        file_put_contents('php://stderr', print_r(' JIMMY1 ', TRUE));
        //        file_put_contents('php://stderr', print_r(preg_match('/PDF-\d/', $pdf), TRUE));
        //        file_put_contents('php://stderr', print_r(' JIMMY2 ', TRUE));
        //        file_put_contents('php://stderr', print_r(strlen($pdf) > 700, TRUE));
        //        file_put_contents('php://stderr', print_r(' JIMMY3 ', TRUE));
        return strlen($pdf) > 700 && preg_match('/PDF-\d/', $pdf);
    }

    /**
     * @param string $html
     *
     * @return string pdf
     */
    public function getPdfFromHtml($html)
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
            'Content-Length: '.strlen($dataString),
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
        while (200 != $httpCode and $count < 3) {
            $pdfBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            ++$count;
        }

        curl_close($ch);

        return $pdfBody;
    }
}
