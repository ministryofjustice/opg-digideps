<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Admin;

use OPG\Digideps\Frontend\Entity\Client;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

// this will eventually contain the whole client page, but currently just renders the report tables at the bottom
#[AsTwigComponent]
final class ClientDetails
{
    /**
     * @var array<string, string> $text
     */
    public array $text = [];

    private array $parameters = [];

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function mount(Client $client): void
    {
        $this->parameters = [];
        $this->text = $this->makeText();
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(): array
    {
        $keys = [
            'reportsHeading',
        ];

        return array_reduce($keys, function (array $sofar, string $key): array {
            $sofar[$key] = $this->translate($key);
            return $sofar;
        }, []);
    }

    private function translate(string $key): string
    {
        try {
            return $this->translator->trans("opg.admin.clientDetails.{$key}", $this->parameters, 'twig-components');
        } catch (\Throwable $t) {
            return "$t";
        }
    }
}
