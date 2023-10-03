<?php

namespace App\Service;

/**
 * keep in sync with API
 */
class CsvUploader
{
    /**
     * @param  mixed  $data
     * @return string
     */
    public static function compressData($data): string
    {
        return base64_encode(gzcompress(json_encode($data), 9));
    }
}
