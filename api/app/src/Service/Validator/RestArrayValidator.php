<?php

declare(strict_types=1);

namespace App\Service\Validator;

class RestArrayValidator
{
    /**
     * @param array $assertions key=>rule
     *
     * @throws \InvalidArgumentException
     */
    public function validateArray(array $data, array $assertions = [])
    {
        $errors = [];

        foreach ($assertions as $requiredKey => $validation) {
            switch ($validation) {
                case 'notEmpty':
                    if (empty($data[$requiredKey])) {
                        $errors[] = "Expected value for '$requiredKey' key";
                    }
                    break;

                case 'mustExist':
                    if (!array_key_exists($requiredKey, $data)) {
                        $errors[] = "Missing '$requiredKey' key";
                    }
                    break;

                default:
                    throw new \InvalidArgumentException(__METHOD__.": {$validation} not recognised.");
            }
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException('Errors('.count($errors).'): '.implode(', ', $errors));
        }
    }
}
