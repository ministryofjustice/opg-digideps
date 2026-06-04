<?php

namespace OPG\Digideps\Backend\Service;

/**
 * keep in sync with CLIENT.
 */
class CsvUploader
{
    /**
     * @deprecated Use OPG\Digideps\Backend\Service\DataCompression
     */
    public static function compressData(array $data): string
    {
        return base64_encode(gzcompress(json_encode($data), 9));
    }

    /**
     * @deprecated Use OPG\Digideps\Backend\Service\DataCompression
     */
    public static function decompressData(string $data): array
    {
        return json_decode(gzuncompress(base64_decode($data)), true);
    }
}
