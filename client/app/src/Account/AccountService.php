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

    public function sendResetRequest(ResetRequest $request): Email|AccountError
    {
        $response = AccountResponse::jsonDeserialize($this->restClient->post('', $request, expectedResponseType: 'raw'));
        if ($response instanceof AccountError) {
            return $response;
        }
        return $this->sendSetPasswordEmail($response, true);
    }

    public function sendAccountRequest(AccountRequest $request): Email|AccountError
    {
        $response = AccountResponse::jsonDeserialize($this->restClient->post('', $request, expectedResponseType: 'raw'));
        if ($response instanceof AccountError) {
            return $response;
        }
        return $this->sendSetPasswordEmail($response);
    }

    public function sendPasswordRequest(PasswordRequest $request): true|AccountError
    {
        $response = PasswordResponse::jsonDeserialize($this->restClient->post('', $request, expectedResponseType: 'raw'));
        if ($response instanceof AccountError) {
            return $response;
        }
        return true;
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
            throw new \Exception('network problem');
        }

        return $accountRequest->deputyEmail;
    }
}
