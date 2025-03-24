<?php

declare(strict_types=1);

namespace App\Service\Formatter;

use App\EventListener\RestInputOuputFormatter;
use App\Service\Validator\RestArrayValidator;
use Symfony\Component\HttpFoundation\Request;

class RestFormatter
{
    public function __construct(private readonly RestInputOuputFormatter $formatter, private readonly RestArrayValidator $validator)
    {
    }

    /**
     * @return array|null
     */
    public function deserializeBodyContent(Request $request, array $assertions = [])
    {
        $return = $this->formatter->requestContentToArray($request);
        $this->validator->validateArray($return, $assertions);

        return $return;
    }

    /**
     * Set serialise group used by JMS serialiser to composer ouput response
     * Attach setting to REquest as header, to be read by REstInputOuputFormatter kernel listener.
     *
     * @param string $groups user
     */
    public function setJmsSerialiserGroups(array $groups)
    {
        $this->formatter->addContextModifier(function ($context) use ($groups): void {
            $context->setGroups($groups);
        });
    }

    public function validateArray($data, array $assertions = [])
    {
        $this->validator->validateArray($data, $assertions);
    }
}
