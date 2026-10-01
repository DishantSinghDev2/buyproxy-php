<?php

declare(strict_types=1);

namespace BuyProxy\Resources;

use BuyProxy\Models\SignupResult;
use BuyProxy\Models\VerifyResult;

/** Create an account without a browser: `signup`, then `verifyOtp` with the emailed code. */
final class Auth extends Resource
{
    public function signup(string $email, string $password, ?string $name = null): SignupResult
    {
        return SignupResult::fromArray($this->client->request('POST', '/api/auth/signup', body: self::compact(['email' => $email, 'password' => $password, 'name' => $name]), public: true));
    }

    public function verifyOtp(string $email, string $code): VerifyResult
    {
        return VerifyResult::fromArray($this->client->request('POST', '/api/auth/verify-otp', body: ['email' => $email, 'code' => $code], public: true));
    }

    public function resendOtp(string $email): void
    {
        $this->client->request('POST', '/api/auth/resend-otp', body: ['email' => $email], public: true);
    }
}
