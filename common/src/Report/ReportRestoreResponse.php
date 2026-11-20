<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Report;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Common\Validating\ValidationException;

class ReportRestoreResponse implements \JsonSerializable
{
    public function __construct(
        public bool $success,
        public ?string $error = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'success' => $this->success,
            'error' => $this->error
        ];
    }

    /**
     * @throws ValidationException
     */
    public static function jsonDeserialize(string $json): ReportRestoreResponse
    {
        $data = new ValidatingArray((array)json_decode($json, true));
        return new ReportRestoreResponse(
            $data->getBooleanOrThrow('success'),
            $data->getStringOrNull('error')
        );
    }
}
