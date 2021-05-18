<?php

declare(strict_types=1);

namespace App\Service\Formatter;

use App\EventListener\RestInputOuputFormatter;
use App\Service\Validator\RestArrayValidator;
use Symfony\Component\HttpFoundation\Request;

class RestFormatter
{
    private RestInputOuputFormatter $formatter;
    private RestArrayValidator $validator;

    public function __construct(RestInputOuputFormatter $formatter, RestArrayValidator $validator)
    {
        $this->formatter = $formatter;
        $this->validator = $validator;
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
        $this->formatter->addContextModifier(function ($context) use ($groups) {
            $context->setGroups($groups);
        });
    }

    public function validateArray($data, array $assertions = [])
    {
        $this->validator->validateArray($data, $assertions);
    }
}
