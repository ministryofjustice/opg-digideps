<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Checker\Exception\InvalidFileException;
use AppBundle\Service\File\Checker\Exception\VirusFoundException;
use AppBundle\Service\File\Checker\Exception\RiskyFileException;
use AppBundle\Service\File\Types\UploadableFileInterface;
use AppBundle\Service\File\Types\Pdf;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\ResponseInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use GuzzleHttp\Post\PostFile;

class ClamAVChecker implements FileCheckerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $options;

    /**
     * ClamAv constructor.
     */
    public function __construct(ClientInterface $client, LoggerInterface $logger, array $options = [])
    {
        /** @var GuzzleHttp\Client client */
        $this->client = $client;
        $this->logger = $logger;
        $this->options = [];
    }

    /**
     *
     * Checks file for viruses using ClamAv
     *
     * @param UploadableFileInterface $uploadedFile
     *
     * @throws RuntimeException in case the result is not PASS
     *
     * @return bool
     */
    public function checkFile(UploadableFileInterface $file)
    {
        // POST body to clamAV
        $response = $this->getScanResults($file);

        $file->setScanResult($response);

        $isResultPass = strtoupper(trim($response['file_scanner_result'])) === 'PASS';

        // log results
        $level = $isResultPass ? Logger::INFO : Logger::ERROR;
        $this->log($level, 'File scan result', $file->getUploadedFile(), $response);

        if ($file instanceof Pdf && !$isResultPass) { // @shaun STILL NEEDED ? wouldn't this case go in the next "switch"
            throw new RiskyFileException('PDF file scan failed');
        }

        if ($isResultPass) {
            return true;
        }

        switch(strtoupper(trim($response['file_scanner_code']))) {
            case 'AV_FAIL':
                throw new VirusFoundException();
            case 'PDF_INVALID_FILE':
            case 'PDF_BAD_KEYWORD':
                throw new RiskyFileException();
        }

        throw new RuntimeException("Files scanner FAIL. Unrecognised code. Full response: ". print_r($response));
    }

    /**
     * POSTS the file body to file scanner, and continually polls until result is returned.
     *
     * @param UploadableFileInterface $uploadedFile
     * @return array
     */
    private function getScanResults(UploadableFileInterface $file)
    {
        try {
            $result = $this->makeScannerRequest($file);

            $maxRetries = 90;
            $count = 0;
            $statusResponse = [];

            //TODO use $statusResponse['celery_task_state'] == 'SUCCESS' to verify
            while ((!array_key_exists('file_scanner_result', $statusResponse)) && ($count < $maxRetries))
            {
                $statusResponse = $this->makeStatusRequest($result['location']);

                if ($statusResponse === false) {
                   $this->log(Logger::CRITICAL, 'Scanner response could not be decoded');
                    throw new \RunTimeException('Unable to contact file scanner');
                }

                sleep(1);

                $count++;
            }

            if (!array_key_exists('file_scanner_result', $statusResponse)) {
                $this->log(Logger::ERROR, 'Maximum attempts at contacting clamAV for status. Unable to retrieve complete scan result ' . $statusResponse);
            }

            return $statusResponse;

        } catch (\Exception $e) {
           $this->log(Logger::CRITICAL, 'Scanner exception: ' . $e->getCode() . ' - ' . $e->getMessage());

            throw new \RunTimeException($e);
        }
    }

    /**
     * Send file to File Scanner
     *
     * @param UploadableFileInterface $file
     *
     * @return array
     */
    private function makeScannerRequest(UploadableFileInterface $file)
    {
        $fullFilePath = $file->getUploadedFile()->getPathName();

        $request = $this->client->createRequest('POST', $file->getScannerEndpoint());
        $postBody = $request->getBody();
        $postBody->addFile(
            new PostFile('file', fopen($fullFilePath, 'r'))
        );

        $response = $this->client->send($request);

        if (!$response instanceof ResponseInterface ) {
            throw new \RuntimeException('ClamAV not available');
        }
        $result = json_decode($response->getBody()->getContents(), true);

        return $result;
    }

    /**
     * Query status of file scan using location returned by AV scanner
     * @param string $location
     *
     * @return array
     */
    private function makeStatusRequest($location)
    {
        $this->log(Logger::DEBUG, 'Quering scan status for location: ' . $location);

        $response = $this->client->get($location);
        $result = json_decode($response->getBody()->getContents(), true);

        $this->log(Logger::DEBUG, 'Scan status result for location: ' . $location . ': ');

        return $result;
    }


    /**
     * @param $level
     * @param $message
     * @param UploadedFile|null $file
     * @param array|null $response
     */
    private function log($level, $message, UploadedFile $file = null, array $response = null)
    {
        $extra = ['service' => 'clam_av_checker'];

        if ($file) {
            $extra['fileName']  = $file->getClientOriginalName();
        }

        if ($response) {
            $extra += [
            'file_scanner_code' => $response['file_scanner_code'],
            'file_scanner_result' => $response['file_scanner_result'], //could be omitted
            'file_scanner_message' => $response['file_scanner_message'],
            'file_scanner_message' => $response['file_scanner_message']
            ];
        }

        $this->logger->log($level, $message, ['extra' => $extra]);
    }
}
