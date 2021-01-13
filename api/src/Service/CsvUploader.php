<?php

namespace App\Service;

/**
 * keep in sync with CLIENT
 */
class CsvUploader
{
    /**
     * @param mixed $data
     *
     * @return string
     * @deprecated Use App\Service\DataCompression
     */
    public static function compressData($data)
    {
        return base64_encode(gzcompress(json_encode($data), 9));
    }

    /**
     * @param mixed $data
     *
     * @return string|array
     * @deprecated Use App\Service\DataCompression
     */
    public static function decompressData($data)
    {
        return json_decode(gzuncompress(base64_decode($data)), true);
    }
}
