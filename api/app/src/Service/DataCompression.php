<?php

namespace OPG\Digideps\Backend\Service;

class DataCompression
{
    public function compress(array $data): string
    {
        return base64_encode(gzcompress(json_encode($data), 9));
    }

    public function decompress(string $data): array
    {
        return json_decode(gzuncompress(base64_decode($data)), true);
    }
}
