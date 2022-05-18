<?php

namespace App\MessageHandler\Command;

use App\Message\Command\UploadCsv;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UploadCsvHandler implements MessageHandlerInterface
{
    public function __invoke(UploadCsv $uploadCsv)
    {
        throw new Exception('Oh no, we are not ready to handle this event yet!');
    }
}
