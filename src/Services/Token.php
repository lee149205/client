<?php

namespace ZiraClient\Services;

use Exception;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

class Token
{
    public function generateToken($clientId, $clientSecret, $userId = null)
    {
        $builder = (new Builder())
            ->permittedFor($clientId)
            ->identifiedBy($this->generateUniqueIdentifier())
            ->issuedAt(time())
            ->canOnlyBeUsedAfter(time())
            ->relatedTo((string) $userId)
            ->expiresAt(time() + 24 * 60 * 60);

        return $builder->getToken(new Sha256(), new Key($clientSecret))->__toString();
    }

    public function generateUniqueIdentifier($length = 40)
    {
        try {
            return bin2hex(random_bytes($length));
        } catch (Exception $e) {
            // If you get this message, the CSPRNG failed hard.
            throw new Exception('Could not generate a random string', $e);
        }
    }
}
