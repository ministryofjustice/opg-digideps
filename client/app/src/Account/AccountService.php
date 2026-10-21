<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Account;

use OPG\Digideps\Common\Account\AccountError;
use OPG\Digideps\Common\Account\AccountRequest;
use OPG\Digideps\Common\Account\AccountResponse;
use OPG\Digideps\Common\Account\Email;
use OPG\Digideps\Common\Account\PasswordRequest;
use OPG\Digideps\Common\Account\PasswordResponse;
use OPG\Digideps\Common\Account\ResetRequest;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use Psr\Http\Message\StreamInterface;

final readonly class AccountService
{
    public function __construct(
        private RestClient $restClient,
        private Mailer $mailer,
    ) {
    }

    public function sendAccountRequest(AccountRequest $request): Email|AccountError
    {
        try {
            $response = AccountResponse::jsonDeserialize($this->getRawResponse('/account/request', $request));
            if ($response instanceof AccountError) {
                return $response;
            }
            return $this->sendSetPasswordEmail($response);
        } catch (\Throwable $e) {
            return AccountError::NetworkError;
        }
    }

    public function sendResetRequest(ResetRequest $request): Email|AccountError
    {
        try {
            $response = AccountResponse::jsonDeserialize($this->getRawResponse('/account/password/reset', $request));
            if ($response instanceof AccountError) {
                return $response;
            }
            return $this->sendSetPasswordEmail($response, true);
        } catch (\Throwable $_) {
            return AccountError::NetworkError;
        }
    }

    public function sendPasswordRequest(PasswordRequest $request): Email|AccountError
    {
        try {
            $response = PasswordResponse::jsonDeserialize($this->getRawResponse('/account/password/set', $request));
            if ($response instanceof AccountError) {
                return $response;
            }
            return $this->sendConfirmPasswordEmail($response);
        } catch (\Throwable $_) {
            return AccountError::NetworkError;
        }
    }

    private function sendSetPasswordEmail(AccountResponse $response, bool $isReset = false): Email|AccountError
    {
        return $this->mailer->sendSetPasswordEmail($response, $isReset) ? $response->email : AccountError::EmailError;
    }

    private function sendConfirmPasswordEmail(PasswordResponse $response): Email|AccountError
    {
        return $this->mailer->sendConfirmPasswordEmail($response) ? $response->email : AccountError::EmailError;
    }

    private function getRawResponse(string $path, AccountRequest|PasswordRequest|ResetRequest $request): string
    {
        $json = json_encode($request);
        if ($json === false) {
            throw new \InvalidArgumentException('account-related request could not be encoded as JSON');
        }

        /** @var StreamInterface $rawResponse */
        $rawResponse = $this->restClient->post($path, $json, expectedResponseType: 'raw');

        return (string) $rawResponse;
    }
}
