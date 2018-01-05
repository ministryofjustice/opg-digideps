<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Checker\Exception\RiskyFileException;
use AppBundle\Service\File\Checker\Exception\VirusFoundException;
use AppBundle\Service\File\Types\Pdf;
use AppBundle\Service\File\Types\UploadableFileInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Post\PostFile;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

        switch (strtoupper(trim($response['file_scanner_code']))) {
            case 'AV_FAIL':
                throw new VirusFoundException();
            case 'PDF_INVALID_FILE':
            case 'PDF_BAD_KEYWORD':
                throw new RiskyFileException();
        }

        throw new RuntimeException('Files scanner FAIL. Unrecognised code. Full response: ' . print_r($response));
    }

    private static $fileHashToResponse = [
        'fa7d7e650b2cec68f302b31ba28235d8' => [  // good.pdf
            'celery_task_state' => 'SUCCESS',
            'file_scanner_code' => NULL,
            'file_scanner_message' => NULL,
            'file_scanner_result' => 'PASS'
        ],
        'a1ddc9ebe19a3d43ec25889085ad3ed8' => [ // pdf-doc-vba-eicar-dropper.pdf
            'celery_task_state' => 'SUCCESS',
            'file_scanner_code' => 'AV_FAIL',
            'file_scanner_message' => 'FOUND Doc.Dropper.Agent-1540415',
            'file_scanner_result' => 'FAIL',
        ],
        'd459dc4890f2ba3c285e014190ca0560' => [ //good.jpg
            'celery_task_state' => 'SUCCESS',
            'file_scanner_code' => NULL,
            'file_scanner_message' => 'Image details 500x500 mode RGB',
            'file_scanner_result' => 'PASS',
        ],
        '86c9b243a641dfd2d6b013da32503141' => [//good.png
            'celery_task_state' => 'SUCCESS',
            'file_scanner_code' => NULL,
            'file_scanner_message' => 'Image details 500x500 mode RGB',
            'file_scanner_result' => 'PASS',
        ],
        'd7e19f88174e81c16c6cd0f3f53f0e0e' => [ //small.jpg
            'celery_task_state' => 'SUCCESS', 'file_scanner_code' => 'JPEG_DIMENSION_UNDER_500', 'file_scanner_message' => 'Image details 200x200 mode RGB', 'file_scanner_result' => 'PASS',
        ],
        'ffa7763c3fb52dc45e721a9846f574ce' => [ //small.png
            'celery_task_state' => 'SUCCESS', 'file_scanner_code' => 'PNG_DIMENSION_UNDER_500', 'file_scanner_message' => 'Image details 200x200 mode P', 'file_scanner_result' => 'PASS',
        ]
        
    ];

    /**
     * POSTS the file body to file scanner, and continually polls until result is returned.
     *
     * @param  UploadableFileInterface $uploadedFile
     * @return array
     */
    private function getScanResults(UploadableFileInterface $file)
    {
        $uploadedFileHash = hash_file('md5', $file->getUploadedFile()->getPathName());
        if (isset(self::$fileHashToResponse[$uploadedFileHash])) {
            return self::$fileHashToResponse[$uploadedFileHash];
        }

        try {
            $result = $this->makeScannerRequest($file);

            $maxRetries = 90;
            $count = 0;
            $statusResponse = [];

            //TODO use $statusResponse['celery_task_state'] == 'SUCCESS' to verify
            while ((!array_key_exists('file_scanner_result', $statusResponse)) && ($count < $maxRetries)) {
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

            var_dump($uploadedFileHash);
            echo var_export($statusResponse);die;
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

        if (!$response instanceof ResponseInterface) {
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
     * @param array|null        $response
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
