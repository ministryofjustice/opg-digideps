<?php

namespace App\MessageHandler\Command;

use App\Message\Command\UploadCsv;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UploadCsvHandler implements MessageHandlerInterface
{
    public function __invoke(UploadCsv $uploadCsv)
    {
        var_dump('Your CSV has been uploaded');
    }
}
