<?php

namespace AppBundle\Service;

class CsvUploader
{
    /**
     * keep in sync with inverse method in CLIENT
     *
     * @param  mixed  $data
     * @return string
     */
    public static function decompressData($data)
    {
        return json_decode(gzuncompress(base64_decode($data)), true);
    }
}
