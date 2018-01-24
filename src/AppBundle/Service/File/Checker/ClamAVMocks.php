<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Types\UploadableFileInterface;

/**
 * ClamAV responses for known files, based on file hash
 * To use for behat-tests and avoid non-responsiveness of the ClamAV service on test env
 */
class ClamAVMocks
{
    /**
     * `tests/behat/fixtures` has files along with response
     *
     * @var array
     */
    private static $fileHashToResponse = [
        'fa7d7e650b2cec68f302b31ba28235d8' => [  // good.pdf
            'celery_task_state'    => 'SUCCESS',
            'file_scanner_code'    => null,
            'file_scanner_message' => null,
            'file_scanner_result'  => 'PASS',
        ],
        'a1ddc9ebe19a3d43ec25889085ad3ed8' => [ // pdf-doc-vba-eicar-dropper.pdf
            'celery_task_state'    => 'SUCCESS',
            'file_scanner_code'    => 'AV_FAIL',
            'file_scanner_message' => 'FOUND Doc.Dropper.Agent-1540415 (mock response)',
            'file_scanner_result'  => 'FAIL',
        ],
        'd459dc4890f2ba3c285e014190ca0560' => [ //good.jpg
            'celery_task_state'    => 'SUCCESS',
            'file_scanner_code'    => null,
            'file_scanner_message' => 'Image details 500x500 mode RGB (mock response)',
            'file_scanner_result'  => 'PASS',
        ],
        '86c9b243a641dfd2d6b013da32503141' => [//good.png
            'celery_task_state'    => 'SUCCESS',
            'file_scanner_code'    => null,
            'file_scanner_message' => 'Image details 500x500 mode RGB (mock response)',
            'file_scanner_result'  => 'PASS',
        ],
        'd7e19f88174e81c16c6cd0f3f53f0e0e' => [ //small.jpg
            'celery_task_state' => 'SUCCESS',
            'file_scanner_code' => 'JPEG_DIMENSION_UNDER_500',
            'file_scanner_message' => 'Image details 200x200 mode RGB (mock response)',
            'file_scanner_result' => 'PASS',
        ],
        'ffa7763c3fb52dc45e721a9846f574ce' => [ //small.png
            'celery_task_state' => 'SUCCESS',
            'file_scanner_code' => 'PNG_DIMENSION_UNDER_500',
            'file_scanner_message' => 'Image details 200x200  (mock response)',
            'file_scanner_result' => 'PASS',
        ],
        'a2ba3341104c77c2697666690559a907' => [ // file1.pdf
            'celery_task_state' => 'SUCCESS',
            'file_scanner_code' => null,
            'file_scanner_message' => null,
            'file_scanner_result' => 'PASS'
        ],
        '96deed834c6424a81b4b755d5d9ec504' => [ //file2.pdf
            'celery_task_state' => 'SUCCESS',
            'file_scanner_code' => null,
            'file_scanner_message' => null,
            'file_scanner_result' => 'PASS'
        ]

    ];

    /**
     * Returns cache response if there is one. Null otherwise
     *
     * @param  UploadableFileInterface $file
     * @return mixed
     */
    public static function getCachedResponse(UploadableFileInterface $file)
    {
        $uploadedFileHash = hash_file('md5', $file->getUploadedFile()->getPathName());
        if (isset(self::$fileHashToResponse[$uploadedFileHash])) {
            return self::$fileHashToResponse[$uploadedFileHash];
        }
    }
}
