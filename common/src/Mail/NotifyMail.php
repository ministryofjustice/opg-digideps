<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Mail;

use OPG\Digideps\Common\Account\Email;
use OPG\Digideps\Common\Validating\ValidatingArray;
use Symfony\Component\Uid\Uuid;

final readonly class NotifyMail implements \JsonSerializable
{
    public const string NOTIFY_FROM_EMAIL_ID = 'db930cb2-2153-4e2a-b3d0-06f7c7f92f37';

    public Uuid $notifyFromId;

    public function __construct(
        public Email $toEmail,
        public EmailTemplate $template,
        /**
         * @var array<string, string> $parameters
         */
        public array $parameters = [],
        ?Uuid $notifyFromId = null,
    ) {
        $this->notifyFromId = $notifyFromId ?? Uuid::fromRfc4122(NotifyMail::NOTIFY_FROM_EMAIL_ID);
    }

    public function jsonSerialize(): array
    {
        return [
            'toEmail' => $this->toEmail,
            'template' => $this->template,
            'parameters' => $this->parameters,
            'fromId' => $this->notifyFromId,
        ];
    }

    public static function jsonDeserialize(string $json): NotifyMail
    {
        $data = new ValidatingArray((array)json_decode($json, true));
        $parameters =  new ValidatingArray($data->getArrayOrThrow('parameters'));
        $array = [];
        foreach ($parameters->keys() as $key) {
            $array[(string)$key] = $parameters->getStringOrThrow($key);
        }

        return new NotifyMail(
            new Email($data->getStringOrThrow('toEmail')),
            EmailTemplate::fromUuid(Uuid::fromString($data->getStringOrThrow('template'))),
            $array,
            Uuid::fromString($data->getStringOrThrow('fromId'))
        );
    }
}
