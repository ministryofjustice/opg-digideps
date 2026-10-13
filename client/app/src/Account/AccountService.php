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
            $response = AccountResponse::jsonDeserialize($this->restClient->post('/account/request', $request, expectedResponseType: 'raw'));
            if ($response instanceof AccountError) {
                return $response;
            }
            return $this->sendSetPasswordEmail($response);
        } catch (\Throwable $_) {
            return AccountError::NetworkError;
        }
    }

    public function sendResetRequest(ResetRequest $request): Email|AccountError
    {
        try {
            $response = AccountResponse::jsonDeserialize($this->restClient->post('/account/password/reset', $request, expectedResponseType: 'raw'));
            if ($response instanceof AccountError) {
                return $response;
            }
            return $this->sendSetPasswordEmail($response, true);
        } catch (\Throwable $_) {
            return AccountError::NetworkError;
        }
    }

    public function sendPasswordRequest(PasswordRequest $request): true|AccountError
    {
        try {
            $response = PasswordResponse::jsonDeserialize($this->restClient->post('/account/password/set', $request, expectedResponseType: 'raw'));
            if ($response instanceof AccountError) {
                return $response;
            }
            return true;
        } catch (\Throwable $_) {
            return AccountError::NetworkError;
        }
    }

    private function sendSetPasswordEmail(AccountResponse $response, bool $isReset = false): Email|AccountError
    {
        return $this->mailer->sendSetPasswordEmail($response, $isReset) ? $response->email : AccountError::EmailError;
    }

    public function sendAccountRequestMock(AccountRequest $accountRequest, string $mockResponseToReceive): Email|AccountError
    {
        if ($mockResponseToReceive === 'alreadyregistered') {
            return AccountError::AccountTaken;
        } elseif ($mockResponseToReceive === 'nomatch') {
            return AccountError::MatchingError;
        } elseif ($mockResponseToReceive === 'setpasswordemailfail') {
            return AccountError::EmailError;
        } elseif ($mockResponseToReceive === 'networkerror') {
            return AccountError::NetworkError;
        }

        return $accountRequest->deputyEmail;
    }
}
